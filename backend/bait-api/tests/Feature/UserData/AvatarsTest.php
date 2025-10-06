<?php

namespace Tests\Feature\UserData;

use App\Modules\UserData\Domain\Models\Avatar;
use App\Modules\UserData\Domain\Models\User;
use Database\Seeders\AvatarSeeder;
use Database\Seeders\BannerSeeder;
use Database\Seeders\UserRolesSeeder;
use Database\Seeders\UserStateSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AvatarsTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(UserRolesSeeder::class);
        $this->seed(UserStateSeeder::class);
        $this->seed(AvatarSeeder::class);
        $this->seed(BannerSeeder::class);

        $this->user = User::factory()->create();
    }

    #[Test]
    public function an_authenticated_user_can_upload_an_avatar()
    {
        Storage::fake('public');
        $file = UploadedFile::fake()->image('avatar.jpg', 500, 500);

        $response = $this->actingAs($this->user, 'api')->postJson(route('avatars.upload'), [
            'avatar' => $file,
        ]);

        $response->assertStatus(201);
        $response->assertJsonStructure(['data' => ['id', 'url_avatars']]);

        $avatarData = $response->json('data');
        $avatarPath = str_replace('/storage/', '', $avatarData['url_avatars']);

        Storage::disk('public')->assertExists($avatarPath);
        $this->assertDatabaseHas('avatars', ['url_avatars' => $avatarPath]);
        $this->assertDatabaseHas('users', [
            'id' => $this->user->id,
            'avatar_id' => $avatarData['id'],
        ]);
    }

    #[Test]
    public function an_authenticated_user_can_replace_their_avatar()
    {
        Storage::fake('public');

        $oldAvatarFile = UploadedFile::fake()->image('old.jpg', 500, 500);
        $oldAvatarPath = $oldAvatarFile->store('avatars', 'public');
        $oldAvatar = Avatar::create(['url_avatars' => $oldAvatarPath]);
        $this->user->update(['avatar_id' => $oldAvatar->id]);

        $this->user->refresh();

        $newAvatarFile = UploadedFile::fake()->image('new.jpg', 500, 500);
        $response = $this->actingAs($this->user, 'api')->postJson(route('avatars.upload'), [
            'avatar' => $newAvatarFile,
        ]);

        $response->assertStatus(201);
        Storage::disk('public')->assertMissing($oldAvatarPath);
        $this->assertDatabaseMissing('avatars', ['id' => $oldAvatar->id]);
    }

    #[Test]
    public function unauthenticated_user_cannot_upload_avatar()
    {
        $file = UploadedFile::fake()->image('avatar.jpg');
        $response = $this->postJson(route('avatars.upload'), ['avatar' => $file]);
        $response->assertStatus(401);
    }

    #[Test]
    public function avatar_must_be_an_image()
    {
        $file = UploadedFile::fake()->create('document.pdf');
        $response = $this->actingAs($this->user, 'api')->postJson(route('avatars.upload'), ['avatar' => $file]);
        $response->assertStatus(422)->assertJsonValidationErrors('avatar');
    }

    #[Test]
    public function avatar_must_meet_dimension_requirements()
    {
        $file = UploadedFile::fake()->image('small.jpg', 50, 50);
        $response = $this->actingAs($this->user, 'api')->postJson(route('avatars.upload'), ['avatar' => $file]);
        $response->assertStatus(422)->assertJsonValidationErrors('avatar');
    }

    #[Test]
    public function it_can_show_an_avatar()
    {
        Storage::fake('public');
        $avatar = Avatar::factory()->create();
        Storage::disk('public')->put($avatar->url_avatars, 'dummy-content');

        $response = $this->actingAs($this->user, 'api')->getJson(route('avatars.show', $avatar->id));

        $response->assertStatus(200);
        $response->assertJson(['data' => [
            'id' => $avatar->id,
            'url_avatars' => Storage::url($avatar->url_avatars),
        ]]);
    }
}