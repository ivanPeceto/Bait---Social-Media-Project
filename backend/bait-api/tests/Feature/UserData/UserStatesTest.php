<?php

namespace Tests\Feature\UserData;

use App\Modules\UserData\Domain\Models\User;
use App\Modules\UserData\Domain\Models\UserRole;
use App\Modules\UserData\Domain\Models\UserState;
use Database\Seeders\AvatarSeeder;
use Database\Seeders\BannerSeeder;
use Database\Seeders\UserRolesSeeder;
use Database\Seeders\UserStateSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserStatesTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected User $regularUser;

    protected function setUp(): void
    {
        parent::setUp();

        // Ejecutamos todos los seeders necesarios para crear usuarios.
        $this->seed(UserRolesSeeder::class);
        $this->seed(UserStateSeeder::class);
        $this->seed(AvatarSeeder::class);
        $this->seed(BannerSeeder::class);

        $adminRole = UserRole::where('name', 'admin')->first();
        $this->adminUser = User::factory()->create(['role_id' => $adminRole->id]);

        $userRole = UserRole::where('name', 'user')->first();
        $this->regularUser = User::factory()->create(['role_id' => $userRole->id]);
    }

    /** @test */
    public function an_admin_can_get_a_list_of_states()
    {
        $response = $this->actingAs($this->adminUser, 'api')->getJson(route('states.index'));

        $response->assertStatus(200);
        $response->assertJsonStructure(['data' => [['id', 'name']]]);
        $response->assertJsonFragment(['name' => 'active']);
    }

    /** @test */
    public function an_admin_can_create_a_state()
    {
        $stateData = ['name' => 'banned'];
        $response = $this->actingAs($this->adminUser, 'api')->postJson(route('states.create'), $stateData);

        $response->assertStatus(201);
        $response->assertJsonFragment($stateData);
        $this->assertDatabaseHas('user_states', $stateData);
    }

    /** @test */
    public function a_regular_user_cannot_create_a_state()
    {
        $stateData = ['name' => 'banned'];
        $response = $this->actingAs($this->regularUser, 'api')->postJson(route('states.create'), $stateData);

        $response->assertStatus(403);
    }

    /** @test */
    public function an_admin_can_update_a_state()
    {
        $state = UserState::factory()->create(['name' => 'to-update']);
        $updateData = ['name' => 'updated-state'];

        $response = $this->actingAs($this->adminUser, 'api')->putJson(route('states.update', $state->id), $updateData);

        $response->assertStatus(200);
        $response->assertJsonFragment($updateData);
        $this->assertDatabaseHas('user_states', ['id' => $state->id, 'name' => 'updated-state']);
    }

    /** @test */
    public function a_regular_user_cannot_update_a_state()
    {
        $state = UserState::factory()->create();
        $updateData = ['name' => 'updated-state'];

        $response = $this->actingAs($this->regularUser, 'api')->putJson(route('states.update', $state->id), $updateData);

        $response->assertStatus(403);
    }

    /** @test */
    public function an_admin_can_delete_a_state()
    {
        $state = UserState::factory()->create();
        $response = $this->actingAs($this->adminUser, 'api')->deleteJson(route('states.destroy', $state->id));

        $response->assertStatus(204);
        $this->assertDatabaseMissing('user_states', ['id' => $state->id]);
    }

    /** @test */
    public function a_regular_user_cannot_delete_a_state()
    {
        $state = UserState::factory()->create();
        $response = $this->actingAs($this->regularUser, 'api')->deleteJson(route('states.destroy', $state->id));

        $response->assertStatus(403);
    }
}