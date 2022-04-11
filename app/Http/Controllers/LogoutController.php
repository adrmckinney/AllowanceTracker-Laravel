<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class LogoutController extends Controller
{
    public function logout(Request $request)
    {

        Auth::logout();

        // $request->session()->invalidate();

        // $request->session()->regenerateToken();

        return "You are logged out";
    }

    // /**
    //  * Log out account user.
    //  *
    //  * @return \Illuminate\Routing\Redirector
    //  */
    // public function perform()
    // {
    //     Session::flush();

    //     Auth::logout();

    //     return "You are logged out";
    // }
}
