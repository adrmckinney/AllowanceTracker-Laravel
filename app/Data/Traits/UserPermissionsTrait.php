<?php

namespace App\Data\Traits;

use App\Models\Permission;
use App\Models\UsersPermissions;
use Exception;

trait UserPermissionsTrait
{
    public function addPermission($permissionName)
    {

        $permission = Permission::where('name', '=', $permissionName)->first();
        if ($permission) {
            $userPermission = UsersPermissions::where('user_id', '=', $this->id)
                ->where('permission_id', '=', $permission->id)
                ->first();
            if (!$userPermission) {
                UsersPermissions::create([
                    'user_id' => $this->id,
                    'permission_id' => $permission->id,
                ]);
            } else {
                throw new Exception(
                    "Permission: User already has this permission - $permissionName"
                );
            }
        } else {
            throw new Exception("Permission: $permissionName not found.");
        }
    }

    public function removePermission($permissionName)
    {
        $permission = Permission::where('name', '=', $permissionName)->first();
        if ($permission) {
            $userPermission = UsersPermissions::where('user_id', '=', $this->id)
                ->where('permission_id', '=', $permission->id)
                ->first();
            if ($userPermission) {
                $userPermission->delete();
            } else {
                throw new Exception(
                    "Permission: User does not have this permission - $permissionName."
                );
            }
        } else {
            throw new Exception("Permission: $permissionName not found.");
        }
    }

    public function hasPermission($target): bool
    {
        if (gettype($target) === 'array') {
            foreach ($target as $targetPermission) {
                foreach ($this->permissions as $permission) {
                    if ($permission->permission->name === $targetPermission) {
                        return true;
                    }
                }
            }
        } else {
            foreach ($this->permissions as $permission) {
                if ($permission->permission->name === $target) {
                    return true;
                }
            }
        }
        return false;
    }

    public function resetPermissions()
    {
        dump('reset perms ran');
        $permissions = UsersPermissions::where('user_id', '=', $this->id)->get();
        foreach ($permissions as $permission) {
            $permission->delete();
        }
    }

    public function isAdmin(): bool
    {
        return $this->hasPermission(['admin']);
    }

    public function isParent(): bool
    {
        return $this->hasPermission('parent');
    }

    public function isChild(): bool
    {
        return $this->hasPermission('child');
    }

    public function isNoAccessUser(): bool
    {
        $userPermissions = UsersPermissions::where('user_id', '=', $this->id)->get();

        return $this->permissions->count() === 0
            && $userPermissions->count() === 0;
        return false;
    }


    // public function isOrganizationPurchaser(): bool
    // {
    //     return $this->hasPermission('organization-purchaser');
    // }
}
