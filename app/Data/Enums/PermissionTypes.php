<?php

namespace App\Data\Enums;

class PermissionTypes
{
    public static $NONE = 0;
    public static $PARENT = 1;
    public static $CHILD = 2;

    public static $STATUSES = [
        'none' => [
            'value' => 0,
            'name' => 'No Status'
        ],
        'check_one' => [
            'value' => 1,
            'name' => 'Parent'
        ],
        'check_two' => [
            'value' => 2,
            'name' => 'Child'
        ],
    ];
}
