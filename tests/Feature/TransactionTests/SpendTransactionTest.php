<?php

namespace Tests\Feature\UserTests;

use App\Data\Enums\TransactionTypes;
use App\Models\Chore;
use App\Models\Transaction;
use App\Models\User;
use Tests\APITestCase;


class SpendTransactionTest extends APITestCase
{
    protected $user;

    public function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
    }

    // /** @test */
    // public function admin_can_spend()
    // {
    //     $this->initAdminUser();
    //     $this->canSpendMoney();
    // }

    // /** @test */
    // public function parent_can_spend()
    // {
    //     $this->initParentUser();
    //     $this->canSpendMoney();
    // }

    /** @test */
    public function child_can_spend_own_money()
    {
        $this->initChildUser();
        $this->canSpendMoneyOwnMoney();
    }

    /** @test */
    public function no_access_user_cannot_spend_own_money()
    {
        $this->initNoAccessUser();
        $this->cannotSpendOwnMoney();
    }

    // /** @test */
    // public function child_user_cannot_spend_money_of_another_child()
    // {
    //     $this->initChildUser();
    //     $this->cannotSpendMoney();
    // }

    // child cannot spend more than they have
    // or child needs approval to spend more than they have
    // parent can spend child money


    private function canSpendMoneyOwnMoney()
    {
        // need to add wallet update to this test
        // and no access isn't working
        $input = [
            "user_id" => $this->authUser->id,
            'transaction_amount' => 1000,
            'transaction_type' => TransactionTypes::$WITHDRAW
        ];

        $response = $this->urlConfig('post', 'transaction/spend', $input);

        $response->assertStatus(201);
        $response->assertJsonPath('user_id', $input['user_id'])
            ->assertJsonPath('transaction_amount', $input['transaction_amount'])
            ->assertJsonPath('transaction_type', $input['transaction_type'])
            ->assertJsonMissing(['chore_id']);
    }

    public function cannotSpendOwnMoney()
    {
        $input = [
            "user_id" => $this->authUser->id,
            'transaction_amount' => 1000,
            'transaction_type' => TransactionTypes::$WITHDRAW
        ];

        $response = $this->urlConfig('post', 'transaction/spend', $input);

        $errorMessage = $response->exception->getMessage();

        $response->assertStatus(403);
        $this->assertEquals('You do not have access to spend money', $errorMessage);
    }
}
