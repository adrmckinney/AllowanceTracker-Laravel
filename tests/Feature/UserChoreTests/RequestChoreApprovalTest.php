<?php

namespace Tests\Feature\UserChoreTests;

use App\Data\Enums\UserChoreApprovalStatuses;
use App\Models\Chore;
use Tests\APITestCase;


class RequestChoreApprovalTest extends APITestCase
{
    protected $chore;

    public function setUp(): void
    {
        parent::setUp();

        $this->chore = Chore::factory()->create();
    }

    /** @test */
    public function admin_user_can_request_approval()
    {
        $this->initAdminUser();
        $this->canRequestApproval();
    }

    /** @test */
    public function parent_user_can_request_approval()
    {
        $this->initParentUser();
        $this->canRequestApproval();
    }

    /** @test */
    public function child_user_can_request_approval()
    {
        $this->initChildUser();
        $this->canRequestApproval();
    }

    /** @test */
    public function no_access_user_cannot_request_approval()
    {
        $this->initNoAccessUser();
        $this->cannotRequestApproval();
    }

    /** @test */
    public function no_access_cannot_request_approval_without_token()
    {
        $this->initNoTokenAccessUser();
        $this->cannotRequestApprovalWithoutToken();
    }

    private function canRequestApproval()
    {
        $userChore = $this->createUserChore();

        $response = $this->urlConfig('put', 'user/chore/request', [
            'id' => $userChore->id,
            'approval_requested' => true
        ]);

        $response->assertJsonPath('chore_id', $this->chore->id)
            ->assertJsonPath('approval_requested', true)
            ->assertJsonPath('approval_status', UserChoreApprovalStatuses::$PENDING);
        $this->assertNotNull($response['approval_request_date']);
    }

    private function cannotRequestApproval()
    {
        $userChore = $this->createUserChore();

        $response = $this->urlConfig('put', 'user/chore/request', [
            'id' => $userChore->id,
            'approval_requested' => true
        ]);
        $errorMessage = $response->exception->getMessage();

        $response->assertStatus(403);
        $this->assertEquals('You do not have access to request approval', $errorMessage);
    }

    public function cannotRequestApprovalWithoutToken()
    {
        $userChore = $this->createUserChore();

        $response = $this->urlConfig('delete', "user-chore/{$userChore->id}/remove");

        $errorMessage = $response->exception->getMessage();

        $response->assertStatus(401);
        $this->assertEquals('Unauthenticated.', $errorMessage);
    }
}
