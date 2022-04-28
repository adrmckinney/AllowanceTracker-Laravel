<?php

namespace Tests\Feature;

use App\Http\Controllers\PermissionsController;
use App\Models\Permission;
use App\Models\UsersPermissions;
use Exception;
use Illuminate\Http\Request;
use Tests\APITestCase;


class PermissionsControllerTest extends APITestCase
{
    protected $permissions;

    public function setUp(): void
    {
        parent::setUp();

        $this->permissions = Permission::factory()->count(2)->create();
    }

    /** @test */
    public function can_get_permission()
    {
        $this->initTestUser();
        $this->getPermission();
    }

    /** @test */
    public function can_get_permissions()
    {
        $this->initTestUser();
        $this->getPermissions();
    }

    /** @test */
    public function can_create_permission()
    {
        $this->initTestUser();
        $this->createPermission();
    }

    /** @test */
    public function can_assign_user_permission_level()
    {
        $this->initTestUser();
        $this->addPermission();
    }

    // /** @test */
    // public function cannot_assign_user_permission_level_they_already_have()
    // {
    //     $this->initTestUser();
    //     $this->cannotAddPermissionAlreadyAssigned();
    // }

    private function getPermission()
    {
        $permission = $this->permissions->first();

        $response = $this->get("/api/permission/{$permission->id}");
        $responseName = $response->baseResponse->original->name;

        $response->assertStatus(200);
        $this->assertEquals($permission->name, $responseName);
    }

    private function getPermissions()
    {

        $permissionNames = collect($this->permissions)->map(function ($permission) {
            return $permission->name;
        });

        $response = $this->get('/api/permissions');
        $responseNames = $response->baseResponse->original->map(function ($resPermission) {
            return $resPermission['name'];
        });

        $response->assertStatus(200);
        $this->assertEquals($permissionNames, $responseNames);
    }

    private function createPermission()
    {
        $input = [
            'name' => 'random_name',
            'display_name' => 'Random Name'
        ];

        $response = $this->post('/api/permission/create', $input);

        $response->assertStatus(201);
        $response->assertJsonPath('name', $input['name']);
    }

    private function addPermission()
    {
        $permission = $this->permissions[1];
        $userId = $this->authUser->id;

        UsersPermissions::factory()->create([
            'user_id' => $userId,
            'permission_id' => $permission->id
        ]);

        // dump('authUser perm', $this->authUser->permissions);

        $this->post('/api/permission/add', [
            'user_id' => $userId,
            'name' => $permission->name
        ]);

        $this->assertDatabaseHas('users_permissions', [
            'user_id' => $userId,
            'permission_id' => $permission->id
        ]);
        // $response = $this->post('/api/permission/add', [
        //     'user_id' => $this->authUser->id,
        //     'permission_id' => $this->authUser->id
        // ]);


        // 'name' => PermissionTypes::$STATUSES['parent']['name'],
        // 'display_name' => PermissionTypes::$STATUSES['parent']['display_name'],
    }

    private function cannotAddPermissionAlreadyAssigned()
    {
        $permission = $this->permissions->first();
        $userId = $this->authUser->id;

        $input = new Request([
            'user_id' => $userId,
            'name' => $permission->name
        ]);

        PermissionsController::addPermission($input);

        $response = $this->post('/api/permission/add', [
            'user_id' => $userId,
            'name' => $permission->name
        ]);

        $this->expectExceptionMessage("Permission: User already has this permission - {$permission->name}");
    }


    public function cannotUpdateUser($target, $old, $new)
    {
        $response = $this->put('/api/user/update', [$target => $new]);
        $error = $response['error'];

        $response->assertStatus(200);
        $this->assertEquals("Only a parent has access to change this", $error);
    }
}
