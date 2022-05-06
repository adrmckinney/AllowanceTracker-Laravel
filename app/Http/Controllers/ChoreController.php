<?php

namespace App\Http\Controllers;

use App\Data\Enums\ChoreApprovalStatuses;
use App\Models\Chore;
use Exception;
use Illuminate\Auth\Access\Response;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\File\Exception\AccessDeniedException;

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

        if ($request->user()->cannot('create', $chore)) {
            abort(403, 'You do not have access to create a chore');
        }

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

        if (!$this->isRequestingApproval($request, 'approval_requested')) {
            if ($request->user()->cannot('update', $chore)) {
                abort(403, 'You do not have access to update this chore');
            }
        }


        foreach ($fields as $field) {
            if ($request->$field) {
                if ($this->isRequestingApproval($request, $field)) {
                    $this->handleApprovalRequest($chore);
                }

                $chore->$field = $request->$field;

                $chore->save();
            }
        }
        return $chore;
    }

    public function isRequestingApproval($request, $field)
    {
        return ($field === 'approval_requested' && $request->$field === true);

        // if ($field === 'approval_requested' && $request->$field === true) {
        //     $chore['approval_request_date'] = date('Y-m-d H:i:s', time());
        //     $chore['approval_status'] = ChoreApprovalStatuses::$PENDING;

        //     return $chore;
        // } else {
        //     return $chore;
        // }
    }

    public function handleApprovalRequest($chore)
    {
        $chore['approval_request_date'] = date('Y-m-d H:i:s', time());
        $chore['approval_status'] = ChoreApprovalStatuses::$PENDING;

        return $chore;
    }

    public function getChoreById($id)
    {
        return Chore::find($id);
    }
}
