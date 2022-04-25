<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
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
}
