<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use Illuminate\Http\Request;

class PermissionsController extends Controller
{

    public function getPermission(Request $request, $id)
    {
        return $this->getPermissionById($id);
    }


    public function getPermissions(Request $request)
    {
        $permissions = Permission::all();

        if ($request->user()->cannot('getMany', $permissions->first())) {
            abort(403, 'You do not have access to get permissions');
        }

        return $permissions;
    }

    public function createPermission(Request $request)
    {
        $permission = Permission::create([
            'name' => $request->name,
            'display_name' => $request->display_name,
        ]);

        if ($request->user()->cannot('create', $permission)) {
            abort(403, 'You do not have access to create a permission');
        }

        return $permission;
    }

    public function updatePermission(Request $request)
    {
        $fields = [
            'name',
            'display_name',
        ];

        if ($this->permissionExists($request->name)) {
            abort(406, 'A permission with this name already exists.');
        }

        $permission = $this->getPermissionById($request->id);

        if ($request->user()->cannot('update', $permission)) {
            abort(403, 'You do not have access to update this permission');
        };

        foreach ($fields as $field) {
            if ($request->$field) {
                $permission->$field = $request->$field;

                $permission->save();
            }
        }

        return $permission;
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
