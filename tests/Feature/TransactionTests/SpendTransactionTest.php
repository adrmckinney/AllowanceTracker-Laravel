<?php

namespace Tests\Feature\UserTests;

use App\Data\Enums\TransactionApprovalStatuses;
use App\Data\Enums\TransactionTypes;
use App\Models\Chore;
use App\Models\Transaction;
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

    // /** @test */
    // public function admin_can_spend_money_of_child()
    // {
    //     $this->initAdminUser();
    //     $this->canSpendMoney();
    // }

    // /** @test */
    // public function parent_can_spend_money_of_child()
    // {
    //     $this->initParentUser();
    //     $this->canSpendMoney();
    // }

    // /** @test */
    // public function child_can_spend_own_money()
    // {
    //     $this->initChildUser();
    //     $this->canSpendMoney('self');
    // }

    // /** @test */
    // public function child_can_request_to_spend_more_money_than_they_have()
    // {
    //     $this->initChildUser();
    //     $this->canRequestSpend('self');
    // }

    /** @test */
    public function parent_can_approve_spend_request()
    {
        $this->initParentUser();
        $this->canApproveSpend();
    }

    // /** @test */
    // public function child_can_transfer_own_money()
    // {
    //     $this->initChildUser();
    //     $this->canTransferMoney();
    // }

    // /** @test */
    // public function parent_can_transfer_between_children()
    // {
    //     $this->initChildUser();
    //     $this->canTransferMoney(null, 'parent');
    // }

    // /** @test */
    // public function no_access_user_cannot_spend_own_money()
    // {
    //     $this->initNoAccessUser();
    //     $this->cannotSpendMoney('self');
    // }

    // /** @test */
    // public function child_user_cannot_spend_money_of_another_child()
    // {
    //     $this->initChildUser();
    //     $this->cannotSpendMoney();
    // }

    // child can request to spend more than they have  √
    // parent can approve request
    // child can request transfer from another child


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
        dump('wallet after', $userWalletAfterTransaction);

        $response->assertStatus(201);
        $response->assertJsonPath('user_id', $input['user_id'])
            ->assertJsonPath('transaction_amount', $input['transaction_amount'])
            ->assertJsonPath('transaction_type', $input['transaction_type'])
            ->assertJsonPath('approval_requested', true)
            ->assertJsonPath('approval_status', TransactionApprovalStatuses::$PENDING)
            ->assertJsonMissing(['chore_id']);
        $this->assertEquals($userWalletBeforeTransaction, $userWalletAfterTransaction);
        $this->assertNotNull($response->baseResponse->original['approval_request_date']);
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

        $transferToUser = $target === 'self'
            ? $this->user
            : $this->authUser;

        dump('targetUser', $targetUser->id);
        dump('$transferToUser', $transferToUser->id);

        $targetUserWalletBeforeTransaction = $targetUser->wallet;
        $passiveUserWalletBeforeTransaction = $transferToUser->wallet;

        $input = [
            "user_id" => $targetUser->id,
            'transfer_passive_user_id' => $transferToUser->id,
            'transaction_amount' => 1000,
            'transaction_type' => $transferType
        ];

        $response = $this->urlConfig('post', 'transaction/spend', $input);

        $targetUser->refresh();
        $transferToUser->refresh();
        $targetUserWalletAfterTransaction = $targetUser->wallet;
        $passiveUserWalletAfterTransaction = $transferToUser->wallet;

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
        // create child user 
        // create a transaction approval request with that child
        $transaction = Transaction::factory()->create([
            'approval_requested' => true,
            'approval_request_date' => date('Y-m-d H:i:s', time()),
            'approval_status' => TransactionApprovalStatuses::$PENDING
        ]);

        $input = [
            'id' => $transaction->id,
            'approval_status' => TransactionApprovalStatuses::$APPROVED
        ];

        $response = $this->urlConfig('put', 'transaction/approval', $input);
        // there should not be a chore with this response. check trans factory
        $this->echoResponse($response);
    }
}
