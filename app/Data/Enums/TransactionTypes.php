<?php

namespace App\Data\Enums;

class TransactionTypes
{
    public static $DEPOSIT = 1;
    public static $WITHDRAW = 2;
    public static $TRANSFER_DEPOSIT = 3;
    public static $TRANSFER_WITHDRAW = 4;

    public static $TRANSACTIONS = [
        'deposit' => [
            'value' => 1,
            'display_name' => 'Deposit',
            'name' => 'deposit'
        ],
        'withdraw' => [
            'value' => 2,
            'display_name' => 'Withdraw',
            'name' => 'withdraw'
        ],
        'transfer_deposit' => [
            'value' => 3,
            'display_name' => 'Transfer Deposit',
            'name' => 'transfer_deposit'
        ],
        'transfer_withdraw' => [
            'value' => 4,
            'display_name' => 'Transfer Withdraw',
            'name' => 'transfer_withdraw'
        ],
    ];

    public static function getTransactionName($value)
    {
        return collect(TransactionTypes::$TRANSACTIONS)
            ->filter(function ($transaction) use ($value) {
                return $transaction['value'] === $value;
            })->first()['name'];
    }
}
