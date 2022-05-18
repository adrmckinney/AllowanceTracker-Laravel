<?php

namespace App\Types\Transactions;

use App\Types\BaseType;

class TransactionType extends BaseType
{
    protected $table = 'transactions';
    protected $fillable = [
        'id',
        'user_id',
        'chore_id',
        'transaction_amount',
        'transaction_type'
    ];

    public function toCreateArray(): array
    {
        return $this->toArray([
            'id',
            'user_id',
            'chore_id',
            'transaction_amount',
            'transaction_type'
        ]);
    }
}
