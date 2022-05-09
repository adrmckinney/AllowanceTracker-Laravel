<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use App\Models\User;
use App\Models\UsersPermissions;
use Exception;
use Illuminate\Http\Request;

class UserPermissionController extends Controller
{

    public function getUserPermission(Request $request, $id)
    {
        return $this->getUserPermissionById($id);
    }


    public function getUserPermissions(Request $request)
    {
        $userPermissions = UsersPermissions::all();

        if ($request->user()->cannot('getMany', $userPermissions->first())) {
            abort(403, 'You do not have access to get this user\'s permission level');
        }

        return $userPermissions;
    }

    public function addPermission(Request $request)
    {
        $user_id = $request->user_id;
        $permissionName = $request->name;

        $permission =  Permission::where('name', '=', $permissionName)->first();

        if ($permission) {
            $userPermission = UsersPermissions::where('user_id', '=', $user_id)
                ->where('permission_id', '=', $permission->id)
                ->first();

            if (!$userPermission) {
                $newUserPermission = UsersPermissions::create([
                    'user_id' => $user_id,
                    'permission_id' => $permission->id,
                ]);
                if ($request->user()->cannot('add', $newUserPermission)) {
                    abort(403, 'You do not have access to assign permissions');
                };
                return $newUserPermission;
            } else {
                abort(406, "Permission: User already has this permission - {$permissionName}");
            }
        } else {
            throw new Exception("Permission: $permissionName not found.");
        }
    }

    public function getUserPermissionById($id)
    {
        return UsersPermissions::where('id', '=', $id)->first();
    }

    public function getUserPermissionByUserId($user_id)
    {
        return UsersPermissions::where('user_id', '=', $user_id)->first();
    }

    public function userPermissionExists($name)
    {
        return UsersPermissions::where('name', '=', $name)->first();
    }
}
