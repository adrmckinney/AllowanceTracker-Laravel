<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function getUser($id)
    {
        return $this->getUserById($id);
    }

    public function usernameExists($username)
    {
        return User::where('username', '=', $username)->first();
    }

    public function userEmailExists($email)
    {
        return User::where('email', '=', $email)->first();
    }

    public function getUsers()
    {
        return User::all();
    }

    public function update(Request $request)
    {
        $fields = ['name', 'email', 'username', 'wallet', 'password', 'permissions'];

        /** @var \App\Models\User $user */
        $user = Auth::user();

        foreach ($fields as $field) {
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
}
