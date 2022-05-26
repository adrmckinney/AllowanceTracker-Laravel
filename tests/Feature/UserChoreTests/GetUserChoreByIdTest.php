<?php

namespace Tests\Feature\UserChoreTests;

use App\Data\Enums\ChoreApprovalStatuses;
use App\Models\Chore;
use App\Models\UserChore;
use Tests\APITestCase;


class GetUserChoreByIdTest extends APITestCase
{
    protected $chore;

    public function setUp(): void
    {
        parent::setUp();

        $this->chore = Chore::factory()->create();
    }

    /** @test */
    public function admin_user_can_get_user_chore_by_id()
    {
        $this->initAdminUser();
        $this->getUserChoreById();
    }

    /** @test */
    public function parent_user_can_get_user_chore_by_id()
    {
        $this->initParentUser();
        $this->getUserChoreById();
    }

    /** @test */
    public function child_user_can_get_user_chore_by_id()
    {
        $this->initChildUser();
        $this->getUserChoreById();
    }

    /** @test */
    public function no_access_user_cannot_get_user_chore_by_id()
    {
        $this->initNoAccessUser();
        $this->cannotGetUserChoreById();
    }

    /** @test */
    public function no_access_cannot_get_user_chore_without_token()
    {
        $this->initNoTokenAccessUser();
        $this->cannotGetUserChoreWithoutToken();
    }

    private function getUserChoreById()
    {
        $userChore = $this->createUserChore();
        $response = $this->urlConfig('get', "user-chore/{$userChore->id}");

        $response->assertJsonPath('user_id', $this->authUser->id)
            ->assertJsonPath('chore_id', $this->chore->id)
            ->assertJsonPath('approval_requested', $userChore->approval_requested === false ? 0 : 1)->assertJsonPath('approval_request_date', $userChore->approval_request_date)
            ->assertJsonPath('approval_status', $userChore->approval_status)
            ->assertJsonPath('approval_date', $userChore->approval_date);
    }

    private function cannotGetUserChoreById()
    {
        $userChore = $this->createUserChore();
        $response = $this->urlConfig('get', "user-chore/{$userChore->id}");
        $errorMessage = $response->exception->getMessage();

        $response->assertStatus(403);
        $this->assertEquals('You do not have access to get this chore', $errorMessage);
    }

    public function cannotGetUserChoreWithoutToken()
    {
        $userChore = $this->createUserChore();

        $response = $this->urlConfig('get', "user-chore/{$userChore->id}");

        $errorMessage = $response->exception->getMessage();

        $response->assertStatus(401);
        $this->assertEquals('Unauthenticated.', $errorMessage);
    }
}
