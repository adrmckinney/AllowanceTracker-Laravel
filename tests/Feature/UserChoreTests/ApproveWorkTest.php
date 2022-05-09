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

    /** @test */
    public function admin_user_can_reject_work()
    {
        $this->initAdminUser();
        $this->canRejectWork();
    }

    /** @test */
    public function parent_user_can_reject_work()
    {
        $this->initParentUser();
        $this->canRejectWork();
    }

    /** @test */
    public function child_user_cannot_reject_work()
    {
        $this->initChildUser();
        $this->cannotRejectWork();
    }

    /** @test */
    public function admin_user_can_reject_work_previously_approved()
    {
        $this->initAdminUser();
        $this->canRejectWorkPreviouslyApproved();
    }

    /** @test */
    public function admin_user_can_approve_work_previously_rejected()
    {
        $this->initAdminUser();
        $this->canApproveWorkPreviouslyRejected();
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
            'approval_status' => UserChoreApprovalStatuses::$APPROVED
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
        $this->assertNull($response['rejected_date']);
    }

    private function canRejectWork()
    {
        $userChore = $this->createUserChore();
        $userChore['approval_requested'] = true;
        $userChore['approval_request_date'] = date('Y-m-d H:i:s', time());
        $userChore['approval_status'] = UserChoreApprovalStatuses::$PENDING;
        $userChore['approval_date'] = date('Y-m-d H:i:s', time());
        $userChore->save();

        $response = $this->put('/api/user-chore/approve-work', [
            'id' => $userChore->id,
            'approval_status' => UserChoreApprovalStatuses::$REJECTED
        ]);

        $response->assertJsonPath('id', $userChore->id)
            ->assertJsonPath('approval_requested', 1)
            ->assertJsonPath('approval_status', UserChoreApprovalStatuses::$REJECTED);
        $this->assertNotNull($response['approval_request_date']);
        $this->assertNotNull($response['rejected_date']);
        $this->assertNull($response['approval_date']);
    }

    private function cannotRejectWork()
    {
        $userChore = $this->createUserChore();
        $userChore['approval_requested'] = true;
        $userChore['approval_request_date'] = date('Y-m-d H:i:s', time());
        $userChore['approval_status'] = UserChoreApprovalStatuses::$PENDING;
        $userChore['approval_date'] = date('Y-m-d H:i:s', time());
        $userChore->save();
        $response = $this->put('/api/user-chore/approve-work', [
            'id' => $userChore->id,
            'approval_status' => UserChoreApprovalStatuses::$REJECTED
        ]);
        $errorMessage = $response->exception->getMessage();

        $response->assertStatus(403);
        $this->assertEquals('You do not have access to reject work', $errorMessage);
    }

    private function canRejectWorkPreviouslyApproved()
    {
        $userChore = $this->createUserChore();
        $userChore['approval_requested'] = true;
        $userChore['approval_request_date'] = date('Y-m-d H:i:s', time());
        $userChore['approval_status'] = UserChoreApprovalStatuses::$APPROVED;
        $userChore['approval_date'] = date('Y-m-d H:i:s', time());
        $userChore->save();

        $response = $this->put('/api/user-chore/approve-work', [
            'id' => $userChore->id,
            'approval_status' => UserChoreApprovalStatuses::$REJECTED
        ]);

        $response->assertJsonPath('id', $userChore->id)
            ->assertJsonPath('approval_requested', 1)
            ->assertJsonPath('approval_status', UserChoreApprovalStatuses::$REJECTED);
        $this->assertNotNull($response['approval_request_date']);
        $this->assertNotNull($response['rejected_date']);
        $this->assertNull($response['approval_date']);
    }

    private function canApproveWorkPreviouslyRejected()
    {
        $userChore = $this->createUserChore();
        $userChore['approval_requested'] = true;
        $userChore['approval_request_date'] = date('Y-m-d H:i:s', time());
        $userChore['approval_status'] = UserChoreApprovalStatuses::$REJECTED;
        $userChore['rejected_date'] = date('Y-m-d H:i:s', time());
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
        $this->assertNull($response['rejected_date']);
    }
}
