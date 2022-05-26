<?php

namespace Tests\Helpers;

use App\Data\Enums\PermissionTypes;
use App\Models\Permission;

/**
 * Trait WithPermissionHelpers
 * @package Tests\Helpers
 */
trait WithPermissionHelpers
{
    public function getAllPermissions()
    {
        return Permission::all();
    }

    public function getPermission($type)
    {
        switch ($type) {
            case PermissionTypes::$PARENT:
                return $this->permission =
                    Permission::where('name', '=', PermissionTypes::getPermissionName(PermissionTypes::$PARENT))
                    ->first();
            case PermissionTypes::$CHILD:
                return $this->permission =
                    Permission::where('name', '=', PermissionTypes::getPermissionName(PermissionTypes::$CHILD))
                    ->first();
            case PermissionTypes::$ADMIN:
                return $this->permission =
                    Permission::where('name', '=', PermissionTypes::getPermissionName(PermissionTypes::$ADMIN))
                    ->first();
            case PermissionTypes::$NO_ACCESS:
                return $this->permission =
                    Permission::where('name', '=', PermissionTypes::getPermissionName(PermissionTypes::$NO_ACCESS))
                    ->first();
        }
    }
}
