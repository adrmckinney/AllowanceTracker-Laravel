<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\LoginRequest;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{

    // /**
    //  * Handle account login request
    //  * 
    //  * @param Request $request
    //  * 
    //  */
    // public function login(Request $request)
    // {

    //     $user = User::where('username', $request->username)->first();

    //     if (!$user || !Hash::check($request->password, $user->password)) {
    //         return ['error' => 'Username or Password is incorrect'];
    //     };

    //     return $user;
    // }

    /**
     * Handle account login request
     * 
     * @param LoginRequest $request
     * 
     * @return \Illuminate\Http\Response
     */
    public function login(LoginRequest $request)
    {
        $credentials = $request->getCredentials();

        if (!Auth::validate($credentials)) {
            // return redirect()->to('login')
            //     ->withErrors(trans('auth.failed'));
            return ['error' => 'Username or Password is incorrect'];
        }

        $user = Auth::getProvider()->retrieveByCredentials($credentials);

        Auth::login($user);

        return [$this->authenticated($request, $user)->getStatusCode(), $user];
    }

    /**
     * Handle response after user authenticated
     * 
     * @param Request $request
     * @param Auth $user
     * 
     * @return \Illuminate\Http\Response
     */
    protected function authenticated(Request $request, $user)
    {
        // return redirect()->intended();
        return new JsonResponse(['message' => 'something']);
    }
}
