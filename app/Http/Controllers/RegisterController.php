<?php

namespace App\Http\Controllers;

use App\Data\Traits\UserTrait;
use Illuminate\Http\Request;
use App\Types\Users\UserType;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class RegisterController extends Controller
{
    use UserTrait;
    /**
     * Handle account registration request
     * 
     * @param Request $request
     */
    public function register(Request $request)
    {
        $userData = new UserType([
            'name' => $request['name'],
            'username' => $request['username'],
            'email' => $request['email'],
            'password' => Hash::make($request['password']),
            'api_token' => Str::random(60),
        ]);

        $user = $this->createUser($userData);

        auth()->guard('web')->login($user);

        return response()->json([
            'messsage' => 'User successfully created!',
            $user
        ], 201);
    }
}
