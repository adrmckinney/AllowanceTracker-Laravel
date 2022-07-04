<?php

namespace App\Types\Transactions;

use App\Types\BaseType;

class TransactionType extends BaseType
{
    protected $table = 'transactions';
    protected $fillable = [
        'user_id',
        'chore_id',
        'transfer_passive_user_id',
        'transaction_amount',
        'transaction_type',
        'transaction_approval_type',
        'approval_requested',
        'approval_request_date',
        'approval_status',
        'approval_date',
        'rejection_reason',
        'rejected_date'
    ];

    public function toCreateArray(): array
    {
        return $this->toArray(['excludes' => [
            'id',
            'rejection_reason',
            'rejected_date'
        ]]);
    }
    public function toUpdateArray(): array
    {
        return $this->toArray(['excludes' => [
            'id',
            'user_id',
            'transfer_passive_user_id'
        ]]);
    }
}
