<?php

namespace App\Data\Enums;

class TransactionApprovalTypes
{
    public static $NO_APPROVAL_NEEDED = 1;
    public static $OVERDRAFT_APPROVAL_NEEDED = 2;
    public static $TRANSFER_APPROVAL_NEEDED = 3;
    public static $OVERDRAFT_AND_TRANSFER_APPROVAL_NEEDED = 4;
    public static $APPROVED = 5;

    public static $TRANSACTION_APPROVALS_TYPES = [
        'no_approval_needed' => [
            'value' => 1,
            'display_name' => 'No Approval Needed',
            'name' => 'no_approval_needed'
        ],
        'overdraft_approval_needed' => [
            'value' => 2,
            'display_name' => 'Overdraft Approval Needed',
            'name' => 'overdraft_approval_needed'
        ],
        'transfer_approval_needed' => [
            'value' => 3,
            'display_name' => 'Transfer Approval Needed',
            'name' => 'transfer_approval_needed'
        ],
        'overdraft_and_transfer_needed' => [
            'value' => 4,
            'display_name' => 'Overdraft and Transfer Needed',
            'name' => 'overdraft_and_transfer_needed'
        ],
        'approved' => [
            'value' => 4,
            'display_name' => 'Approved',
            'name' => 'approved'
        ],
    ];

    public static function getTransactionName($value)
    {
        return collect(TransactionApprovalTypes::$TRANSACTION_APPROVALS_TYPES)
            ->filter(function ($types) use ($value) {
                return $types['value'] === $value;
            })->first()['name'];
    }
}
