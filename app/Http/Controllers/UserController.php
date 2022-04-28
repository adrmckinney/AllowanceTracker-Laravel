<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function getUser(Request $request, $id)
    {
        return $this->getUserById($id);
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

    public function getUserById($id)
    {
        return User::find($id);
    }

    public static function getUserByName($name)
    {
        return User::where('name', '=', $name);
    }
}
