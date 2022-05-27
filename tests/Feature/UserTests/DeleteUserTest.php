<?php

namespace Tests\Feature\UserTests;

use App\Models\User;
use Tests\APITestCase;


class DeleteUserTest extends APITestCase
{
    protected $differntUser;

    public function setUp(): void
    {
        parent::setUp();

        $this->differntUser = User::factory()->create();
    }

    /** @test */
    public function admin_can_delete_self()
    {
        $this->initAdminUser();
        $this->canDeleteUser();
    }

    /** @test */
    public function parent_can_delete_self()
    {
        $this->initParentUser();
        $this->canDeleteUser();
    }

    /** @test */
    public function child_can_delete_self()
    {
        $this->initChildUser();
        $this->canDeleteUser();
    }

    /** @test */
    public function admin_can_delete_a_different_user()
    {
        $this->initAdminUser();
        $this->canDeleteUser($this->differntUser);
    }

    /** @test */
    public function parent_can_delete_a_different_user()
    {
        $this->initParentUser();
        $this->canDeleteUser($this->differntUser);
    }

    /** @test */
    public function child_cannot_delete_a_different_user()
    {
        $this->initChildUser();
        $this->cannotDeleteUser($this->differntUser);
    }

    /** @test */
    public function no_access_cannot_delete_self()
    {
        $this->initNoAccessUser();
        $this->cannotDeleteUser();
    }

    /** @test */
    public function no_access_cannot_delete_user_without_token()
    {
        $this->initNoTokenAccessUser();
        $this->cannotDeleteUserWithoutToken();
    }

    private function canDeleteUser($differntUser = null)
    {
        $user = $differntUser ? $differntUser : $this->authUser;

        $response = $this->urlConfig('delete', "user/{$user->id}");

        $response->assertStatus(200);
        $response->assertJsonPath('name', $user->name);
    }

    public function cannotDeleteUser($differntUser = null)
    {
        $user = $differntUser ? $differntUser : $this->authUser;

        $response = $this->urlConfig('delete', "user/{$user->id}");

        $errorMessage = $response->exception->getMessage();

        $response->assertStatus(403);
        $this->assertEquals('You do not have access to delete this user', $errorMessage);
    }

    public function cannotDeleteUserWithoutToken()
    {
        $user = $this->authUser;

        $response = $this->urlConfig('delete', "user/{$user->id}");

        $errorMessage = $response->exception->getMessage();

        $response->assertStatus(401);
        $this->assertEquals('Unauthenticated.', $errorMessage);
    }
}
