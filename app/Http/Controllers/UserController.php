<?php

namespace App\Http\Controllers;

use App\Data\Enums\PermissionTypes;
use App\Models\User;
use App\Models\UsersPermissions;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    protected $userPermissionController;

    public function __construct(UserPermissionController $userPermissionController)
    {
        $this->userPermissionController = $userPermissionController;
    }

    public function getUser(Request $request, $id)
    {
        $user = $this->getUserById($id);

        if ($request->user()->cannot('viewOne', $user)) {
            abort(403, 'You do not have access to get this user info');
        };

        $userPermission = $this->userPermissionController->getUserPermissionByUserId($user->id);
        $user['user_permission'] = $userPermission->permission_id;

        return $user;
    }

    public function usernameExists($username)
    {
        return User::where('username', '=', $username)->first();
    }

    public function userEmailExists($email)
    {
        return User::where('email', '=', $email)->first();
    }

    public function getUsers(Request $request)
    {
        $user = $request->user();
        $permissionId = $user->permissions->toArray()[0]['permission_id'];

        switch ($permissionId) {
            case PermissionTypes::$ADMIN:
                $users = User::where('id', '!=', $user->id)->get();
                $this->addPermissionToUserData($users);

                return $users;

            case PermissionTypes::$PARENT:
                $users = User::where('id', '!=', $user->id)->get();
                $this->addPermissionToUserData($users);

                $filteredUsers = $users->filter(function ($user) {
                    return $user->user_permission !== PermissionTypes::$ADMIN;
                });

                return collect(array_values(array_filter($filteredUsers->toArray())));

            case PermissionTypes::$CHILD:
            case PermissionTypes::$NO_ACCESS:
                if ($request->user()->cannot('viewAll', $user)) {
                    abort(403, 'You do not have access to get users');
                };
        }
    }

    public function update(Request $request)
    {
        $fields = ['name', 'email', 'username', 'wallet', 'password', 'permissions'];

        $user = $request->user();

        if ($request->user()->cannot('update', $user)) {
            abort(403, 'You do not have access');
        };


        if ($request->user()->cannot('update', $user)) {
            abort(403, 'You do not have access');
        };

        foreach ($fields as $field) {
            if (array_keys($request->toArray())[0] === 'wallet') {
                if ($request->user()->cannot('updateWallet', $user)) {
                    abort(403, 'You do not have access');
                };
            }

            if ($request->$field) {
                $user->$field = $request->$field;
                $user->save();
                return $user;
            }
        }
    }

    public static function getUserById($id)
    {
        return User::find($id);
    }

    public static function getUserByName($name)
    {
        return User::where('name', '=', $name);
    }

    public static function getUserByUsername($username)
    {
        return User::where('username', '=', $username);
    }

    public function addPermissionToUserData($users)
    {
        $users->map(function ($user) {
            $userPermission = $this->userPermissionController->getUserPermissionByUserId($user->id);
            $user['user_permission'] = $userPermission->permission_id;

            return $user;
        });

        return $users;
    }
}
