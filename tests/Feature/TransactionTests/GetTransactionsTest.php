<?php

namespace Tests\Feature\UserTests;

use App\Data\Enums\TransactionTypes;
use App\Models\Chore;
use App\Models\Transaction;
use App\Models\User;
use Tests\APITestCase;


class GetTransactionsTest extends APITestCase
{
    protected $transaction1, $transaction2, $user1, $user2, $chore;

    public function setUp(): void
    {
        parent::setUp();

        $this->count = 4;
        $this->user1 = User::factory()->create();
        $this->user2 = User::factory()->create();
        $this->chore = Chore::factory()->create(['cost' => 2000]);
        $this->transaction1 = Transaction::factory()->count($this->count / 2)->create([
            'user_id' => $this->user1->id,
            'chore_id' => $this->chore->id,
            'transaction_amount' => $this->chore->cost,
            'transaction_type' => TransactionTypes::$DEPOSIT
        ]);
        $this->transaction2 = Transaction::factory()->count($this->count / 2)->create([
            'user_id' => $this->user2->id,
            'chore_id' => $this->chore->id,
            'transaction_amount' => $this->chore->cost,
            'transaction_type' => TransactionTypes::$WITHDRAW
        ]);
    }

    /** @test */
    public function admin_can_get_all_transactions()
    {
        $this->initAdminUser();
        $this->canGetTransactionsList();
    }

    /** @test */
    public function parent_can_get_all_transactions()
    {
        $this->initParentUser();
        $this->canGetTransactionsList();
    }

    /** @test */
    public function child_can_get_all_transactions()
    {
        $this->initChildUser();
        $this->canGetTransactionsList();
    }

    /** @test */
    public function no_access_user_can_get_all_transactions()
    {
        $this->initNoAccessUser();
        $this->cannotGetTransactionsList();
    }

    /** @test */
    public function no_access_without_token()
    {
        $this->initNoTokenAccessUser();
        $this->noAccessWithoutToken();
    }

    private function canGetTransactionsList()
    {
        $response = $this->urlConfig('get', 'transactions');
        $response->assertStatus(200);

        $response->assertJsonCount($this->count);
        $response->assertJsonPath('0.user_id', $this->user1->id);
        $response->assertJsonPath('2.user_id', $this->user2->id);
        $response->assertJsonPath('1.chore_id', $this->chore->id);
        $response->assertJsonPath('3.transaction_amount', $this->chore->cost);
        $response->assertJsonPath('1.transaction_type', TransactionTypes::$DEPOSIT);
        $response->assertJsonPath('3.transaction_type', TransactionTypes::$WITHDRAW);
    }

    public function cannotGetTransactionsList()
    {
        $response = $this->urlConfig('get', 'transactions');

        $errorMessage = $response->exception->getMessage();

        $response->assertStatus(403);
        $this->assertEquals('You do not have access to see transactions', $errorMessage);
    }

    public function noAccessWithoutToken()
    {
        $response = $this->urlConfig('get', 'transactions');

        $errorMessage = $response->exception->getMessage();

        $response->assertStatus(401);
        $this->assertEquals('Unauthenticated.', $errorMessage);
    }
}
