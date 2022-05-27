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
        'transfer_passive_user_id',
        'transaction_amount',
        'transaction_type',
        'approval_requested',
        'approval_request_date',
        'approval_status',
        'approval_date',
        'rejected_date'
    ];

    public function toCreateArray(): array
    {
        return $this->toArray([
            'id',
            'user_id',
            'chore_id',
            'transfer_passive_user_id',
            'transaction_amount',
            'transaction_type',
            'approval_requested',
            'approval_request_date',
            'approval_status',
            'approval_date',
            'rejected_date'
        ]);
    }
}
