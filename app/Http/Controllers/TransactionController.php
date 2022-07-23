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
            return new Exception('Waiting for approval before updating wallet');
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
                    $transactionData = new TransactionType($request->input());
                    $transaction = $this->createTransaction($transactionData);
                    $transaction = $this->handleApprovalRequest($transaction);

                    return $transaction;
                } else {
                    $transactionData = new TransactionType($request->input());
                    $transaction = $this->createTransaction($transactionData);
                    $transaction = $this->setApprovalStatusToNone($transaction);

                    $this->updateWallet($transaction);

                    return $transaction;
                }

            case TransactionTypes::$TRANSFER_WITHDRAW:
                $transactionData = new TransactionType($request->input());
                $transaction = $this->createTransaction($transactionData);

                $this->updateWallet($transaction);

                return $transaction;
            case TransactionTypes::$TRANSFER_DEPOSIT:
                $transactionData = new TransactionType($request->input());
                $transaction = $this->createTransaction($transactionData);
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
        $transactionData = new TransactionType($request->input());
        $transactionData->id = $request->id;
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
                    $transaction['approval_date'] = date('Y-m-d H:i:s', time());
                    $updatedTransaction = $this->updateTransaction($transaction, $transactionData);

                    $this->updateWallet($transaction);

                    return $updatedTransaction;
                } else {
                    $transaction['approval_date'] = date('Y-m-d H:i:s', time());
                    $transaction->save();
                    $passiveUser = $this->getPassiveUser($transaction);

                    return [
                        'transaction' => $transaction,
                        'message' => $this->getApprovalMessage($transaction, $passiveUser)
                    ];
                }
            case TransactionApprovalStatuses::$REJECTED:
                return [
                    'transaction' => $transaction,
                    'message' => 'Transaction cannot be approved because it has already been rejected'
                ];
        }
    }

    public function rejectTransaction(Request $request)
    {
        $transactionData = new TransactionType($request->input());
        $transactionData->id = $request->id;

        $transaction = $this->getTransactionById($request->id);
        $this->checkApprovalPolicies($request, $transaction);

        if ($request->approval_status === TransactionApprovalStatuses::$REJECTED) {
            return $this->updateTransaction($transaction, $transactionData);
        }
    }

    public function createTransaction(TransactionType $transactionData)
    {
        return Transaction::create($transactionData->toCreateArray());
    }

    public function updateTransaction(Transaction $transaction, TransactionType $transactionData)
    {
        $transaction->fill($transactionData->toUpdateArray());
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
