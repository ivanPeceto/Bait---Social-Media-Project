<?php

namespace Tests\Feature\UserData;

use App\Modules\UserData\Domain\Models\Banner;
use App\Modules\UserData\Domain\Models\User;
use Database\Seeders\AvatarSeeder;
use Database\Seeders\BannerSeeder;
use Database\Seeders\UserRolesSeeder;
use Database\Seeders\UserStateSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BannersTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    public function setUp(): void
    {
        parent::setUp();

        $this->seed(UserRolesSeeder::class);
        $this->seed(UserStateSeeder::class);
        $this->seed(AvatarSeeder::class);
        $this->seed(BannerSeeder::class);

        $this->user = User::factory()->create();
    }
    
    /** @test */
    public function an_authenticated_user_can_upload_a_banner()
    {
        Storage::fake('public');
        $file = UploadedFile::fake()->image('banner.jpg', 1500, 500);

        $response = $this->actingAs($this->user, 'api')
            ->postJson(route('banner.upload'), [
                'banner' => $file,
            ]);

        $response->assertStatus(201);
        $response->assertJsonStructure(['data' => ['id', 'url_banners']]);

        $bannerData = $response->json('data');
        $bannerPath = str_replace('/storage/', '', $bannerData['url_banners']);

        Storage::disk('public')->assertExists($bannerPath);

        $this->assertDatabaseHas('banners', [
            'id' => $bannerData['id'],
            'url_banners' => $bannerPath
        ]);

        $this->assertDatabaseHas('users', [
            'id' => $this->user->id,
            'banner_id' => $bannerData['id']
        ]);
    }

    /** @test */
    public function an_unauthenticated_user_cannot_upload_a_banner()
    {
        Storage::fake('public');
        $file = UploadedFile::fake()->image('banner.jpg');
        $response = $this->postJson(route('banner.upload'), ['banner' => $file]);
        $response->assertStatus(401);
    }

    /** @test */
    public function banner_upload_fails_if_file_is_not_an_image()
    {
        Storage::fake('public');
        $file = UploadedFile::fake()->create('document.pdf', 100);
        $response = $this->actingAs($this->user, 'api')
            ->postJson(route('banner.upload'), ['banner' => $file]);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors('banner');
    }

    /** @test */
    public function banner_upload_fails_if_dimensions_are_invalid()
    {
        Storage::fake('public');
        $file = UploadedFile::fake()->image('small_banner.jpg', 100, 50);
        $response = $this->actingAs($this->user, 'api')
            ->postJson(route('banner.upload'), ['banner' => $file]);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors('banner');
    }

    /** @test */
    public function it_can_show_a_banner()
    {
        Storage::fake('public');
        $banner = Banner::factory()->create();
        Storage::disk('public')->put($banner->url_banners, 'dummy-content');

        $response = $this->actingAs($this->user, 'api')
            ->getJson(route('banner.show', $banner->id));

        $response->assertStatus(200);
        $response->assertJson([
            'data' => [
                'id' => $banner->id,
                'url_banners' => Storage::url($banner->url_banners),
            ]
        ]);
    }
}