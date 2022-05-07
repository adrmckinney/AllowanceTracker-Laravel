<?php

namespace App\Http\Controllers;

use App\Data\Enums\ChoreApprovalStatuses;
use App\Models\UserChore;
use DateTime;
use Illuminate\Http\Request;

class UserChoreController extends Controller
{
    public function addChoreToUser(Request $request)
    {
        // if ($field === 'approval_requested' && $request->$field === true) {
        //     $chore['approval_request_date'] = date('Y-m-d H:i:s', time());
        //     $chore['approval_status'] = ChoreApprovalStatuses::$PENDING;

        //     return $chore;
        // } else {
        //     return $chore;
        // }

        $newUserChore = UserChore::create([
            'user_id' => $request['user_id'],
            'chore_id' => $request['chore_id'],
            'approval_requested' => false,
            'approval_request_date' => NULL,
            'approval_status' => ChoreApprovalStatuses::$NONE,
            'approval_date' => NULL,
        ]);

        $userChore = $this->getUserChoreById($newUserChore->id);

        if ($request->user()->cannot('add', $userChore)) {
            abort(403, 'You do not have access to be added to this chore');
        }

        return $newUserChore;




        // public function createChore(Request $request)
        // {


        //     if ($request->user()->cannot('create', $chore)) {
        //         abort(403, 'You do not have access to create a chore');
        //     }

        //     return $chore;
        // }
    }

    public function getUserChoreById($id)
    {
        return UserChore::find($id);
    }
}
