<?php

namespace Tests\Feature\UserChoreTests;

use App\Data\Enums\ChoreApprovalStatuses;
use App\Models\Chore;
use App\Models\UserChore;
use Tests\APITestCase;


class GetUserChoresTest extends APITestCase
{
    protected $chores;

    public function setUp(): void
    {
        parent::setUp();

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
        $this->getUserChores();
    }

    /** @test */
    public function no_access_user_cannot_get_user_chores()
    {
        $this->initNoAccessUser();
        $this->cannotGetUserChores();
    }

    private function getUserChores()
    {
        $userChores = $this->createChoresWithSameUser($this->authUser, $this->chores);
        $response = $this->get("/api/user-chore/get-user-chores/{$this->authUser->id}");

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
        $this->createChoresWithSameUser($this->authUser, $this->chores);
        $response = $this->get("/api/user-chore/get-user-chores/{$this->authUser->id}");
        $errorMessage = $response->exception->getMessage();

        $response->assertStatus(403);
        $this->assertEquals('You do not have access to get chores', $errorMessage);
    }
}
