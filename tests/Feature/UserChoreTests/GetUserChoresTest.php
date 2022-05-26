<?php

namespace Tests\Feature\UserChoreTests;

use App\Models\Chore;
use App\Models\User;
use Tests\APITestCase;


class GetUserChoresTest extends APITestCase
{
    protected $user, $chore;

    public function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->chores = Chore::factory()->count(3)->create();
    }

    /** @test */
    public function admin_user_can_get_user_chores()
    {
        $this->initAdminUser();
        $this->getUserChores();
    }

    /** @test */
    public function parent_user_can_get_user_chores()
    {
        $this->initParentUser();
        $this->getUserChores();
    }

    /** @test */
    public function child_user_can_get_user_chores()
    {
        $this->initChildUser();
        $this->getUserChores('self');
    }

    /** @test */
    public function no_access_user_cannot_get_user_chores()
    {
        $this->initNoAccessUser();
        $this->cannotGetUserChores();
    }

    /** @test */
    public function no_access_cannot_get_user_chores_without_token()
    {
        $this->initNoTokenAccessUser();
        $this->cannotGetUserChoresWithoutToken();
    }

    private function getUserChores($target = null)
    {
        if ($target === 'self') {
            $userChores = $this->createChoresWithSameUser($this->authUser, $this->chores);
            $response = $this->urlConfig('get', "user/{$this->authUser->id}/chores");
        } else {
            $userChores = $this->createChoresWithSameUser($this->user, $this->chores);
            $response = $this->urlConfig('get', "user/{$this->user->id}/chores");
        }

        $userChoreIds = [];
        $responseUserChoreIds = [];
        $userChoreUserIds = [];
        $responseUserChoreUserIds = [];
        $userChoreChoreIds = [];
        $responseUserChoreChoreIds = [];

        foreach ($userChores as $userChore) {
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

    private function cannotGetUserChores()
    {
        $userChores = $this->createChoresWithSameUser($this->user, $this->chores);
        $response = $this->urlConfig('get', "user/{$this->user->id}/chores");

        $errorMessage = $response->exception->getMessage();

        $response->assertStatus(403);
        $this->assertEquals('You do not have access to get chores', $errorMessage);
    }

    public function cannotGetUserChoresWithoutToken()
    {
        $response = $this->urlConfig('get', "user/{$this->user->id}/chores");

        $errorMessage = $response->exception->getMessage();

        $response->assertStatus(401);
        $this->assertEquals('Unauthenticated.', $errorMessage);
    }
}
