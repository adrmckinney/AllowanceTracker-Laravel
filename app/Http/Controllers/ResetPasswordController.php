<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class ResetPasswordController extends Controller
{
    public function resetPassword(Request $request)
    {
        $user = $request->user();

        if ($request->email !== $user->email) {
            abort(422, "The email you provided is not correct");
        }

        // $request->validate([
        //     'api_token' => 'required',
        //     'email' => 'required|email',
        //     'password' => 'required|min:8|confirmed',
        // ]);

        $validator = Validator::make($request->all(), [
            'api_token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);

        dump('validator', $validator->fails());
        dump('validator', $validator->errors());

        if ($validator->fails()) {
            return new JsonResponse(['success' => false, 'message' => $validator->errors()], 422);
        }


        $user->update([
            'password' => Hash::make($request->password)
        ]);

        return $user;
    }
}
