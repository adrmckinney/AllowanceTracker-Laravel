<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UserChoreController extends Controller
{
    // public function createChore(Request $request)
    // {
    //     $chore = Chore::create([
    //         'name' => $request['name'],
    //         'description' => $request['description'],
    //         'cost' => $request['cost'],
    //         'user_id' => $request['user_id'],
    //         'approval_requested' => $request['approval_requested'],
    //         'approval_request_date' => $request['approval_request_date'],
    //         'approval_status' => $request['approval_status'],
    //         'approval_date' => $request['approval_date'],
    //     ]);

    //     if ($request->user()->cannot('create', $chore)) {
    //         abort(403, 'You do not have access to create a chore');
    //     }

    //     return $chore;
    // }
}
