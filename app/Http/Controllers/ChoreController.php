<?php

namespace App\Http\Controllers;

use App\Data\Enums\ChoreApprovalStatuses;
use App\Models\Chore;
use Illuminate\Http\Request;

class ChoreController extends Controller
{
    public function createChore(Request $request)
    {
        $chore = Chore::create([
            'name' => $request['name'],
            'description' => $request['description'],
            'cost' => $request['cost'],
            'user_id' => $request['user_id'],
            'approval_requested' => $request['approval_requested'],
            'approval_request_date' => $request['approval_request_date'],
            'approval_status' => $request['approval_status'],
            'approval_date' => $request['approval_date'],
        ]);

        return $chore;
    }

    public function updateChore(Request $request)
    {
        $fields = [
            'name',
            'description',
            'cost',
            'user_id',
            'approval_requested',
            'approval_request_date',
            'approval_status',
            'approval_date'
        ];

        $chore = $this->getChoreById($request->id);

        foreach ($fields as $field) {
            if ($request->$field) {
                $this->isRequestingApproval($chore, $request, $field);
                $chore->$field = $request->$field;

                $chore->save();
            }
        }
        return $chore;
    }

    public function isRequestingApproval($chore, $request, $field)
    {
        if ($field === 'approval_requested' && $request->$field === true) {
            $chore['approval_request_date'] = date('Y-m-d H:i:s', time());
            $chore['approval_status'] = ChoreApprovalStatuses::$PENDING;

            return $chore;
        } else {
            return $chore;
        }
    }

    public function getChoreById($id)
    {
        return Chore::find($id);
    }
}
