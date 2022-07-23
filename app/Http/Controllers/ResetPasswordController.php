<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class ResetPasswordController extends Controller
{
    public function resetPassword(Request $request)
    {
        $user = $request->user();

        if ($request->email !== $user->email) {
            abort(422, "The email you provided is not correct");
        }

        if ($request->password !== $request->password_confirmation) {
            abort(422, "You new password does not match your confirmed password");
        }

        $pwdMatches = Hash::check($request->oldPassword, $user->password);

        if (!$pwdMatches) {
            abort(403, 'The old password did not match');
        }

        $validator = Validator::make($request->all(), [
            'api_token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return new JsonResponse(['success' => false, 'message' => $validator->errors()], 422);
        }

        $user->update([
            'password' => Hash::make($request->password)
        ]);

        return $user;
    }
}
