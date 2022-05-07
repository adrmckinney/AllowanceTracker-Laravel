<?php

namespace App\Data\Traits;

use App\Data\Enums\PermissionTypes;

trait UserAuthorizableTrait
{
    protected function isParent($permissionId)
    {
        return $permissionId === PermissionTypes::$PARENT;
    }

    protected function isChildOrHigher($permissionId)
    {
        return ($permissionId === PermissionTypes::$CHILD
            || $permissionId === PermissionTypes::$PARENT
        );
    }

    // public function isOrgPurchaserOrHigher()
    // {
    //     return $this->hasPermission(['organization-admin', 'organization-purchaser']);
    // }
}
