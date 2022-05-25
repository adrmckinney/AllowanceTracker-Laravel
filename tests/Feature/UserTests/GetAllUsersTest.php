<?php

namespace Tests\Feature\UserTests;

use App\Data\Enums\PermissionTypes;
use App\Models\User;
use App\Models\UsersPermissions;
use Tests\APITestCase;


class GetAllUserSTest extends APITestCase
{
    protected $user1, $user2, $user3;

    public function setUp(): void
    {
        parent::setUp();

        $this->user1 = User::factory()->create();
        $this->user2 = User::factory()->create();
        $this->user3 = User::factory()->create();
    }

    /** @test */
    public function admin_can_get_all_users()
    {
        $this->initAdminUser();
        $expectedPermissions = [
            PermissionTypes::$ADMIN,
            PermissionTypes::$PARENT,
            PermissionTypes::$CHILD
        ];
        $this->canGetAllUsers(3, $expectedPermissions);
    }

    /** @test */
    public function parent_can_get_all_users_parent_or_lower()
    {
        $this->initParentUser();
        $expectedPermissions = [
            PermissionTypes::$PARENT,
            PermissionTypes::$CHILD
        ];
        $this->canGetAllUsers(2, $expectedPermissions);
    }

    /** @test */
    public function child_cannot_get_any_users()
    {
        $this->initChildUser();
        $this->cannotGetAllUsers();
    }

    /** @test */
    public function no_access_cannot_get_any_users()
    {
        $this->initNoAccessUser();
        $this->cannotGetAllUsers();
    }

    /** @test */
    public function no_access_cannot_get_any_users_without_token()
    {
        $this->initNoTokenAccessUser();
        $this->cannotGetUsersWithoutToken();
    }

    private function canGetAllUsers($count, $expectedPermissions)
    {
        $this->createUserPermissions();
        $response = $this->urlConfig('get', 'users');

        $responsePermissions = $response->baseResponse->original->map(function ($resPermission) {
            return $resPermission['user_permission'];
        });

        $response->assertStatus(200);

        $response->assertJsonCount($count);
        $this->assertEquals($expectedPermissions, $responsePermissions->toArray());
    }

    public function cannotGetAllUsers()
    {
        $response = $this->urlConfig('get', 'users');

        $errorMessage = $response->exception->getMessage();

        $response->assertStatus(403);
        $this->assertEquals('You do not have access to get users', $errorMessage);
    }

    public function cannotGetUsersWithoutToken()
    {
        $user = $this->authUser;

        $response = $this->urlConfig('get', 'users');

        $errorMessage = $response->exception->getMessage();

        $response->assertStatus(401);
        $this->assertEquals('Unauthenticated.', $errorMessage);
    }

    private function createUserPermissions()
    {
        UsersPermissions::factory()->create([
            'user_id' => $this->user1->id,
            'permission_id' => PermissionTypes::$ADMIN
        ]);
        UsersPermissions::factory()->create([
            'user_id' => $this->user2->id,
            'permission_id' => PermissionTypes::$PARENT
        ]);
        UsersPermissions::factory()->create([
            'user_id' => $this->user3->id,
            'permission_id' => PermissionTypes::$CHILD
        ]);
    }
}
