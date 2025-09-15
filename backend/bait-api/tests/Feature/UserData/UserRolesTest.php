<?php

namespace Tests\Feature\UserData;

use App\Modules\UserData\Domain\Models\User;
use App\Modules\UserData\Domain\Models\UserRole;
use Database\Seeders\UserRolesSeeder;
use Database\Seeders\UserStateSeeder;
use Database\Seeders\BannerSeeder;
use Database\Seeders\AvatarSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserRolesTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected User $regularUser;

    protected function setUp(): void
    {
        parent::setUp();

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
    public function an_admin_can_get_a_list_of_roles()
    {
        $response = $this->actingAs($this->adminUser, 'api')->getJson(route('roles.index'));

        $response->assertStatus(200);
        $response->assertJsonStructure(['data' => [['id', 'name']]]);
        $response->assertJsonFragment(['name' => 'admin']);
    }

    /** @test */
    public function an_admin_can_create_a_role()
    {
        $roleData = ['name' => 'new-role'];
        $response = $this->actingAs($this->adminUser, 'api')->postJson(route('roles.create'), $roleData);

        $response->assertStatus(201);
        $response->assertJsonFragment($roleData);
        $this->assertDatabaseHas('user_roles', $roleData);
    }

    /** @test */
    public function a_regular_user_cannot_create_a_role()
    {
        $roleData = ['name' => 'new-role'];
        $response = $this->actingAs($this->regularUser, 'api')->postJson(route('roles.create'), $roleData);

        $response->assertStatus(403);
    }
    
    /** @test */
    public function an_admin_can_update_a_role()
    {
        $role = UserRole::factory()->create(['name' => 'to-be-updated']);
        $updateData = ['name' => 'updated-role'];

        $response = $this->actingAs($this->adminUser, 'api')->putJson(route('roles.update', $role->id), $updateData);

        $response->assertStatus(200);
        $response->assertJsonFragment($updateData);
        $this->assertDatabaseHas('user_roles', ['id' => $role->id, 'name' => 'updated-role']);
    }

    /** @test */
    public function a_regular_user_cannot_update_a_role()
    {
        $role = UserRole::factory()->create();
        $updateData = ['name' => 'updated-role'];

        $response = $this->actingAs($this->regularUser, 'api')->putJson(route('roles.update', $role->id), $updateData);

        $response->assertStatus(403);
    }
    
    /** @test */
    public function an_admin_can_delete_a_role()
    {
        $role = UserRole::factory()->create();

        $response = $this->actingAs($this->adminUser, 'api')->deleteJson(route('roles.destroy', $role->id));

        $response->assertStatus(204);
        $this->assertDatabaseMissing('user_roles', ['id' => $role->id]);
    }

    /** @test */
    public function a_regular_user_cannot_delete_a_role()
    {
        $role = UserRole::factory()->create();

        $response = $this->actingAs($this->regularUser, 'api')->deleteJson(route('roles.destroy', $role->id));

        $response->assertStatus(403);
    }
}