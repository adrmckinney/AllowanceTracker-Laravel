<?php

namespace App\Http\Controllers;

use App\Data\Enums\TransactionTypes;
use App\Data\Enums\UserChoreApprovalStatuses;
use App\Data\Traits\UserTrait;
use App\Models\UserChore;
use App\Types\Transactions\TransactionType;
use Illuminate\Database\Events\TransactionCommitted;
use Illuminate\Http\Request;

class UserChoreController extends Controller
{
    use UserTrait;

    protected $choreController, $userController, $transactionController;

    public function __construct(
        ChoreController $choreController,
        UserController $userController,
        TransactionController $transactionController
    ) {
        $this->choreController = $choreController;
        $this->userController = $userController;
        $this->transactionController = $transactionController;
    }

    public function getUserChore(Request $request)
    {
        if ($request->user()->cannot('getOne', UserChore::class)) {
            abort(403, 'You do not have access to get this chore');
        }

        $userChore = $this->getUserChoreById($request->id);


        return $userChore;
    }

    public function getUserChores(Request $request, $userIdOfChores)
    {
        if ($request->user()->cannot('getMany', UserChore::class)) {
            abort(403, 'You do not have access to get chores');
        }

        $userChores = $this->getChoresOfUser($userIdOfChores);

        return $userChores;
    }

    public function getChoreUsers(Request $request, $choreId)
    {
        if ($request->user()->cannot('getMany', UserChore::class)) {
            abort(403, 'You do not have access to get chores');
        }

        $userChores = $this->getChoresByUserId($choreId);

        return $userChores;
    }

    public function addChoreToUser(Request $request)
    {
        if ($request->user()->cannot('add', UserChore::class)) {
            abort(403, 'You do not have access to be added to this chore');
        }

        $newUserChore = UserChore::create([
            'user_id' => $request['user_id'],
            'chore_id' => $request['chore_id'],
            'approval_requested' => false,
            'approval_request_date' => NULL,
            'approval_status' => UserChoreApprovalStatuses::$NONE,
            'approval_date' => NULL,
        ]);

        return $newUserChore;
    }

    public function removeChoreFromUser(Request $request)
    {
        if ($request->user()->cannot('remove', UserChore::class)) {
            abort(403, 'You do not have access to remove to this chore');
        }
        $userChore = $this->getUserChoreById($request->id);
        $userChore->delete();

        return $userChore;
    }

    public function requestApproval(Request $request)
    {
        if ($request->user()->cannot('requestApproval', UserChore::class)) {
            abort(403, 'You do not have access to request approval');
        }
        $userChore = $this->getUserChoreById($request->id);
        $userChore = $this->handleApprovalRequest($userChore, $request);

        $userChore->save();

        return $userChore;
    }

    public function approveWork(Request $request)
    {
        $statusName = UserChoreApprovalStatuses::getStatusName($request->approval_status);
        if ($request->user()->cannot('approveWork', UserChore::class)) {
            abort(403, "You do not have access to {$statusName} work");
        }

        $userChore = $this->getUserChoreById($request->id);
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
        $user = $this->findUser($userChore->user_id);
        $userChoreCurrentStatus = $userChore->approval_status;
        $choreCost = $this->findChoreCost($userChore->chore_id);

        switch ($request->approval_status) {
            case UserChoreApprovalStatuses::$APPROVED:
                $userChore['approval_status'] = UserChoreApprovalStatuses::$APPROVED;
                $userChore['approval_date'] = date('Y-m-d H:i:s', time());
                $userChore['rejected_date'] = NULL;

                $transaction = new TransactionType([
                    'user_id' => $userChore->user_id,
                    'chore_id' => $userChore->chore_id,
                    'transaction_amount' => $choreCost,
                    'transaction_type' => TransactionTypes::$DEPOSIT
                ]);
                $this->transactionController->createTransaction($transaction);
                $this->addMoneyToWallet($user, $choreCost);

                return $userChore;

            case UserChoreApprovalStatuses::$REJECTED:
                $userChore['approval_status'] = UserChoreApprovalStatuses::$REJECTED;
                $userChore['approval_date'] = NULL;
                $userChore['rejected_date'] = date('Y-m-d H:i:s', time());

                if ($userChoreCurrentStatus === UserChoreApprovalStatuses::$APPROVED) {
                    $this->removeMoneyFromWallet($user, $choreCost);
                }

                return $userChore;

            case UserChoreApprovalStatuses::$PENDING:
                $userChore['approval_status'] = UserChoreApprovalStatuses::$PENDING;
                $userChore['approval_date'] = NULL;
                $userChore['rejected_date'] = NULL;

                if ($userChoreCurrentStatus === UserChoreApprovalStatuses::$APPROVED) {
                    $this->removeMoneyFromWallet($user, $choreCost);
                }

                return $userChore;

            default:
                return $userChore;
        }

        return $userChore;
    }

    public function findUser($userId)
    {
        return $this->userController->getUserById($userId);
    }

    public function findChoreCost($choreId)
    {
        return $this->choreController->getChoreById($choreId)->cost;
    }
}
