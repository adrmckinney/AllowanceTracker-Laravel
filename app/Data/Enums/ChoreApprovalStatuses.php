<?php

namespace App\Data\Enums;

class ChoreApprovalStatuses
{
    public static $NONE = 0;
    public static $PENDING = 1;
    public static $ACCEPTED = 2;
    public static $REJECTED = 3;

    public static $STATUSES = [
        'none' => [
            'value' => 0,
            'name' => 'No Status'
        ],
        'check_one' => [
            'value' => 1,
            'name' => 'Pending Approval'
        ],
        'check_two' => [
            'value' => 2,
            'name' => 'Work Approved'
        ],
        'check_three' => [
            'value' => 3,
            'name' => 'Work Not Satisfactory'
        ],
    ];
}
