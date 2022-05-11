<?php

namespace App\Data\Enums;

class PermissionTypes
{
    public static $NO_ACCESS = 1;
    public static $ADMIN = 2;
    public static $PARENT = 3;
    public static $CHILD = 4;

    public static $PERMISSIONS = [
        'no_access' => [
            'value' => 1,
            'display_name' => 'No Access',
            'name' => 'no_access'
        ],
        'admin' => [
            'value' => 2,
            'display_name' => 'Admin',
            'name' => 'admin'
        ],
        'parent' => [
            'value' => 3,
            'display_name' => 'Parent',
            'name' => 'parent'
        ],
        'child' => [
            'value' => 4,
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
