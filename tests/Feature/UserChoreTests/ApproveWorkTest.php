<?php

namespace Tests\Feature\UserChoreTests;

use App\Data\Enums\TransactionTypes;
use App\Data\Enums\UserChoreApprovalStatuses;
use App\Models\Chore;
use App\Models\User;
use Tests\APITestCase;


class ApproveWorkTest extends APITestCase
{
    protected $user, $chore;

    public function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->chore = Chore::factory()->create(['cost' => 1000]);
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
    public function parent_user_can_undo_approval()
    {
        $this->initParentUser();
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
    public function parent_user_can_reject_work_previously_approved()
    {
        $this->initParentUser();
        $this->canRejectWorkPreviouslyApproved();
    }

    /** @test */
    public function parent_user_can_approve_work_previously_rejected()
    {
        $this->initParentUser();
        $this->canApproveWorkPreviouslyRejected();
    }

    /** @test */
    public function wallet_can_be_negative()
    {
        $this->initParentUser();
        $this->walletCanBeNegativeTest();
    }

    private function canApproveWork()
    {
        $userChore = $this->createUserChoreForDifferentUser($this->user, $this->chore);
        $this->setupApprovalRequestWithPendingStatus($userChore);
        $userWalletBeforeApproval = $this->user->wallet;

        $response = $this->put('/api/user-chore/approve-work', [
            'id' => $userChore->id,
            'approval_status' => UserChoreApprovalStatuses::$APPROVED
        ]);
        $this->user->refresh();
        $userWalletAfterApproval = $this->user->wallet;

        $response->assertJsonPath('id', $userChore->id)
            ->assertJsonPath('approval_requested', 1)
            ->assertJsonPath('approval_status', UserChoreApprovalStatuses::$APPROVED);
        $this->assertNotNull($response['approval_request_date']);
        $this->assertNotNull($response['approval_date']);
        $this->assertNotEquals($userWalletAfterApproval, $userWalletBeforeApproval);
        $this->assertEquals($userWalletBeforeApproval + $this->chore->cost, $userWalletAfterApproval);
        $this->assertDatabaseHas('transactions', [
            'user_id' => $userChore->user_id,
            'chore_id' => $userChore->chore_id,
            'transaction_amount' => $this->chore->cost,
            'transaction_type' => TransactionTypes::$DEPOSIT
        ]);
    }

    private function cannotApproveWork()
    {
        $userChore = $this->createUserChoreForDifferentUser($this->user, $this->chore);
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
        $userChore = $this->createUserChoreForDifferentUser($this->user, $this->chore);
        $this->setupApprovedRequest($userChore);
        $userWalletBeforeApproval = $this->user->wallet;

        $response = $this->put('/api/user-chore/approve-work', [
            'id' => $userChore->id,
            'approval_status' => UserChoreApprovalStatuses::$PENDING
        ]);
        $this->user->refresh();
        $userWalletAfterApproval = $this->user->wallet;

        $response->assertJsonPath('id', $userChore->id)
            ->assertJsonPath('approval_requested', 1)
            ->assertJsonPath('approval_status', UserChoreApprovalStatuses::$PENDING);
        $this->assertNotNull($response['approval_request_date']);
        $this->assertNull($response['approval_date']);
        $this->assertNull($response['rejected_date']);
        $this->assertNotEquals($userWalletAfterApproval, $userWalletBeforeApproval);
        $this->assertEquals($userWalletBeforeApproval - $this->chore->cost, $userWalletAfterApproval);
    }

    private function canRejectWork()
    {
        $userChore = $this->createUserChoreForDifferentUser($this->user, $this->chore);
        $this->setupApprovalRequestWithPendingStatus($userChore);
        $userWalletBeforeApproval = $this->user->wallet;

        $response = $this->put('/api/user-chore/approve-work', [
            'id' => $userChore->id,
            'approval_status' => UserChoreApprovalStatuses::$REJECTED
        ]);
        $this->user->refresh();
        $userWalletAfterApproval = $this->user->wallet;

        $response->assertJsonPath('id', $userChore->id)
            ->assertJsonPath('approval_requested', 1)
            ->assertJsonPath('approval_status', UserChoreApprovalStatuses::$REJECTED);
        $this->assertNotNull($response['approval_request_date']);
        $this->assertNotNull($response['rejected_date']);
        $this->assertNull($response['approval_date']);
        $this->assertEquals($userWalletAfterApproval, $userWalletBeforeApproval);
    }

    private function cannotRejectWork()
    {
        $userChore = $this->createUserChoreForDifferentUser($this->user, $this->chore);
        $this->setupApprovalRequestWithPendingStatus($userChore);

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
        $userChore = $this->createUserChoreForDifferentUser($this->user, $this->chore);
        $this->setupApprovedRequest($userChore);
        $userWalletBeforeApproval = $this->user->wallet;

        $response = $this->put('/api/user-chore/approve-work', [
            'id' => $userChore->id,
            'approval_status' => UserChoreApprovalStatuses::$REJECTED
        ]);
        $this->user->refresh();
        $userWalletAfterApproval = $this->user->wallet;

        $response->assertJsonPath('id', $userChore->id)
            ->assertJsonPath('approval_requested', 1)
            ->assertJsonPath('approval_status', UserChoreApprovalStatuses::$REJECTED);
        $this->assertNotNull($response['approval_request_date']);
        $this->assertNotNull($response['rejected_date']);
        $this->assertNull($response['approval_date']);
        $this->assertNotEquals($userWalletAfterApproval, $userWalletBeforeApproval);
        $this->assertEquals($userWalletBeforeApproval - $this->chore->cost, $userWalletAfterApproval);
    }

    private function canApproveWorkPreviouslyRejected()
    {
        $userChore = $this->createUserChoreForDifferentUser($this->user, $this->chore);
        $this->setupRejectedRequest($userChore);
        $userWalletBeforeApproval = $this->user->wallet;

        $response = $this->put('/api/user-chore/approve-work', [
            'id' => $userChore->id,
            'approval_status' => UserChoreApprovalStatuses::$APPROVED
        ]);
        $this->user->refresh();
        $userWalletAfterApproval = $this->user->wallet;

        $response->assertJsonPath('id', $userChore->id)
            ->assertJsonPath('approval_requested', 1)
            ->assertJsonPath('approval_status', UserChoreApprovalStatuses::$APPROVED);
        $this->assertNotNull($response['approval_request_date']);
        $this->assertNotNull($response['approval_date']);
        $this->assertNull($response['rejected_date']);
        $this->assertNotEquals($userWalletAfterApproval, $userWalletBeforeApproval);
        $this->assertEquals($userWalletBeforeApproval + $this->chore->cost, $userWalletAfterApproval);
    }

    private function walletCanBeNegativeTest()
    {

        $chore = Chore::factory()->create(['cost' => 50000]);;
        $userChore = $this->createUserChoreForDifferentUser($this->user, $chore);
        $this->setupApprovedRequest($userChore);
        $userWalletBeforeApproval = $this->user->wallet;

        $response = $this->put('/api/user-chore/approve-work', [
            'id' => $userChore->id,
            'approval_status' => UserChoreApprovalStatuses::$PENDING
        ]);
        $this->user->refresh();
        $userWalletAfterApproval = $this->user->wallet;

        $response->assertJsonPath('id', $userChore->id)
            ->assertJsonPath('approval_requested', 1)
            ->assertJsonPath('approval_status', UserChoreApprovalStatuses::$PENDING);
        $this->assertNotNull($response['approval_request_date']);
        $this->assertNull($response['approval_date']);
        $this->assertNull($response['rejected_date']);
        $this->assertGreaterThan($this->chore->cost, $userWalletBeforeApproval);
        $this->assertEquals($userWalletAfterApproval, $userWalletBeforeApproval - $chore->cost);
    }

    private function setupApprovalRequestWithPendingStatus($userChore)
    {
        $userChore['approval_requested'] = true;
        $userChore['approval_request_date'] = date('Y-m-d H:i:s', time());
        $userChore['approval_status'] = UserChoreApprovalStatuses::$PENDING;
        $userChore->save();
    }

    private function setupApprovedRequest($userChore)
    {
        $userChore['approval_requested'] = true;
        $userChore['approval_request_date'] = date('Y-m-d H:i:s', time());
        $userChore['approval_status'] = UserChoreApprovalStatuses::$APPROVED;
        $userChore['approval_date'] = date('Y-m-d H:i:s', time());
        $userChore->save();

        $this->addChoreCostToWallet();
    }

    private function setupRejectedRequest($userChore)
    {
        $userChore['approval_requested'] = true;
        $userChore['approval_request_date'] = date('Y-m-d H:i:s', time());
        $userChore['approval_status'] = UserChoreApprovalStatuses::$REJECTED;
        $userChore['rejected_date'] = date('Y-m-d H:i:s', time());
        $userChore->save();
    }

    private function addChoreCostToWallet()
    {
        $this->user['wallet'] = $this->user['wallet'] + $this->chore->cost;
        $this->user->save();
    }
}
