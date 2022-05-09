<?php

namespace Tests\Feature\PermissionTests;

use Tests\APITestCase;


class GetPermissionsTest extends APITestCase
{
    /** @test */
    public function admin_user_can_get_permissions()
    {
        $this->initAdminUser();
        $this->getPermissions();
    }

    /** @test */
    public function parent_user_can_get_permissions()
    {
        $this->initParentUser();
        $this->getPermissions();
    }

    /** @test */
    public function child_user_cannon_get_permissions()
    {
        $this->initChildUser();
        $this->cannotGetPermissions();
    }

    private function getPermissions()
    {

        $permissionNames = $this->getAllPermissions()->map(function ($permission) {
            return $permission->name;
        });

        $response = $this->get('/api/permissions');
        $responseNames = $response->baseResponse->original->map(function ($resPermission) {
            return $resPermission['name'];
        });

        $response->assertStatus(200);
        $this->assertContains($permissionNames->first(), $responseNames);
        $this->assertContains($permissionNames[1], $responseNames);
        $this->assertContains($permissionNames[2], $responseNames);
    }

    private function cannotGetPermissions()
    {
        $response = $this->get('/api/permissions');
        $errorMessage = $response->exception->getMessage();

        $response->assertStatus(403);
        $this->assertEquals('You do not have access to get permissions', $errorMessage);
    }
}
