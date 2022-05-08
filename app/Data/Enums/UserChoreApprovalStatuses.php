<?php

namespace App\Data\Enums;

class UserChoreApprovalStatuses
{
    public static $NONE = 0;
    public static $PENDING = 1;
    public static $APPROVED = 2;
    public static $REJECTED = 3;

    public static $STATUSES = [
        'none' => [
            'value' => 0,
            'display' => 'No Status'
        ],
        'pending' => [
            'value' => 1,
            'display' => 'Pending Approval',
            'name' => 'pending'
        ],
        'approved' => [
            'value' => 2,
            'display' => 'Work Approved',
            'name' => 'approve'
        ],
        'rejected' => [
            'value' => 3,
            'display' => 'Work Not Approved',
            'name' => 'reject'
        ],
    ];

    public static function getStatusName($value)
    {
        $status = collect(UserChoreApprovalStatuses::$STATUSES)->filter(function ($status) use ($value) {
            return $status['value'] === $value;
        });

        return $status->first()['name'];
    }
}
