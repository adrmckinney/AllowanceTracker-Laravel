<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Types\Transactions\TransactionType;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function createTransaction(TransactionType $transaction)
    {
        return Transaction::create(
            $transaction->toCreateArray()
            //     [
            //     'user_id' => $transaction->user_id,
            //     'chore_id' => $transaction->chore_id,
            //     'transaction_amount' => $transaction->transaction_amount,
            //     'transaction_type' => $transaction->transaction_type
            // ]
        );
    }
}
