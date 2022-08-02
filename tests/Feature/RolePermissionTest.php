<?php

namespace Tests\Feature;

use App\Models\User;
use Spatie\Permission\Models\Role;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class RolePermissionTest extends TestCase
{
    use RefreshDatabase;

    private $userToken;
    private $users;
    private function register()
    {
        $response = $this->post('/api/auth/register', [
            'name' => 'Admin User',
            'email' => 'superadmin@databox.lk',
            'password' => 'admin@#321',
            'password_confirmation' => 'admin@#321'
        ]);
        $response->assertStatus(200);
    }

    private function login()
    {
        $login = $this->post('/api/auth/login', [
            'email' => 'superadmin@databox.lk',
            'password' => 'admin@#321',
        ]);
        $login->assertStatus(200);
        $this->userToken = $login['token'];
    }

    private function logout()
    {
        $response = $this->withHeaders([
            'Accept' => "application/json",
            'Authorization' => "Bearer ".$this->userToken
        ])->get('/api/auth/logout');
    }

    public function setUp(): void
    {
        // first include all the normal setUp operations
        parent::setUp();

        // now re-register all the roles and permissions (clears cache and reloads relations)
        $this->app->make(\Spatie\Permission\PermissionRegistrar::class)->registerPermissions();

        // Seed role
        $this->seed(RoleSeeder::class);

        // create Super admin
        $this->register();
        $user = User::where(['email' => 'superadmin@databox.lk'])->first();
        $user->assignRole($_ENV['SUPER_ADMIN_ROLE']);

        $this->login();
    }

    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_set_user_role()
    {
        // Default role is user. So no need to set again
        $response = $this->post('/api/auth/register', [
            'name' => 'User',
            'email' => 'user@databox.lk',
            'password' => 'user@#321',
            'password_confirmation' => 'user@#321'
        ]);
        $response->assertStatus(200);

        $userid = $response['id'];

        // get all roles
        $response = $this->withHeaders([
            'Accept' => "application/json",
            'Authorization' => "Bearer ".$this->userToken
        ])->get("/api/user/{$userid}/roles");

        $response->assertStatus(200);
        $response->assertSee('user');

        // get all permissions
        $response = $this->withHeaders([
            'Accept' => "application/json",
            'Authorization' => "Bearer ".$this->userToken
        ])->get("/api/user/{$userid}/permissions");

        $response->assertStatus(200);
        // User only can view dashboard, view & edit profile
        $response->assertSee('dashboard');
        $response->assertSee('view profile');
        $response->assertSee('edit profile');

        // Other permission must not be there
        $response->assertDontSee('delete profile');
        $response->assertDontSee('view report');
        $response->assertDontSee('generate report');
    }

    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_set_user_extended_permissions()
    {
        // Default role is user. So no need to set again
        $response = $this->post('/api/auth/register', [
            'name' => 'User',
            'email' => 'user@databox.lk',
            'password' => 'user@#321',
            'password_confirmation' => 'user@#321'
        ]);
        $response->assertStatus(200);

        $userid = $response['id'];

        // get all roles
        $response = $this->withHeaders([
            'Accept' => "application/json",
            'Authorization' => "Bearer ".$this->userToken
        ])->get("/api/user/{$userid}/roles");

        $response->assertStatus(200);
        $response->assertSee('user');

        // get all permissions
        $response = $this->withHeaders([
            'Accept' => "application/json",
            'Authorization' => "Bearer ".$this->userToken
        ])->get("/api/user/{$userid}/permissions");

        $response->assertStatus(200);
        // User only can view dashboard, view & edit profile
        $response->assertSee('dashboard');
        $response->assertSee('view profile');
        $response->assertSee('edit profile');

        // Other permission must not be there
        $response->assertDontSee('delete profile');
        $response->assertDontSee('view report');
        $response->assertDontSee('generate report');

        // set admin roles to user
        $response = $this->withHeaders([
            'Accept' => "application/json",
            'Authorization' => "Bearer ".$this->userToken
        ])->post("/api/user/{$userid}/permission", ["permission" => "view report"]);
        $response->assertSee('view report');

    }

    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_set_admin_role()
    {
        $user = User::factory()->create();

        // Set admin role
        $response = $this->withHeaders([
            'Accept' => "application/json",
            'Authorization' => "Bearer ".$this->userToken
        ])->post("/api/user/{$user->id}/role", [
            'role' => 'admin'
        ]);
        $response->assertStatus(200);

        // get all roles
        $response = $this->withHeaders([
            'Accept' => "application/json",
            'Authorization' => "Bearer ".$this->userToken
        ])->get("/api/user/{$user->id}/roles");

        $response->assertStatus(200);
        $response->assertSee('admin');

        // get all permissions
        $response = $this->withHeaders([
            'Accept' => "application/json",
            'Authorization' => "Bearer ".$this->userToken
        ])->get("/api/user/{$user->id}/permissions");

        $response->assertStatus(200);

        // Admin have following permissions
        $response->assertSee('dashboard');
        $response->assertSee('edit profile');
        $response->assertSee('delete profile');
        $response->assertSee('view report');
        $response->assertSee('generate report');
    }
}
