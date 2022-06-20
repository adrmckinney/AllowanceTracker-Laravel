<?php

namespace App\Data\Traits;

use App\Data\Enums\PermissionTypes;
use App\Data\Enums\TransactionApprovalStatuses;
use App\Data\Enums\TransactionApprovalTypes;
use App\Data\Enums\TransactionTypes;

trait TransactionTrait
{
    public function setTransactionApprovalType($transaction): void
    {
        $user = $this->findUser($transaction->user_id);
        $transferPassiveUser = $this->getPassiveUser($transaction);

        $isTransferRequest =
            !is_null($transferPassiveUser)
            && ($transaction->transaction_type === TransactionTypes::$TRANSFER_DEPOSIT);


        $difference = $user->wallet - $transaction->transaction_amount;
        $isOverdraft = $difference < 0;

        if ($isTransferRequest && $isOverdraft) {
            $transaction['transaction_approval_type'] = TransactionApprovalTypes::$OVERDRAFT_AND_TRANSFER_APPROVAL_NEEDED;
        } elseif ($isTransferRequest) {
            $transaction['transaction_approval_type'] = TransactionApprovalTypes::$TRANSFER_APPROVAL_NEEDED;
        } elseif ($isOverdraft) {
            $transaction['transaction_approval_type'] = TransactionApprovalTypes::$OVERDRAFT_APPROVAL_NEEDED;
        } else {
            $transaction['transaction_approval_type'] = TransactionApprovalTypes::$NO_APPROVAL_NEEDED;
        }
    }

    public function checkSpendPolicies($request)
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

    public function checkApprovalPolicies($request, $transaction)
    {
        $permissionId = $request->user()->permissions->toArray()[0]['permission_id'];
        // if parent and transfer, parent cannot
        switch ($transaction->transaction_type) {
            case TransactionTypes::$WITHDRAW:
                if ($request->user()->cannot('approveSpend', Transaction::class)) {
                    abort(403, "You do not have access to approve spend");
                }
                break;
            case TransactionTypes::$TRANSFER_DEPOSIT:
                // if parent and isOverdraft
                // then child cannot
                if ($permissionId === PermissionTypes::$PARENT) {
                    if ($request->user()->cannot('approveSpend', Transaction::class)) {
                        abort(403, "A child does not have access to approve the amount of this transfer");
                    }
                }

                if (
                    $permissionId === PermissionTypes::$PARENT &&
                    $transaction->transaction_approval_type === TransactionApprovalTypes::$TRANSFER_APPROVAL_NEEDED
                ) {
                    if ($request->user()->cannot('approveTransfer', Transaction::class)) {
                        abort(403, "A parent does not have access to approve this transfer");
                    }
                }
                // if child isTransferRequest
                // then parent cannot
                if ($permissionId === PermissionTypes::$CHILD) {
                    if ($request->user()->cannot('approveTransfer', Transaction::class)) {
                        abort(403, "A parent does not have access to approve this transfer");
                    }
                }
                break;
        }
    }

    public function adjustApprovalType($transaction, $user)
    {
        switch ($transaction->transaction_approval_type) {
            case TransactionApprovalTypes::$TRANSFER_APPROVAL_NEEDED:
                return $transaction['transaction_approval_type'] = TransactionApprovalTypes::$APPROVED;
            case TransactionApprovalTypes::$OVERDRAFT_APPROVAL_NEEDED:
                return $transaction['transaction_approval_type'] = TransactionApprovalTypes::$APPROVED;
            case TransactionApprovalTypes::$OVERDRAFT_AND_TRANSFER_APPROVAL_NEEDED:
                $permissionId = $user->permissions->toArray()[0]['permission_id'];
                if ($permissionId === PermissionTypes::$PARENT) {
                    return $transaction['transaction_approval_type'] = TransactionApprovalTypes::$TRANSFER_APPROVAL_NEEDED;
                }

                if ($permissionId === PermissionTypes::$CHILD) {
                    return $transaction['transaction_approval_type'] = TransactionApprovalTypes::$OVERDRAFT_APPROVAL_NEEDED;
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

    public function getApprovalMessage($transaction, $transferPassiveUser)
    {
        switch ($transaction->transaction_approval_type) {
            case TransactionApprovalTypes::$OVERDRAFT_APPROVAL_NEEDED:
                return "Awaiting approval from a parent";
            case TransactionApprovalTypes::$TRANSFER_APPROVAL_NEEDED:
                return "Awaiting approval from {$transferPassiveUser->name}";
            case TransactionApprovalTypes::$OVERDRAFT_AND_TRANSFER_APPROVAL_NEEDED:
                return "Awaiting approval from {$transferPassiveUser->name} and a parent";
        }
    }

    public function getPassiveUser($request)
    {
        return
            !is_null($request->transfer_passive_user_id)
            ? $this->findUser($request->transfer_passive_user_id)
            : null;
    }
}
