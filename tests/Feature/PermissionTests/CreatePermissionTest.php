<?php

namespace Tests\Feature\PermissionTests;

use Tests\APITestCase;


class CreatePermissionTest extends APITestCase
{
    protected $input;

    public function setUp(): void
    {
        parent::setUp();

        $this->input = [
            'name' => 'random_name',
            'display_name' => 'Random Name'
        ];
    }

    /** @test */
    public function admin_user_can_create_permission()
    {
        $this->initAdminUser();
        $this->createPermission();
    }

    /** @test */
    public function parent_user_can_create_permission()
    {
        $this->initParentUser();
        $this->createPermission();
    }

    /** @test */
    public function child_user_cannot_create_permission()
    {
        $this->initChildUser();
        $this->cannotCreatePermission();
    }

    private function createPermission()
    {
        $response = $this->post('/api/permission/create', $this->input);

        $response->assertStatus(201);
        $response->assertJsonPath('name', $this->input['name']);
    }

    private function cannotCreatePermission()
    {
        $response = $this->post('/api/permission/create', $this->input);
        $errorMessage = $response->exception->getMessage();

        $response->assertStatus(403);
        $this->assertEquals('You do not have access to create a permission', $errorMessage);
    }
}
