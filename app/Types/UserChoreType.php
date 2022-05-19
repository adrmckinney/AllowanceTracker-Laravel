<?php

namespace App\Types;

use App\Types\BaseType;

class UserChoreType extends BaseType
{
    protected $fillable = [
        'id',
        'user_id',
        'chore_id',
        'approval_requested',
        'approval_request_date',
        'approval_status',
        'approval_date'
    ];

    public function toCreateArray(): array
    {
        return $this->toArray([
            'id',
            'user_id',
            'chore_id',
            'approval_requested',
            'approval_request_date',
            'approval_status',
            'approval_date'
        ]);
    }
}
