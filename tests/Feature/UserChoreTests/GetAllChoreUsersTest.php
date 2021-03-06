<?php

namespace Tests\Feature\UserChoreTests;

use App\Models\Chore;
use App\Models\User;
use Tests\APITestCase;


class GetAllChoreUsersTest extends APITestCase
{
    protected $chore, $users;

    public function setUp(): void
    {
        parent::setUp();

        $this->chore = Chore::factory()->create();
        $this->users = User::factory()->count(3)->create();
    }

    /** @test */
    public function admin_user_can_get_all_users_of_chore()
    {
        $this->initAdminUser();
        $this->getChoreUsers();
    }

    /** @test */
    public function parent_user_can_get_all_users_of_chore()
    {
        $this->initParentUser();
        $this->getChoreUsers();
    }

    /** @test */
    public function child_user_can_get_all_users_of_chore()
    {
        $this->initChildUser();
        $this->getChoreUsers('self');
    }

    /** @test */
    public function no_access_user_cannot_get_all_users_of_chore()
    {
        $this->initNoAccessUser();
        $this->cannotGetChoreUsers();
    }

    /** @test */
    public function no_access_cannot_get_chore_users_without_token()
    {
        $this->initNoTokenAccessUser();
        $this->cannotGetChoreUsersWithoutToken();
    }

    private function getChoreUsers($target = null)
    {
        $usersChore = $this->createChoreWithMultipleUsers($this->users, $this->chore);
        $response = $this->urlConfig('get', "users/chore/{$this->chore->id}");

        $userChoreIds = [];
        $responseUserChoreIds = [];
        $userChoreUserIds = [];
        $responseUserChoreUserIds = [];
        $userChoreChoreIds = [];
        $responseUserChoreChoreIds = [];

        foreach ($usersChore as $userChore) {
            array_push($userChoreIds, $userChore->id);
            array_push($userChoreUserIds, $userChore->user_id);
            array_push($userChoreChoreIds, $userChore->chore_id);
        }
        foreach ($response->baseResponse->original as $userChore) {
            array_push($responseUserChoreIds, $userChore->id);
            array_push($responseUserChoreUserIds, $userChore->user_id);
            array_push($responseUserChoreChoreIds, $userChore->chore_id);
        }

        $this->assertEquals($userChoreIds, $responseUserChoreIds);
        $this->assertEquals($userChoreUserIds, $responseUserChoreUserIds);
        $this->assertEquals($userChoreChoreIds, $responseUserChoreChoreIds);
    }

    private function cannotGetChoreUsers()
    {
        $this->createChoreWithMultipleUsers($this->users, $this->chore);
        $response = $this->urlConfig('get', "users/chore/{$this->chore->id}");
        $errorMessage = $response->exception->getMessage();

        $response->assertStatus(403);
        $this->assertEquals('You do not have access to get chores', $errorMessage);
    }

    public function cannotGetChoreUsersWithoutToken()
    {
        $this->createChoreWithMultipleUsers($this->users, $this->chore);

        $response = $this->urlConfig('get', "users/chore/{$this->chore->id}");

        $errorMessage = $response->exception->getMessage();

        $response->assertStatus(401);
        $this->assertEquals('Unauthenticated.', $errorMessage);
    }
}
