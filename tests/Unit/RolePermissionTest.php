<?php

namespace Tests\Unit;

use App\Models\User;
use Spatie\Permission\Models\Role;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class RolePermissionTest extends TestCase
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
     * @return void
     */
    public function test_user_has_roles_and_permissions()
    {
        $user = \App\Models\User::factory()->create([
            'name' => 'User',
            'email' => 'test@databox.lk',
        ]);
        $user->assignRole('user');

        $this->assertTrue($user->hasRole('user'));
        $this->assertTrue($user->can('dashboard'));
        $this->assertTrue($user->can('edit profile'));
        $this->assertFalse($user->can('delete profile'));
        $this->assertFalse($user->can('generate report'));
    }

    /**
     * @return void
     */
    public function test_admin_has_roles_and_permissions()
    {
        $user = \App\Models\User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@databox.lk',
            'password' => 'admin@#321',
        ]);
        $user->assignRole('admin');

        $this->assertTrue($user->hasRole('admin'));
        $this->assertTrue($user->can('dashboard'));
        $this->assertTrue($user->can('dashboard'));
        $this->assertTrue($user->can('edit profile'));
        $this->assertTrue($user->can('delete profile'));
        $this->assertTrue($user->can('view report'));
        $this->assertTrue($user->can('generate report'));
        $this->assertFalse($user->can('request report'));
        $this->assertFalse($user->can('delete admin'));
    }
    
    /**
     * @return void
     */
    public function test_super_admin_has_roles_and_permissions()
    {
        $user = \App\Models\User::factory()->create([
            'name' => 'Super Admin User',
            'email' => 'superadmin@databox.lk',
            'password' => 'admin@#321',
        ]);
        $user->assignRole($_ENV['SUPER_ADMIN_ROLE']);

        $this->assertTrue($user->hasRole(Role::all()));
        $this->assertTrue($user->hasRole($_ENV['SUPER_ADMIN_ROLE']));
        $this->assertTrue($user->can('dashboard'));
        $this->assertTrue($user->can('edit profile'));
        $this->assertTrue($user->can('delete profile'));
        $this->assertTrue($user->can('view report'));
        $this->assertTrue($user->can('generate report'));
        $this->assertTrue($user->can('request report'));
        $this->assertTrue($user->can('delete admin'));
    }
}
