<?php

namespace App\Http\Controllers;

use App\Data\Enums\TransactionApprovalStatuses;
use App\Data\Enums\TransactionTypes;
use App\Data\Enums\UserChoreApprovalStatuses;
use App\Data\Traits\UserTrait;
use App\Models\Transaction;
use App\Types\Transactions\TransactionType;
use Error;
use Exception;
use GuzzleHttp\Psr7\Message;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    use UserTrait;

    protected $userController;

    public function __construct(UserController $userController)
    {
        $this->userController = $userController;
    }

    public function updateWallet($transaction)
    {
        if ($transaction->approval_status === TransactionApprovalStatuses::$PENDING) {
            return new Exception('waiting for approval before updating wallet');
        }

        $user = $this->findUser($transaction->user_id);

        if (!!$transaction->transfer_passive_user_id) {
            $transferPassiveUser = $this->findUser($transaction->transfer_passive_user_id);
        }

        switch ($transaction->transaction_type) {
            case TransactionTypes::$DEPOSIT:
                $this->addMoneyToWallet($user, $transaction->transaction_amount);
                break;
            case TransactionTypes::$WITHDRAW:
                $this->removeMoneyFromWallet($user, $transaction->transaction_amount);
                break;
            case TransactionTypes::$TRANSFER_DEPOSIT:
                $this->addMoneyToWallet($user, $transaction->transaction_amount);
                $this->removeMoneyFromWallet($transferPassiveUser, $transaction->transaction_amount);
                break;
            case TransactionTypes::$TRANSFER_WITHDRAW:
                $this->removeMoneyFromWallet($user, $transaction->transaction_amount);
                $this->addMoneyToWallet($transferPassiveUser, $transaction->transaction_amount);
                break;
        }
    }

    public function getTransaction(Request $request, $id)
    {
        if ($request->user()->cannot('viewOne', Transaction::class)) {
            abort(403, "You do not have access to see this transaction");
        }
        return $this->getTransactionById($id);
    }

    public function getTransactionsList(Request $request)
    {
        if ($request->user()->cannot('viewMany', Transaction::class)) {
            abort(403, "You do not have access to see transactions");
        }
        return $this->getAllTransactions();
    }

    public function spendTransaction(Request $request)
    {
        switch ($request->transaction_type) {
            case TransactionTypes::$DEPOSIT:
            case TransactionTypes::$WITHDRAW:
                $this->checkPolicyPermissions($request);

                [$difference, $isOverdraft] = $this->checkTransactionPermissions($request);

                if ($isOverdraft) {
                    // create transaction but do not update wallet
                    // update transaction to set the request
                    // return json response 'request pending'
                    // once approval is given then update wallet
                    // if rejected then delete transaction (should be soft delete)
                    $transaction = $this->createTransaction($request);
                    $transaction = $this->handleApprovalRequest($transaction);
                    // $transaction = $this->requestApproval($request, $transaction);
                    // dump('$transaction', $transaction);
                    $this->updateWallet($transaction);

                    return $transaction;
                } else {
                    $transaction = $this->createTransaction($request);

                    $transaction = $this->setApprovalStatusToNone($transaction);

                    $this->updateWallet($transaction);

                    return $transaction;
                }

            case TransactionTypes::$TRANSFER_DEPOSIT:
            case TransactionTypes::$TRANSFER_WITHDRAW:
                $transaction = $this->createTransaction($request);

                $this->updateWallet($transaction);

                return $transaction;
        }
    }

    public function approveTransaction(Request $request)
    {
        $transaction = $this->getTransactionById($request->id);

        switch ($request->approval_status) {
            case TransactionApprovalStatuses::$APPROVED:
                $transaction['approval_status'] = $request->approval_status;
                $transaction['approval_date'] = date('Y-m-d H:i:s', time());
                $transaction->save();
                $this->updateWallet($transaction);

                return $transaction;
        }
    }

    public function createTransaction($request)
    {
        $newTransaction = new TransactionType($request->input());
        return Transaction::create($newTransaction->toCreateArray());
    }

    public function checkTransactionPermissions($request)
    {
        $user = $this->findUser($request->user_id);

        if (!!$request->transfer_passive_user_id) {
            $transferPassiveUser = $this->findUser($request->transfer_passive_user_id);
        }

        // transfer-Deposit = passive permission needed
        // transfer withraw more than have in wallet = parent permission
        // transfer-deposit with withdraw of more than in wallet = passive and parent permissions


        // spending more than have in wallet = parent permission
        $difference = $user->wallet - $request->transaction_amount;
        $isOverdraft = $difference < 0;

        return [$difference, $isOverdraft];
    }

    public function checkPolicyPermissions($request)
    {
        if ($request->user()->id === $request->user_id) {
            if ($request->user()->cannot('spendOwnMoney', Transaction::class)) {
                abort(403, "You do not have access to spend money");
            }
        } else {
            if ($request->user()->cannot('spendOtherMoney', Transaction::class)) {
                abort(403, "You do not have access to spend money");
            }
        }
    }


    public function handleApprovalRequest($transaction)
    {
        $transaction['approval_requested'] = true;
        $transaction['approval_request_date'] = date('Y-m-d H:i:s', time());
        $transaction['approval_status'] = TransactionApprovalStatuses::$PENDING;
        $transaction->save();

        return $transaction;
    }

    public function setApprovalStatusToNone($transaction)
    {
        $transaction['approval_status'] = TransactionApprovalStatuses::$NONE;
        $transaction->save();

        return $transaction;
    }

    public function getTransactionById($id)
    {
        return Transaction::find($id);
    }

    public function getAllTransactions()
    {
        return Transaction::all();
    }

    public function findUser($userId)
    {
        return $this->userController->getUserById($userId);
    }
}
