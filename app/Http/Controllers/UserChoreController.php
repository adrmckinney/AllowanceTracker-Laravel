<?php

namespace App\Http\Controllers;

use App\Data\Enums\TransactionTypes;
use App\Data\Enums\UserChoreApprovalStatuses;
use App\Data\Traits\UserTrait;
use App\Models\UserChore;
use App\Types\Transactions\TransactionType;
use App\Types\UserChoreType;
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

    public function getUserChores(Request $request, $userId)
    {
        if ($request->user()->cannot('getMany', UserChore::class)) {
            abort(403, 'You do not have access to get chores');
        }

        return UserChore::where('user_id', '=', $userId)->get();
    }

    public function getChoreUsers(Request $request, $choreId)
    {
        if ($request->user()->cannot('getMany', UserChore::class)) {
            abort(403, 'You do not have access to get chores');
        }
        $userChores = $this->getChoresByUserId($choreId);

        return $userChores;
    }

    public function addChoreToUser(Request $request, $userId, $choreId)
    {
        if ($request->user()->cannot('add', UserChore::class)) {
            abort(403, 'You do not have access to be added to this chore');
        }

        $userChoreData = new UserChoreType([
            'user_id' => $userId,
            'chore_id' => $choreId,
            'approval_requested' => false,
            'approval_request_date' => NULL,
            'approval_status' => UserChoreApprovalStatuses::$NONE,
            'approval_date' => NULL,
        ]);

        return $this->createUserChore($userChoreData);
    }

    public function removeChoreFromUser(Request $request, $id)
    {
        if ($request->user()->cannot('remove', UserChore::class)) {
            abort(403, 'You do not have access to remove to this chore');
        }
        $userChore = $this->getUserChoreById($id);
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

    public function isRequestingApproval($request, $field)
    {
        return ($field === 'approval_requested' && $request->$field === true);
    }

    public function handleApprovalRequest($userChore, $request)
    {
        $userChoreData = new UserChoreType([
            $userChore['approval_requested'] = $request->approval_requested,
            $userChore['approval_request_date'] = date('Y-m-d H:i:s', time()),
            $userChore['approval_status'] = UserChoreApprovalStatuses::$PENDING,
        ]);
        $userChore = $this->updateUserChore($userChore, $userChoreData);

        return $userChore;
    }

    public function handleApproval($userChore, $request)
    {
        $userChoreCurrentStatus = $userChore->approval_status;
        $choreCost = $this->findChoreCost($userChore->chore_id);

        switch ($request->approval_status) {
            case UserChoreApprovalStatuses::$APPROVED:
                $userChoreData = new UserChoreType([
                    $userChore['approval_status'] = UserChoreApprovalStatuses::$APPROVED,
                    $userChore['approval_date'] = date('Y-m-d H:i:s', time()),
                    $userChore['rejected_date'] = NULL,
                ]);

                $this->updateUserChore($userChore, $userChoreData);

                $transaction = new TransactionType([
                    'user_id' => $userChore->user_id,
                    'chore_id' => $userChore->chore_id,
                    'transaction_amount' => $choreCost,
                    'transaction_type' => TransactionTypes::$DEPOSIT
                ]);
                $this->transactionController->createTransaction($transaction);
                $this->transactionController->updateWallet($transaction);
                return $userChore;

            case UserChoreApprovalStatuses::$REJECTED:
                $userChoreData = new UserChoreType([
                    $userChore['approval_status'] = UserChoreApprovalStatuses::$REJECTED,
                    $userChore['approval_date'] = NULL,
                    $userChore['rejected_date'] = date('Y-m-d H:i:s', time()),
                ]);

                $this->updateUserChore($userChore, $userChoreData);

                if ($userChoreCurrentStatus === UserChoreApprovalStatuses::$APPROVED) {
                    $transaction = new TransactionType([
                        'user_id' => $userChore->user_id,
                        'chore_id' => $userChore->chore_id,
                        'transaction_amount' => $choreCost,
                        'transaction_type' => TransactionTypes::$WITHDRAW
                    ]);
                    $this->transactionController->createTransaction($transaction);
                    $this->transactionController->updateWallet($transaction);
                }

                return $userChore;

            case UserChoreApprovalStatuses::$PENDING:
                $userChoreData = new UserChoreType([
                    $userChore['approval_status'] = UserChoreApprovalStatuses::$PENDING,
                    $userChore['approval_date'] = NULL,
                    $userChore['rejected_date'] = NULL,
                ]);
                $this->updateUserChore($userChore, $userChoreData);

                if ($userChoreCurrentStatus === UserChoreApprovalStatuses::$APPROVED) {
                    $transaction = new TransactionType([
                        'user_id' => $userChore->user_id,
                        'chore_id' => $userChore->chore_id,
                        'transaction_amount' => $choreCost,
                        'transaction_type' => TransactionTypes::$WITHDRAW
                    ]);
                    $this->transactionController->createTransaction($transaction);
                    $this->transactionController->updateWallet($transaction);
                }

                return $userChore;

            default:
                return $userChore;
        }

        return $userChore;
    }

    public function createUserChore(UserChoreType $userChoreData)
    {
        return UserChore::create($userChoreData->toCreateArray());
    }

    public function updateUserChore(UserChore $userChore, UserChoreType $userChoreData)
    {
        $userChore->fill($userChoreData->toUpdateArray());
        $userChore->save();

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
