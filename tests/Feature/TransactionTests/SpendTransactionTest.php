<?php

namespace Tests\Feature\UserTests;

use App\Data\Enums\TransactionApprovalStatuses;
use App\Data\Enums\TransactionApprovalTypes;
use App\Data\Enums\TransactionTypes;
use App\Models\User;
use Tests\APITestCase;


class SpendTransactionTest extends APITestCase
{
    protected $user, $user2;

    public function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->user2 = User::factory()->create();
    }

    /** @test */
    public function admin_can_spend_money_of_child()
    {
        $this->initAdminUser();
        $this->canSpendMoney();
    }

    /** @test */
    public function parent_can_spend_money_of_child()
    {
        $this->initParentUser();
        $this->canSpendMoney();
    }

    /** @test */
    public function child_can_spend_own_money()
    {
        $this->initChildUser();
        $this->canSpendMoney('self');
    }

    /** @test */
    public function child_can_request_to_spend_more_money_than_they_have()
    {
        $this->initChildUser();
        $this->canRequestSpend('self');
    }

    /** @test */
    public function parent_can_approve_spend_request()
    {
        $this->initParentUser();
        $this->canApproveSpend();
    }

    /** @test */
    public function child_cannot_approve_spend_request()
    {
        $this->initChildUser();
        $this->cannotApproveSpend();
    }

    /** @test */
    public function child_can_transfer_own_money()
    {
        $this->initChildUser();
        $this->canTransferMoney();
    }

    /** @test */
    public function parent_can_transfer_between_children()
    {
        $this->initChildUser();
        $this->canTransferMoney(null, 'parent');
    }

    /** @test */
    public function child_can_approve_transfer_request()
    {
        $this->initChildUser();
        $this->canApproveTransfer(
            TransactionTypes::$TRANSFER_DEPOSIT,
            TransactionApprovalTypes::$TRANSFER_APPROVAL_NEEDED,
            500
        );
    }

    /** @test */
    public function parent_can_approve_overdraft_of_transfer_withdraw_request()
    {
        $this->initParentUser();
        $this->canApproveTransfer(
            TransactionTypes::$TRANSFER_WITHDRAW,
            TransactionApprovalTypes::$OVERDRAFT_APPROVAL_NEEDED,
            2000
        );
    }

    /** @test */
    public function parent_can_approve_overdraft_of_transfer_deposit_request()
    {
        $this->initParentUser();
        $this->canApproveOneApprovalOfTwoApprovalTransfer(
            TransactionApprovalTypes::$TRANSFER_APPROVAL_NEEDED
        );
    }

    /** @test */
    public function child_can_approve_overdraft_of_transfer_deposit_request()
    {
        $this->initChildUser();
        $this->canApproveOneApprovalOfTwoApprovalTransfer(
            TransactionApprovalTypes::$OVERDRAFT_APPROVAL_NEEDED
        );
    }

    /** @test */
    public function parent_cannot_approve_transfer_request()
    {
        $this->initParentUser();
        $this->cannotApproveTransfer();
    }

    /** @test */
    public function child_can_request_transfer_between_children()
    {
        $this->initChildUser();
        $this->canRequestTransfer(500);
    }

    /** @test */
    public function child_can_request_transfer_between_children_for_more_than_second_child_has_in_wallet()
    {
        $this->initChildUser();
        $this->canRequestTransfer(2000);
    }

    /** @test */
    public function no_access_user_cannot_spend_own_money()
    {
        $this->initNoAccessUser();
        $this->cannotSpendMoney('self');
    }

    /** @test */
    public function child_user_cannot_spend_money_of_another_child()
    {
        $this->initChildUser();
        $this->cannotSpendMoney();
    }

    /** @test */
    public function child_user_can_reject_transfer_request()
    {
        $this->initChildUser();
        $this->canRejectTransferRequest();
    }

    /** @test */
    public function parent_user_can_reject_transfer_request()
    {
        $this->initParentUser();
        $this->canRejectTransferRequest();
    }

    /** @test */
    public function child_user_cannot_approve_rejected_transfer_request()
    {
        $this->initChildUser();
        $this->cannotApproveRejectedTransferRequest();
    }

    private function canSpendMoney($target = null)
    {
        $targetUser = $target === 'self'
            ? $this->authUser
            : $this->user;

        $userWalletBeforeTransaction = $targetUser->wallet;

        $input = [
            "user_id" => $targetUser->id,
            'transaction_amount' => 1000,
            'transaction_type' => TransactionTypes::$WITHDRAW
        ];

        $response = $this->urlConfig('post', 'transaction/spend', $input);

        $targetUser->refresh();
        $userWalletAfterTransaction = $targetUser->wallet;

        $response->assertStatus(201);
        $response->assertJsonPath('user_id', $input['user_id'])
            ->assertJsonPath('transaction_amount', $input['transaction_amount'])
            ->assertJsonPath('transaction_type', $input['transaction_type'])
            ->assertJsonPath('transaction_approval_type', TransactionApprovalTypes::$NO_APPROVAL_NEEDED)
            ->assertJsonPath('approval_status', TransactionApprovalStatuses::$NONE)
            ->assertJsonMissing(['chore_id']);
        $this->assertEquals($userWalletBeforeTransaction - $input['transaction_amount'], $userWalletAfterTransaction);
    }

    private function canRequestSpend($target = null)
    {
        $targetUser = $target === 'self'
            ? $this->authUser
            : $this->user;

        $userWalletBeforeTransaction = $targetUser->wallet;

        $input = [
            "user_id" => $targetUser->id,
            'transaction_amount' => 2000,
            'transaction_type' => TransactionTypes::$WITHDRAW
        ];

        $response = $this->urlConfig('post', 'transaction/spend', $input);

        $targetUser->refresh();
        $userWalletAfterTransaction = $targetUser->wallet;

        $response->assertStatus(201);
        $response->assertJsonPath('user_id', $input['user_id'])
            ->assertJsonPath('transaction_amount', $input['transaction_amount'])
            ->assertJsonPath('transaction_type', $input['transaction_type'])
            ->assertJsonPath('transaction_approval_type', TransactionApprovalTypes::$OVERDRAFT_APPROVAL_NEEDED)
            ->assertJsonPath('approval_requested', true)
            ->assertJsonPath('approval_status', TransactionApprovalStatuses::$PENDING)
            ->assertJsonMissing(['chore_id']);
        $this->assertEquals($userWalletBeforeTransaction, $userWalletAfterTransaction);
        $this->assertNotNull($response->baseResponse->original['approval_request_date']);
    }

    private function canRequestTransfer($amount)
    {
        $user = $this->authUser;

        $userWalletBeforeTransaction = $user->wallet;

        $input = [
            "user_id" => $user->id,
            'transfer_passive_user_id' => $this->user->id,
            'transaction_amount' => $amount,
            'transaction_type' => TransactionTypes::$TRANSFER_DEPOSIT
        ];

        $response = $this->urlConfig('post', 'transaction/spend', $input);

        $user->refresh();
        $userWalletAfterTransaction = $user->wallet;

        $response->assertStatus(200);
        $response->assertJsonPath('transaction.user_id', $this->authUser->id)
            ->assertJsonPath('transaction.transfer_passive_user_id', $input['transfer_passive_user_id'])
            ->assertJsonPath('transaction.transaction_amount', $input['transaction_amount'])
            ->assertJsonPath('transaction.transaction_type', $input['transaction_type'])
            ->assertJsonPath('transaction.approval_requested', true)
            ->assertJsonPath('transaction.approval_status', TransactionApprovalStatuses::$PENDING)
            ->assertJsonMissing(['transaction.chore_id']);
        $this->assertEquals($userWalletBeforeTransaction, $userWalletAfterTransaction);
        $this->assertNotNull($response->baseResponse->original['transaction']['approval_request_date']);
    }

    private function canTransferMoney($target = null, $parent = null)
    {
        $transferType = $target === 'self'
            ? TransactionTypes::$TRANSFER_DEPOSIT
            : TransactionTypes::$TRANSFER_WITHDRAW;

        if (is_null($parent)) {
            $targetUser = $target === 'self'
                ? $this->authUser
                : $this->user;
        } else {
            $targetUser = $this->user2;
        }

        $passiveUser = $target === 'self'
            ? $this->user
            : $this->authUser;

        $targetUserWalletBeforeTransaction = $targetUser->wallet;
        $passiveUserWalletBeforeTransaction = $passiveUser->wallet;

        $input = [
            "user_id" => $targetUser->id,
            'transfer_passive_user_id' => $passiveUser->id,
            'transaction_amount' => 1000,
            'transaction_type' => $transferType
        ];

        $response = $this->urlConfig('post', 'transaction/spend', $input);

        $targetUser->refresh();
        $passiveUser->refresh();
        $targetUserWalletAfterTransaction = $targetUser->wallet;
        $passiveUserWalletAfterTransaction = $passiveUser->wallet;

        $response->assertStatus(201);
        $response->assertJsonPath('user_id', $input['user_id'])
            ->assertJsonPath('transaction_amount', $input['transaction_amount'])
            ->assertJsonPath('transaction_type', $input['transaction_type'])
            ->assertJsonMissing(['chore_id']);
        $this->assertEquals($targetUserWalletBeforeTransaction - $input['transaction_amount'], $targetUserWalletAfterTransaction);
        $this->assertEquals($passiveUserWalletBeforeTransaction + $input['transaction_amount'], $passiveUserWalletAfterTransaction);
    }

    public function cannotSpendMoney($target = null)
    {
        $targetUser = $target === 'self'
            ? $this->authUser
            : $this->user;
        $input = [
            "user_id" => $targetUser->id,
            'transaction_amount' => 1000,
            'transaction_type' => TransactionTypes::$WITHDRAW
        ];

        $response = $this->urlConfig('post', 'transaction/spend', $input);

        $errorMessage = $response->exception->getMessage();

        $response->assertStatus(403);
        $this->assertEquals('You do not have access to spend money', $errorMessage);
    }

    public function canApproveSpend()
    {
        $userWalletBeforeApproval = $this->user->wallet;

        $transaction = $this->createPendingTransaction(
            $this->user->id,
            TransactionTypes::$WITHDRAW,
            TransactionApprovalTypes::$NO_APPROVAL_NEEDED,
            2000
        );

        $input = [
            'id' => $transaction->id,
            'approval_status' => TransactionApprovalStatuses::$APPROVED
        ];

        $response = $this->urlConfig('put', 'transaction/approval', $input);

        $this->user->refresh();
        $userWalletAfterApproval = $this->user->wallet;

        $response->assertStatus(200)
            ->assertJsonPath('approval_status', TransactionApprovalStatuses::$APPROVED);
        $this->assertEquals($userWalletBeforeApproval - $transaction['transaction_amount'], $userWalletAfterApproval);
        $this->assertNotNull($response->baseResponse->original['approval_date']);
        $this->assertNull($response->baseResponse->original['rejected_date']);
    }

    public function canApproveTransfer($transferType, $transactionApprovalType, $amount)
    {
        $userWalletBeforeApproval = $this->authUser->wallet;
        $passiveUserWalletBeforeApproval = $this->user->wallet;

        $transaction = $this->createPendingTransaction(
            $this->authUser,
            $transferType,
            $transactionApprovalType,
            $amount,
            $this->user->id
        );

        $input = [
            'id' => $transaction->id,
            'approval_status' => TransactionApprovalStatuses::$APPROVED
        ];

        $response = $this->urlConfig('put', 'transaction/approval', $input);

        $this->authUser->refresh();
        $this->user->refresh();
        $userWalletAfterApproval = $this->authUser->wallet;
        $passiveUserWalletAfterApproval = $this->user->wallet;

        $response->assertStatus(200)
            ->assertJsonPath('approval_status', TransactionApprovalStatuses::$APPROVED)
            ->assertJsonPath('transaction_approval_type', TransactionApprovalTypes::$APPROVED);
        $this->assertEquals(
            $userWalletBeforeApproval - $transaction['transaction_amount'],
            $userWalletAfterApproval
        );
        $this->assertEquals(
            $passiveUserWalletBeforeApproval + $transaction['transaction_amount'],
            $passiveUserWalletAfterApproval
        );
        $this->assertNotNull($response->baseResponse->original['approval_date']);
        $this->assertNull($response->baseResponse->original['rejected_date']);
    }

    public function cannotApproveSpend()
    {
        $transaction = $this->createPendingTransaction(
            $this->user->id,
            TransactionTypes::$WITHDRAW,
            TransactionApprovalTypes::$NO_APPROVAL_NEEDED,
            2000
        );

        $input = [
            'id' => $transaction->id,
            'approval_status' => TransactionApprovalStatuses::$APPROVED
        ];

        $response = $this->urlConfig('put', 'transaction/approval', $input);

        $errorMessage = $response->exception->getMessage();

        $response->assertStatus(403);
        $this->assertEquals('You do not have access to approve spend', $errorMessage);
    }

    public function canApproveOneApprovalOfTwoApprovalTransfer($expectedRemainingApprovalType)
    {
        $userWalletBeforeApproval = $this->authUser->wallet;
        $passiveUserWalletBeforeApproval = $this->user->wallet;

        $transaction = $this->createPendingTransaction(
            $this->authUser,
            TransactionTypes::$TRANSFER_DEPOSIT,
            TransactionApprovalTypes::$OVERDRAFT_AND_TRANSFER_APPROVAL_NEEDED,
            2000,
            $this->user->id
        );

        $input = [
            'id' => $transaction->id,
            'approval_status' => TransactionApprovalStatuses::$APPROVED
        ];

        $response = $this->urlConfig('put', 'transaction/approval', $input);

        $this->authUser->refresh();
        $this->user->refresh();
        $userWalletAfterApproval = $this->authUser->wallet;
        $passiveUserWalletAfterApproval = $this->user->wallet;

        $response->assertStatus(200)
            ->assertJsonPath('transaction.approval_status', TransactionApprovalStatuses::$PENDING)
            ->assertJsonPath('transaction.transaction_approval_type', $expectedRemainingApprovalType);
        $this->assertEquals($userWalletBeforeApproval, $userWalletAfterApproval);
        $this->assertEquals($passiveUserWalletBeforeApproval, $passiveUserWalletAfterApproval);
        $this->assertNotNull($response->baseResponse->original['transaction']['approval_date']);
        $this->assertNull($response->baseResponse->original['transaction']['rejected_date']);
    }

    public function cannotApproveTransfer()
    {
        $transaction = $this->createPendingTransaction(
            $this->authUser,
            TransactionTypes::$TRANSFER_DEPOSIT,
            TransactionApprovalTypes::$TRANSFER_APPROVAL_NEEDED,
            500
        );

        $input = [
            'id' => $transaction->id,
            'approval_status' => TransactionApprovalStatuses::$APPROVED
        ];

        $response = $this->urlConfig('put', 'transaction/approval', $input);

        $errorMessage = $response->exception->getMessage();

        $response->assertStatus(403);
        $this->assertEquals('A parent does not have access to approve this transfer', $errorMessage);
    }

    public function canRejectTransferRequest()
    {
        $transaction = $this->createPendingTransaction(
            $this->authUser,
            TransactionTypes::$TRANSFER_DEPOSIT,
            TransactionApprovalTypes::$OVERDRAFT_AND_TRANSFER_APPROVAL_NEEDED,
            2000,
            $this->user->id
        );

        $input = [
            'id' => $transaction->id,
            'approval_status' => TransactionApprovalStatuses::$REJECTED,
            'rejection_reason' => 'Because I do not want to send my money to you'
        ];

        $response = $this->urlConfig('put', 'transaction/reject', $input);

        $response->assertStatus(200)
            ->assertJsonPath('approval_status', TransactionApprovalStatuses::$REJECTED)
            ->assertJsonPath('rejection_reason', $input['rejection_reason']);
        $this->assertNull($response->baseResponse->original['rejected_date']);
    }

    public function cannotApproveRejectedTransferRequest()
    {
        $transaction = $this->createRejectedTransaction(
            $this->authUser,
            TransactionTypes::$TRANSFER_DEPOSIT,
            TransactionApprovalTypes::$OVERDRAFT_AND_TRANSFER_APPROVAL_NEEDED,
            2000,
            $this->user->id
        );

        $input = [
            'id' => $transaction->id,
            'approval_status' => TransactionApprovalStatuses::$APPROVED,
        ];

        $response = $this->urlConfig('put', 'transaction/approval', $input);

        $response->assertStatus(200)
            ->assertJsonPath('transaction.approval_status', TransactionApprovalStatuses::$REJECTED)
            ->assertJsonPath('transaction.rejected_date', $transaction['rejected_date'])
            ->assertJsonPath('transaction.rejection_reason', $transaction['rejection_reason']);
    }
}
