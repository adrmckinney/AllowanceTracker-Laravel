<?php

namespace Tests\Feature\UserChoreTests;

use App\Data\Enums\UserChoreApprovalStatuses;
use App\Models\Chore;
use Tests\APITestCase;


class ApproveWorkTest extends APITestCase
{
    protected $chore;

    public function setUp(): void
    {
        parent::setUp();

        $this->chore = Chore::factory()->create();
    }

    /** @test */
    public function admin_user_can_approve_work()
    {
        $this->initAdminUser();
        $this->canApproveWork();
    }

    /** @test */
    public function parent_user_can_approve_work()
    {
        $this->initParentUser();
        $this->canApproveWork();
    }

    /** @test */
    public function child_user_cannot_approve_work()
    {
        $this->initChildUser();
        $this->cannotApproveWork();
    }

    /** @test */
    public function no_access_user_cannot_approve_work()
    {
        $this->initNoAccessUser();
        $this->cannotApproveWork();
    }

    /** @test */
    public function admin_user_can_undo_approval()
    {
        $this->initAdminUser();
        $this->canUnapproveWork();
    }

    private function canApproveWork()
    {
        $userChore = $this->createUserChore();
        $userChore['approval_requested'] = true;
        $userChore['approval_request_date'] = date('Y-m-d H:i:s', time());
        $userChore['approval_status'] = UserChoreApprovalStatuses::$PENDING;
        $userChore->save();

        $response = $this->put('/api/user-chore/approve-work', [
            'id' => $userChore->id,
            'approval_status' => UserChoreApprovalStatuses::$APPROVED
        ]);

        $response->assertJsonPath('id', $userChore->id)
            ->assertJsonPath('approval_requested', 1)
            ->assertJsonPath('approval_status', UserChoreApprovalStatuses::$APPROVED);
        $this->assertNotNull($response['approval_request_date']);
        $this->assertNotNull($response['approval_date']);
    }

    private function cannotApproveWork()
    {
        $userChore = $this->createUserChore();
        $response = $this->put('/api/user-chore/approve-work', [
            'id' => $userChore->id,
            'approval_request' => true
        ]);
        $errorMessage = $response->exception->getMessage();

        $response->assertStatus(403);
        $this->assertEquals('You do not have access to approve work', $errorMessage);
    }

    private function canUnapproveWork()
    {
        $userChore = $this->createUserChore();
        $userChore['approval_requested'] = true;
        $userChore['approval_request_date'] = date('Y-m-d H:i:s', time());
        $userChore['approval_status'] = UserChoreApprovalStatuses::$APPROVED;
        $userChore['approval_date'] = date('Y-m-d H:i:s', time());
        $userChore->save();

        $response = $this->put('/api/user-chore/approve-work', [
            'id' => $userChore->id,
            'approval_status' => UserChoreApprovalStatuses::$PENDING
        ]);

        $response->assertJsonPath('id', $userChore->id)
            ->assertJsonPath('approval_requested', 1)
            ->assertJsonPath('approval_status', UserChoreApprovalStatuses::$PENDING);
        $this->assertNotNull($response['approval_request_date']);
        $this->assertNull($response['approval_date']);
    }
}
