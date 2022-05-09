<?php

namespace App\Data\Enums;

class PermissionTypes
{
    public static $NONE = 0;
    public static $ADMIN = 1;
    public static $PARENT = 2;
    public static $CHILD = 3;

    public static $PERMISSIONS = [
        'none' => [
            'value' => 0,
            'display_name' => 'No Status',
            'name' => 'no_status'
        ],
        'admin' => [
            'value' => 1,
            'display_name' => 'Admin',
            'name' => 'admin'
        ],
        'parent' => [
            'value' => 2,
            'display_name' => 'Parent',
            'name' => 'parent'
        ],
        'child' => [
            'value' => 3,
            'display_name' => 'Child',
            'name' => 'child'
        ],
    ];

    public static function getPermissionName($value)
    {
        return collect(PermissionTypes::$PERMISSIONS)
            ->filter(function ($permission) use ($value) {
                return $permission['value'] === $value;
            })->first()['name'];
    }
}
