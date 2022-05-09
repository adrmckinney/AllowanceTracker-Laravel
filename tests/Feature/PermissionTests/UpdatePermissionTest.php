<?php

namespace Tests\Feature\PermissionTests;

use Tests\APITestCase;


class UpdatePermissionTest extends APITestCase
{
    protected $input;

    public function setUp(): void
    {
        parent::setUp();

        $this->input = [
            'name' => 'new_name',
            'display_name' => 'New Name'
        ];
    }

    /** @test */
    public function admin_user_can_update_permission()
    {
        $this->initAdminUser();
        $this->canUpdatePermission();
    }

    /** @test */
    public function parent_can_update_permission()
    {
        $this->initParentUser();
        $this->canUpdatePermission();
    }

    /** @test */
    public function child_cannot_update_permission()
    {
        $this->initChildUser();
        $this->cannotUpdatePermission();
    }

    private function canUpdatePermission()
    {
        $permission = $this->getAllPermissions()->first();
        $response = $this->put("/api/permission/update", [...$this->input, 'id' => $permission->id]);

        $responseName = $response->baseResponse->original->name;

        $response->assertStatus(200);
        $this->assertEquals($this->input['name'], $responseName);
    }

    private function cannotUpdatePermission()
    {
        $permission = $this->getAllPermissions()->first();
        $response = $this->put("/api/permission/update", [...$this->input, 'id' => $permission->id]);
        $errorMessage = $response->exception->getMessage();

        $response->assertStatus(403);
        $this->assertEquals('You do not have access to update this permission', $errorMessage);
    }
}
