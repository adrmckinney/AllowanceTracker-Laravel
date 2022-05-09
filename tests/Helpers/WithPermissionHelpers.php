<?php

namespace Tests\Helpers;

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
}
