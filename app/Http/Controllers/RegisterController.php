<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Requests\RegisterRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class RegisterController extends Controller
{

    public function show()
    {
        // return view('auth.register');
        return 'hello';
    }

    /**
     * Handle account registration request
     * 
     * @param Request $request
     */
    public function register(Request $request)
    {
        // $user = User::create($request->input());
        $user = User::create([
            'name' => $request['name'],
            'username' => $request['username'],
            'email' => $request['email'],
            'password' => Hash::make($request['password']),
            'api_token' => Str::random(60),
        ]);


        auth()->login($user);

        // return redirect('/')->with('success', "Account successfully registered.");
        return response()->json([
            'messsage' => 'user successfully created!',
            $user
        ], 201);
    }


    // public function __invoke()
    // {
    // $user = User::create($request->validated());

    // auth()->login($user);

    // // return redirect('/')->with('success', "Account successfully registered.");
    // return response()->json([
    //     'messsage' => 'user successfully created!'
    // ], 201);
    // }

    // /**
    //  * Handle account registration request
    //  * 
    //  * @param RegisterRequest $request
    //  * 
    //  * @return \Illuminate\Http\Response
    //  */
    // public function register(RegisterRequest $request)
    // {
    //     $user = User::create($request->validated());

    //     auth()->login($user);

    //     // return redirect('/')->with('success', "Account successfully registered.");
    //     return response()->json([
    //         'messsage' => 'user successfully created!'
    //     ], 201);
    // }
}
