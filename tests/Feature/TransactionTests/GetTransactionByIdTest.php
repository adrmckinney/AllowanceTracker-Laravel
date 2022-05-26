<?php

namespace Tests\Feature\UserTests;

use App\Data\Enums\TransactionTypes;
use App\Models\Chore;
use App\Models\Transaction;
use App\Models\User;
use Tests\APITestCase;


class GetTransactionByIdTest extends APITestCase
{
    protected $transaction, $user, $chore;

    public function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->chore = Chore::factory()->create(['cost' => 2000]);
        $this->transaction = Transaction::factory()->create([
            'user_id' => $this->user->id,
            'chore_id' => $this->chore->id,
            'transaction_amount' => $this->chore->cost,
            'transaction_type' => TransactionTypes::$DEPOSIT
        ]);
    }

    /** @test */
    public function admin_can_get_transaction_by_id()
    {
        $this->initAdminUser();
        $this->canGetTransaction();
    }

    /** @test */
    public function parent_can_get_transaction_by_id()
    {
        $this->initParentUser();
        $this->canGetTransaction();
    }

    /** @test */
    public function child_can_get_transaction_by_id()
    {
        $this->initChildUser();
        $this->canGetTransaction();
    }

    /** @test */
    public function no_access_user_cannot_get_transaction_by_id()
    {
        $this->initChildUser();
        $this->canGetTransaction();
    }

    /** @test */
    public function no_access_can_get_transaction_by_id()
    {
        $this->initNoAccessUser();
        $this->cannotGetTransaction();
    }

    /** @test */
    public function no_access_without_token()
    {
        $this->initNoTokenAccessUser();
        $this->noAccessWithoutToken();
    }

    private function canGetTransaction()
    {
        $response = $this->urlConfig('get', "transaction/{$this->transaction->id}");

        $response->assertStatus(200);

        $response->assertJsonPath('user_id', $this->user->id);
        $response->assertJsonPath('chore_id', $this->chore->id);
        $response->assertJsonPath('transaction_amount', $this->chore->cost);
        $response->assertJsonPath('transaction_type', $this->transaction->transaction_type);
    }

    public function cannotGetTransaction()
    {
        $response = $this->urlConfig('get', "transaction/{$this->transaction->id}");

        $errorMessage = $response->exception->getMessage();

        $response->assertStatus(403);
        $this->assertEquals('You do not have access to see this transaction', $errorMessage);
    }

    public function noAccessWithoutToken()
    {
        $response = $this->urlConfig('get', "transaction/{$this->transaction->id}");

        $errorMessage = $response->exception->getMessage();

        $response->assertStatus(401);
        $this->assertEquals('Unauthenticated.', $errorMessage);
    }
}
