<?php

namespace Tests\Feature;

use App\Models\Permission;
use Tests\APITestCase;


class PermissionsControllerTest extends APITestCase
{
    protected $permissions;

    public function setUp(): void
    {
        parent::setUp();

        $this->permissions = Permission::factory()->count(2)->create();
    }

    // /** @test */
    // public function can_get_permission()
    // {
    //     $this->initTestUser();
    //     $this->getPermission();
    // }

    // /** @test */
    // public function can_get_permissions()
    // {
    //     $this->initTestUser();
    //     $this->getPermissions();
    // }

    // /** @test */
    // public function can_create_permission()
    // {
    //     $this->initTestUser();
    //     $this->createPermission();
    // }

    /** @test */
    public function can_assign_user_permission_level()
    {
        $this->initTestUser();
        $this->addPermission();
    }

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

        $this->echoResponse($response);
        $response->assertStatus(201);
        $response->assertJsonPath('name', $input['name']);
    }

    private function addPermission()
    {
        $permissionName = $this->permissions->first()->name;
        $userId = $this->authUser->id;

        $response = $this->post('/api/permission/add', [
            'user_id' => $userId,
            'name' => $permissionName
        ]);

        $this->echoResponse($response);
        // $response = $this->post('/api/permission/add', [
        //     'user_id' => $this->authUser->id,
        //     'permission_id' => $this->authUser->id
        // ]);


        // $response->assertStatus(200);
        // $response->assertJsonPath($target, $new);
        // $this->assertNotEquals($old, $new);
    }


    public function cannotUpdateUser($target, $old, $new)
    {
        $response = $this->put('/api/user/update', [$target => $new]);
        $error = $response['error'];

        $response->assertStatus(200);
        $this->assertEquals("Only a parent has access to change this", $error);
    }
}
