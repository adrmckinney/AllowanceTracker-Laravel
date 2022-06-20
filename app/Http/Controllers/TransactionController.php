<?php

namespace App\Http\Controllers;

use App\Data\Enums\PermissionTypes;
use App\Data\Enums\TransactionApprovalStatuses;
use App\Data\Enums\TransactionApprovalTypes;
use App\Data\Enums\TransactionTypes;
use App\Data\Traits\TransactionTrait;
use App\Data\Traits\UserTrait;
use App\Models\Transaction;
use App\Types\Transactions\TransactionType;
use Exception;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    use UserTrait, TransactionTrait;

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
        $transferPassiveUser = $this->getPassiveUser($transaction);

        switch ($transaction->transaction_type) {
            case TransactionTypes::$DEPOSIT:
                $this->addMoneyToWallet($user, $transaction->transaction_amount);
                break;
            case TransactionTypes::$WITHDRAW:
                $this->removeMoneyFromWallet($user, $transaction->transaction_amount);
                break;
            case TransactionTypes::$TRANSFER_DEPOSIT:
                $this->removeMoneyFromWallet($user, $transaction->transaction_amount);
                $this->addMoneyToWallet($transferPassiveUser, $transaction->transaction_amount);
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
                $this->checkSpendPolicies($request);

                $this->setTransactionApprovalType($request);

                if (
                    $request->transaction_approval_type === TransactionApprovalTypes::$OVERDRAFT_APPROVAL_NEEDED
                ) {

                    // TODO return json response 'request pending'
                    // TODO if rejected then delete transaction (should be soft delete)
                    $transaction = $this->createTransaction($request);
                    $transaction = $this->handleApprovalRequest($transaction);

                    return $transaction;
                } else {
                    $transaction = $this->createTransaction($request);
                    $transaction = $this->setApprovalStatusToNone($transaction);

                    $this->updateWallet($transaction);

                    return $transaction;
                }

            case TransactionTypes::$TRANSFER_WITHDRAW:
                $transaction = $this->createTransaction($request);
                $this->updateWallet($transaction);

                return $transaction;
            case TransactionTypes::$TRANSFER_DEPOSIT:

                $transaction = $this->createTransaction($request);
                $transaction = $this->handleApprovalRequest($transaction);
                $transferPassiveUser = $this->findUser($request->transfer_passive_user_id);

                return [
                    'transaction' => $transaction,
                    'message' => $this->getApprovalMessage($request, $transferPassiveUser)
                ];
        }
    }

    public function approveTransaction(Request $request)
    {
        $user = $request->user();
        $transaction = $this->getTransactionById($request->id);
        $this->checkApprovalPolicies($request, $transaction);

        switch ($request->approval_status) {
            case TransactionApprovalStatuses::$APPROVED:
                $this->adjustApprovalType($transaction, $user);

                if (
                    $transaction->transaction_approval_type === TransactionApprovalTypes::$APPROVED ||
                    $transaction->transaction_approval_type === TransactionApprovalTypes::$NO_APPROVAL_NEEDED
                ) {
                    $transaction['approval_status'] = $request->approval_status;
                    $transaction['approval_date'] = date('Y-m-d H:i:s', time());
                    $transaction->save();

                    $this->updateWallet($transaction);
                    return $transaction;
                } else {
                    $transaction['approval_date'] = date('Y-m-d H:i:s', time());
                    $transaction->save();
                    $passiveUser = $this->getPassiveUser($transaction);

                    return [
                        'transaction' => $transaction,
                        'message' => $this->getApprovalMessage($transaction, $passiveUser)
                    ];
                }
        }
    }

    public function createTransaction($request)
    {
        $newTransaction = new TransactionType($request->input());
        return Transaction::create($newTransaction->toCreateArray());
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
