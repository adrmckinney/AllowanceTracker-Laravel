<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use App\Models\User;
use App\Models\UsersPermissions;
use Exception;
use Illuminate\Http\Request;

class PermissionsController extends Controller
{

    public function getPermission(Request $request, $id)
    {
        return $this->getPermissionById($id);
    }


    public function getPermissions()
    {
        return Permission::all();
    }

    public function createPermission(Request $request)
    {
        $permission = Permission::create([
            'name' => $request->name,
            'display_name' => $request->display_name,
        ]);

        return $permission;
    }

    public function addPermission(Request $request)
    {
        // dump('user in perms cont', $request);
        // $user = $request->user() ? $request->user : UserController::getUserById($request->user_id);
        // dump('user', $user->permissions);
        // $permissionId = $user->permissions->toArray()[0]['permission_id'];
        // dump('permId', $permissionId);
        // if ($request->user()->cannot('add', Permission::class)) {
        //     abort(403);
        // }

        // $this->authorize('add', Permission::class);

        $user_id = $request->user_id;
        $permissionName = $request->name;

        $permission = $this->getPermissionByName($permissionName);

        if ($permission) {
            $userPermission = UsersPermissions::where('user_id', '=', $user_id)
                ->where('permission_id', '=', $permission->id)
                ->first();
            if (!$userPermission) {
                UsersPermissions::create([
                    'user_id' => $user_id,
                    'permission_id' => $permission->id,
                ]);
            } else {
                throw new Exception(
                    "Permission: User already has this permission - {$permissionName}"
                );
            }
        } else {
            throw new Exception("Permission: $permissionName not found.");
        }
    }

    public function getPermissionById($id)
    {
        return Permission::where('id', '=', $id)->first();
    }

    public function getPermissionByName($name)
    {
        return Permission::where('name', '=', $name)->first();
    }

    public function permissionExists($name)
    {
        return Permission::where('name', '=', $name)->first();
    }
}
