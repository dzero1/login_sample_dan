<?php

namespace Tests\Feature;

use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class UserAuthRegisterTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        // first include all the normal setUp operations
        parent::setUp();

        // now re-register all the roles and permissions (clears cache and reloads relations)
        $this->app->make(\Spatie\Permission\PermissionRegistrar::class)->registerPermissions();

        $this->seed(RoleSeeder::class);
    }

    /**
     * User registration fail test 1
     *
     * @return void
     */
    public function test_register_fail_only_name()
    {
        $response = $this->post('/api/auth/register', [
            'name' => 'Admin User',
        ]);

        $response->assertStatus(302);
    }

    /**
     * User registration fail test 2
     *
     * @return void
     */
    public function test_register_fail_only_name_and_email()
    {
        $response = $this->post('/api/auth/register', [
            'name' => 'Admin User',
            'email' => 'superadmin@databox.lk',
        ]);

        $response->assertStatus(302);
    }

    /**
     * User registration fail test 3
     *
     * @return void
     */
    public function test_register_fail_invalid_email()
    {
        $response = $this->post('/api/auth/register', [
            'name' => 'Admin User',
            'email' => 'admin_databox.lk',
            'password' => 'admin@#321',
        ]);

        $response->assertStatus(302);
        // $response->assertSee("The password field is required.");
    }
    
    /**
     * User registration fail test 4
     *
     * @return void
     */
    public function test_register_fail_no_confirm_password()
    {
        $response = $this->post('/api/auth/register', [
            'name' => 'Admin User',
            'email' => 'superadmin@databox.lk',
            'password' => 'admin@#321',
        ]);

        $response->assertStatus(302);
    }

    /**
     * User registration fail test 5
     *
     * @return void
     */
    public function test_register_fail_wrong_confirm_password()
    {
        $response = $this->post('/api/auth/register', [
            'name' => 'Admin User',
            'email' => 'superadmin@databox.lk',
            'password' => 'admin@#321',
            'password_confirmation' => 'admin'
        ]);

        $response->assertStatus(302);
    }

    /**
     * User registration correct test
     *
     * @return void
     */
    public function test_register()
    {
        $response = $this->post('/api/auth/register', [
            'name' => 'Admin User',
            'email' => 'superadmin@databox.lk',
            'password' => 'admin@#321',
            'password_confirmation' => 'admin@#321'
        ]);

        $response->assertStatus(200);
    }

}
