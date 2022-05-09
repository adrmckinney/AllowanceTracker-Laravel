<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class ResetPasswordController extends Controller
{
    // public function resetPassword(Request $request)
    // {
    //     $request->validate([
    //         'api_token' => 'required',
    //         'email' => 'required|email',
    //         'password' => 'required|min:8|confirmed',
    //     ]);

    //     $status = Password::reset(
    //         $request->only('email', 'password', 'password_confirmation', 'api_token'),
    //         function ($user, $password) {

    //             dump('password', $password);
    //             $user->forceFill([
    //                 'password' => Hash::make($password)
    //             ])->setRememberToken(Str::random(60));
    //             dump('user', $user);
    //             $user->save();

    //             event(new PasswordReset($user));
    //         }
    //     );
    //     dump('is status pw reset', $status === Password::PASSWORD_RESET);

    //     // return $status === Password::PASSWORD_RESET
    //     //     ? redirect()->route('login')->with('status', __($status))
    //     // : back()->withErrors(['email' => [__($status)]]);
    //     dump('status', $status);
    //     return $status;
    // }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function reset(Request $request)
    {

        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string|min:8|confirmed',
            'api_token' => 'required|string'
        ]);

        $passwordReset = PasswordReset::where([
            ['api_token', $request->api_token],
            ['email', $request->email]
        ])->first();

        if (!$passwordReset) {
            return response()->json([
                'error'   => true,
                'message' => 'This Password Reset token is invalid.'
            ], 404);
        }

        $user = User::where('email', $passwordReset->email)->first();
        if (!$user) {
            return response()->json([
                'error'   => true,
                'message' => 'We cannot find a user with that Email Address'
            ], 404);
        }
        $user->password = bcrypt($request->password);
        $user->save();
        $passwordReset->delete();
        // $user->notify(new PasswordResetSuccess($passwordReset));

        return response()->json([
            'error' => false,
            'message' => 'Your Password changed successfully.'
        ]);
    }
}
