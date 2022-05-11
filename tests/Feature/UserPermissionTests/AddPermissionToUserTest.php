<?php

namespace Tests\Feature\UserPermissionTests;

use App\Data\Enums\PermissionTypes;
use App\Models\Permission;
use App\Models\User;
use App\Models\UsersPermissions;
use Tests\APITestCase;


class AddPermissionToUserTest extends APITestCase
{
    protected $user;

    public function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
    }

    /** @test */
    public function admin_user_can_add_parent_permission_level()
    {
        $this->initAdminUser();
        $this->addPermissionLevelParent(PermissionTypes::$PARENT);
    }

    /** @test */
    public function admin_user_can_add_admin_permission_level()
    {
        $this->initAdminUser();
        $this->addPermissionLevelParent(PermissionTypes::$ADMIN);
    }

    /** @test */
    public function parent_user_can_add_parent_permission_level()
    {
        $this->initParentUser();
        $this->addPermissionLevelParent(PermissionTypes::$PARENT);
    }

    /** @test */
    public function parent_user_can_add_admin_permission_level()
    {
        $this->initParentUser();
        $this->addPermissionLevelParent(PermissionTypes::$ADMIN);
    }

    /** @test */
    public function admin_user_can_add_child_permission_level()
    {
        $this->initAdminUser();
        $this->addPermissionLevelParent(PermissionTypes::$CHILD);
    }

    /** @test */
    public function parent_user_can_add_child_permission_level()
    {
        $this->initParentUser();
        $this->addPermissionLevelParent(PermissionTypes::$CHILD);
    }

    /** @test */
    public function child_user_cannot_add_permission()
    {
        $this->initChildUser();
        $this->cannotAddPermission(PermissionTypes::$PARENT);
    }

    /** @test */
    public function no_access_user_cannot_add_permission()
    {
        $this->initNoAccessUser();
        $this->cannotAddPermission(PermissionTypes::$PARENT);
    }

    /** @test */
    public function admin_user_cannot_add_permission_already_assigned()
    {
        $this->initAdminUser();
        $this->cannotAddPermissionAlreadyAssigned(PermissionTypes::$PARENT);
    }


    private function addPermissionLevelParent($permLevel)
    {
        $permission = $this->getPermission($permLevel);

        $response = $this->post('/api/user-permission/add', [
            'user_id' => $this->user->id,
            'name' => $permission->name
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('user_id', $this->user->id)
            ->assertJsonPath('permission_id', $permLevel);
    }

    public function cannotAddPermission($permLevel)
    {
        $permission = $this->getPermission($permLevel);

        $response = $this->post('/api/user-permission/add', [
            'user_id' => $this->user->id,
            'name' => $permission->name
        ]);
        $errorMessage = $response->exception->getMessage();

        $response->assertStatus(403);
        $this->assertEquals('You do not have access to assign permissions', $errorMessage);
    }

    private function cannotAddPermissionAlreadyAssigned($permLevel)
    {
        $permission = $this->getPermission($permLevel);

        UsersPermissions::factory()->create([
            'user_id' => $this->user->id,
            'permission_id' => $permission->id
        ]);

        $response = $this->post('/api/user-permission/add', [
            'user_id' => $this->user->id,
            'name' => $permission->name
        ]);

        $errorMessage = $response->exception->getMessage();

        $response->assertStatus(406);
        $this->assertEquals("Permission: User already has this permission - {$permission->name}", $errorMessage);
    }

    private function getPermission($type)
    {
        switch ($type) {
            case PermissionTypes::$PARENT:
                return $this->permission =
                    Permission::where('name', '=', PermissionTypes::getPermissionName(PermissionTypes::$PARENT))
                    ->first();
            case PermissionTypes::$CHILD:
                return $this->permission =
                    Permission::where('name', '=', PermissionTypes::getPermissionName(PermissionTypes::$CHILD))
                    ->first();
            case PermissionTypes::$ADMIN:
                return $this->permission =
                    Permission::where('name', '=', PermissionTypes::getPermissionName(PermissionTypes::$ADMIN))
                    ->first();
        }
    }
}
