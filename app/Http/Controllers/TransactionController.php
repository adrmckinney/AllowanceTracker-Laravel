<?php

namespace App\Http\Controllers;

use App\Data\Enums\TransactionTypes;
use App\Data\Traits\UserTrait;
use App\Models\Transaction;
use App\Types\Transactions\TransactionType;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    use UserTrait;

    protected $userController;

    public function __construct(UserController $userController)
    {
        $this->userController = $userController;
    }

    public function createTransaction(TransactionType $transaction)
    {
        $createTransaction = Transaction::create(
            $transaction->toCreateArray()
        );

        $user = $this->findUser($transaction->user_id);

        switch ($transaction->transaction_type) {
            case TransactionTypes::$DEPOSIT:
                $this->addMoneyToWallet($user, $transaction->transaction_amount);
                break;
            case TransactionTypes::$WITHDRAW:
                $this->removeMoneyFromWallet($user, $transaction->transaction_amount);
                break;
            case TransactionTypes::$TRANSFER_DEPOSIT:
            case TransactionTypes::$TRANSFER_WITHDRAW:
                $this->addMoneyToWallet($user, $transaction->transaction_amount);
                $this->removeMoneyFromWallet($user, $transaction->transaction_amount);
                break;
        }

        return $createTransaction;
    }

    public function getTransaction(Request $request, $id)
    {
        if ($request->user()->cannot('viewOne', Transaction::class)) {
            abort(403, "You do not have access to see this transaction");
        }
        return $this->getTransactionById($id);
    }

    public function getTransactionsList(Request $request)
    {
        if ($request->user()->cannot('viewMany', Transaction::class)) {
            abort(403, "You do not have access to see transactions");
        }
        return $this->getAllTransactions();
    }

    public function spendTransaction(Request $request)
    {
        if ($request->user()->cannot('spend', Transaction::class)) {
            abort(403, "You do not have access to spend money");
        }

        $newTransaction = new TransactionType($request->input());

        $createTransaction = Transaction::create($newTransaction->toCreateArray());

        return $createTransaction;
    }

    public function getTransactionById($id)
    {
        return Transaction::find($id);
    }

    public function getAllTransactions()
    {
        return Transaction::all();
    }

    public function findUser($userId)
    {
        return $this->userController->getUserById($userId);
    }
}
