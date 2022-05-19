<?php

namespace Tests\Helpers;

use App\Data\Enums\PermissionTypes;
use App\Models\Permission;
use App\Models\User;
use App\Models\UsersPermissions;

/**
 * Trait WithUserHelpers
 * @package Tests\Helpers
 */
trait WithUserHelpers
{
    protected $authUser;

    protected function initAdminUser()
    {
        $this->createPermissions();
        $this->authUser = User::factory()->create();
        $this->post('/api/login', ['username' => $this->authUser->username, 'password' => 'password']);
        $this->authUser->addPermission('admin');

        return $this->authUser;
    }

    protected function initParentUser()
    {
        $this->createPermissions();
        $this->authUser = User::factory()->create();
        $this->post('/api/login', ['username' => $this->authUser->username, 'password' => 'password']);
        $this->authUser->addPermission('parent');

        return $this->authUser;
    }

    protected function initChildUser()
    {
        $this->createPermissions();
        $this->authUser = User::factory()->create();
        $this->post('/api/login', ['username' => $this->authUser->username, 'password' => 'password']);
        $this->authUser->addPermission('child');

        return $this->authUser;
    }

    protected function initNoAccessUser()
    {
        $this->createPermissions();
        $this->authUser = User::factory()->create();
        $this->post('/api/login', ['username' => $this->authUser->username, 'password' => 'password']);
        $this->authUser->addPermission('no_access');
    }

    protected function initTestUser()
    {
        $this->authUser = User::factory()->create();
        $this->post('/api/login', ['username' => $this->authUser->username, 'password' => 'password']);

        return $this->authUser;
    }

    public function createPermissions()
    {
        Permission::factory()->create([
            'name' => 'no_access',
            'display_name' => 'No Access',
        ]);
        Permission::factory()->create([
            'name' => 'admin',
            'display_name' => 'Admin',
        ]);
        Permission::factory()->create([
            'name' => 'parent',
            'display_name' => 'Parent',
        ]);
        Permission::factory()->create([
            'name' => 'child',
            'display_name' => 'Child',
        ]);
    }

    public function createChildUser()
    {
        $this->createPermissions();
        $user = User::factory()->create();
        return UsersPermissions::factory()->create([
            'user_id' => $user->id,
            'permission_id' => PermissionTypes::$CHILD
        ]);
    }
}
