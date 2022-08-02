<?php

namespace Tests\Feature;

use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class UserAuthTest extends TestCase
{
    use RefreshDatabase;

    /**
     * User registration correct test
     *
     * @return void
     */
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

    public function setUp(): void
    {
        // first include all the normal setUp operations
        parent::setUp();

        // now re-register all the roles and permissions (clears cache and reloads relations)
        $this->app->make(\Spatie\Permission\PermissionRegistrar::class)->registerPermissions();

        $this->seed(RoleSeeder::class);

        $this->register();
    }


    /**
     * User login fail test 1
     *
     * @return void
     */
    public function test_login_no_password()
    {
        $response = $this->post('/api/auth/login', [
            'email' => 'superadmin@databox.lk',
        ]);

        $response->assertStatus(302);
        // $response->assertSee("The password field is required.");
    }

    /**
     * User login fail test 2
     *
     * @return void
     */
    public function test_login_no_email()
    {
        $response = $this->post('/api/auth/login', [
            'password' => 'admin@#321',
        ]);

        $response->assertStatus(302);
        // $response->assertSee("The email field is required.");
    }

    /**
     * User login fail test 3
     *
     * @return void
     */
    public function test_login_invalid_email()
    {
        $response = $this->post('/api/auth/login', [
            'email' => 'admin_databox.lk',
            'password' => 'admin@#321',
        ]);

        $response->assertStatus(302);
    }

    /**
     * User login fail test 4
     *
     * @return void
     */
    public function test_login_wrong_password()
    {
        $response = $this->post('/api/auth/login', [
            'email' => 'superadmin@databox.lk',
            'password' => 'admin',
        ]);

        $response->assertStatus(401);
    }

    /**
     * User login test
     *
     * @return void
     */
    public function test_login()
    {
        $response = $this->post('/api/auth/login', [
            'email' => 'superadmin@databox.lk',
            'password' => 'admin@#321',
        ]);

        $response->assertStatus(200);
        $response->assertSee("token");
    }

    /**
     * User login and check role and permissions
     *
     * @return void
     */
    public function test_login_and_get_role_and_permission()
    {
        $login = $this->post('/api/auth/login', [
            'email' => 'superadmin@databox.lk',
            'password' => 'admin@#321',
        ]);
        // $login->dump();
        $login->assertStatus(200);
        $login->assertSee("token");
        $login->assertSee("roles");
        $login->assertJson(["roles" =>  [
            0 => "user"
          ]
        ]);

        $response = $this->withHeaders([
            'Accept' => "application/json",
            'Authorization' => "Bearer ".$login['token']
        ])->get('/api/user/roles');

        // $response->dump();
        $response->assertStatus(200);
        $login->assertSee("user");

        $response = $this->withHeaders([
            'Accept' => "application/json",
            'Authorization' => "Bearer ".$login['token']
        ])->get('/api/user/permissions');

        // $response->dump();
        $response->assertStatus(200);
        $response->assertSee('dashboard');
        $response->assertSee('view profile');
        $response->assertSee('edit profile');
    }

    /**
     * User login and logout
     *
     * @return void
     */
    public function test_login_and_logout()
    {
        $login = $this->post('/api/auth/login', [
            'email' => 'superadmin@databox.lk',
            'password' => 'admin@#321',
        ]);
        $login->assertStatus(200);
        $login->assertSee("token");

        $response = $this->withHeaders([
            'Accept' => "application/json",
            'Authorization' => "Bearer ".$login['token']
        ])->get('/api/auth/logout');

        $response->assertStatus(200);
    }
}
