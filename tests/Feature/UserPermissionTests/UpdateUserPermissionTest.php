<?php

namespace Tests\Feature\UserPermissionTests;

use App\Data\Enums\PermissionTypes;
use App\Models\Permission;
use App\Models\User;
use App\Models\UsersPermissions;
use Tests\APITestCase;


class UpdateUserPermissionTest extends APITestCase
{
    protected $user;

    public function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
    }

    /** @test */
    public function admin_user_can_update_parent_permission_level_to_child()
    {
        $this->initAdminUser();
        $this->updatePermissionLevel(PermissionTypes::$PARENT, PermissionTypes::$CHILD);
    }

    /** @test */
    public function parent_user_can_update_parent_permission_level_to_child()
    {
        $this->initParentUser();
        $this->updatePermissionLevel(PermissionTypes::$PARENT, PermissionTypes::$CHILD);
    }

    /** @test */
    public function parent_user_can_update_child_permission_level_to_parent()
    {
        $this->initParentUser();
        $this->updatePermissionLevel(PermissionTypes::$CHILD, PermissionTypes::$PARENT);
    }

    /** @test */
    public function parent_user_can_update_child_permission_level_to_no_access()
    {
        $this->initParentUser();
        $this->updatePermissionLevel(PermissionTypes::$CHILD, PermissionTypes::$NO_ACCESS);
    }

    /** @test */
    public function parent_user_can_update_parent_permission_level_to_no_access()
    {
        $this->initParentUser();
        $this->updatePermissionLevel(PermissionTypes::$PARENT, PermissionTypes::$NO_ACCESS);
    }

    /** @test */
    public function child_user_cannot_update_parent_permission_level_to_child()
    {
        $this->initChildUser();
        $this->cannotUpdatePermission(PermissionTypes::$PARENT, PermissionTypes::$CHILD);
    }

    /** @test */
    public function no_access_user_cannot_update_parent_permission_level_to_child()
    {
        $this->initNoAccessUser();
        $this->cannotUpdatePermission(PermissionTypes::$PARENT, PermissionTypes::$CHILD);
    }

    /** @test */
    public function no_access_user_cannot_update_user_permission_without_token()
    {
        $this->initNoTokenAccessUser();
        $this->cannotUpdateUserPermissionWithoutToken();
    }

    private function updatePermissionLevel($currentLevelId, $newLevelId)
    {
        UsersPermissions::factory()->create([
            'user_id' => $this->user->id,
            'permission_id' => $currentLevelId
        ]);

        $newPermission = $this->getPermission($newLevelId);

        $response = $this->urlConfig('put', 'user/permission/update', [
            'user_id' => $this->user->id,
            'name' => $newPermission->name
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('user_id', $this->user->id)
            ->assertJsonPath('permission_id', $newLevelId);
    }

    public function cannotUpdatePermission($currentLevelId, $newLevelId)
    {
        UsersPermissions::factory()->create([
            'user_id' => $this->user->id,
            'permission_id' => $currentLevelId
        ]);

        $newPermission = $this->getPermission($newLevelId);

        $response = $this->urlConfig('put', 'user/permission/update', [
            'user_id' => $this->user->id,
            'name' => $newPermission->name
        ]);
        $errorMessage = $response->exception->getMessage();

        $response->assertStatus(403);
        $this->assertEquals('You do not have access to update permissions', $errorMessage);
    }

    public function cannotUpdateUserPermissionWithoutToken()
    {
        $response = $this->urlConfig('put', 'user/permission/update', [
            'user_id' => $this->user->id,
        ]);

        $errorMessage = $response->exception->getMessage();

        $response->assertStatus(401);
        $this->assertEquals('Unauthenticated.', $errorMessage);
    }
}
