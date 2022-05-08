<?php

namespace App\Http\Controllers;

use App\Data\Enums\UserChoreApprovalStatuses;
use App\Models\UserChore;
use Illuminate\Http\Request;

class UserChoreController extends Controller
{
    public function getUserChore(Request $request)
    {
        $userChore = $this->getUserChoreById($request->id);

        if ($request->user()->cannot('getOne', $userChore)) {
            abort(403, 'You do not have access to get this chore');
        }

        return $userChore;
    }

    public function getUserChores(Request $request)
    {
        $userChores = $this->getChoresOfUser($request->id);

        if ($request->user()->cannot('getMany', $userChores[0])) {
            abort(403, 'You do not have access to get chores');
        }

        return $userChores;
    }

    public function getChoreUsers(Request $request)
    {
        $userChores = $this->getChoresByUserId($request->id);

        if ($request->user()->cannot('getMany', $userChores[0])) {
            abort(403, 'You do not have access to get chores');
        }

        return $userChores;
    }

    public function addChoreToUser(Request $request)
    {
        $newUserChore = UserChore::create([
            'user_id' => $request['user_id'],
            'chore_id' => $request['chore_id'],
            'approval_requested' => false,
            'approval_request_date' => NULL,
            'approval_status' => UserChoreApprovalStatuses::$NONE,
            'approval_date' => NULL,
        ]);

        $userChore = $this->getUserChoreById($newUserChore->id);

        if ($request->user()->cannot('add', $userChore)) {
            abort(403, 'You do not have access to be added to this chore');
        }

        return $newUserChore;
    }

    public function removeChoreFromUser(Request $request)
    {
        $userChore = $this->getUserChoreById($request->id);
        if ($request->user()->cannot('remove', $userChore)) {
            abort(403, 'You do not have access to remove to this chore');
        }
        $userChore->delete();

        return $userChore;
    }

    public function requestApproval(Request $request)
    {
        $userChore = $this->getUserChoreById($request->id);

        if ($request->user()->cannot('requestApproval', $userChore)) {
            abort(403, 'You do not have access to request approval');
        }

        $userChore = $this->handleApprovalRequest($userChore, $request);

        $userChore->save();

        return $userChore;
    }

    public function approveWork(Request $request)
    {
        $userChore = $this->getUserChoreById($request->id);
        $statusName = UserChoreApprovalStatuses::getStatusName($request->approval_status);

        if ($request->user()->cannot('approveWork', $userChore)) {
            abort(403, "You do not have access to {$statusName} work");
        }

        $userChore = $this->handleApproval($userChore, $request);

        $userChore->save();

        return $userChore;
    }

    public function getUserChoreById($id)
    {
        return UserChore::find($id);
    }

    public function getChoresByUserId($chore_id)
    {
        return UserChore::where('chore_id', '=', $chore_id)->get();
    }

    public function getChoresOfUser($user_id)
    {
        return UserChore::where('user_id', '=', $user_id)->get();
    }

    public function isRequestingApproval($request, $field)
    {
        return ($field === 'approval_requested' && $request->$field === true);
    }

    public function handleApprovalRequest($userChore, $request)
    {
        $userChore['approval_requested'] = $request->approval_requested;
        $userChore['approval_request_date'] = date('Y-m-d H:i:s', time());
        $userChore['approval_status'] = UserChoreApprovalStatuses::$PENDING;

        return $userChore;
    }

    public function handleApproval($userChore, $request)
    {
        switch ($request->approval_status) {
            case UserChoreApprovalStatuses::$APPROVED:
                $userChore['approval_status'] = UserChoreApprovalStatuses::$APPROVED;
                $userChore['approval_date'] = date('Y-m-d H:i:s', time());
                $userChore['rejected_date'] = NULL;

                return $userChore;

            case UserChoreApprovalStatuses::$REJECTED:
                $userChore['approval_status'] = UserChoreApprovalStatuses::$REJECTED;
                $userChore['approval_date'] = NULL;
                $userChore['rejected_date'] = date('Y-m-d H:i:s', time());

                return $userChore;

            case UserChoreApprovalStatuses::$PENDING:
                $userChore['approval_status'] = UserChoreApprovalStatuses::$PENDING;
                $userChore['approval_date'] = NULL;
                $userChore['rejected_date'] = NULL;

                return $userChore;

            default:
                return $userChore;
        }

        return $userChore;
    }
}
