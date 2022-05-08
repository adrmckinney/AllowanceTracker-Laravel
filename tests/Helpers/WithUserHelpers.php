<?php

namespace Tests\Helpers;

use App\Models\Permission;
use App\Models\User;

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
        return $this->authUser;
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
}
