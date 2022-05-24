<?php

namespace Tests\Feature\UserTests;

use App\Models\User;
use Tests\APITestCase;


class GetUserTest extends APITestCase
{

    /** @test */
    public function admin_can_get_user_by_id()
    {
        $this->initAdminUser();
        $this->canGetUser();
    }

    /** @test */
    public function parent_can_get_user_by_id()
    {
        $this->initParentUser();
        $this->canGetUser();
    }

    /** @test */
    public function child_can_get_user_by_id()
    {
        $this->initChildUser();
        $this->canGetUser();
    }

    /** @test */
    public function no_access_can_get_user_by_id()
    {
        $this->initNoAccessUser();
        $this->canGetUser();
    }

    private function canGetUser()
    {
        $user = $this->authUser;

        $permissionId = $this->authUser->permissions->toArray()[0]['permission_id'];
        $response = $this->get("/api/user/{$user->id}", [
            'Authorization' => "Bearer {$user->api_token}",
            'Accept' => 'application/json',
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('name', $user->name);
        $response->assertJsonPath('api_token', $user->api_token);
        $response->assertJsonPath('user_permission', $permissionId);
    }

    public function cannotGetUser()
    {
        $user = $this->authUser;
        $response = $this->get("/api/user/{$user->id}");

        $errorMessage = $response->exception->getMessage();

        $response->assertStatus(403);
        $this->assertEquals('You do not have access', $errorMessage);
    }
}
