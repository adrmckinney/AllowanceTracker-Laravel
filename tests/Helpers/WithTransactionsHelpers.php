<?php

namespace Tests\Helpers;

use App\Data\Enums\TransactionApprovalStatuses;
use App\Models\Transaction;

/**
 * @package Tests\Helpers
 */
trait WithTransactionsHelpers
{
    public function createPendingTransaction(
        $userId,
        $transactionType,
        $transactionApprovalType,
        $amount,
        $passiveUserId = null
    ) {
        return Transaction::factory()->create([
            'user_id' => $userId,
            'chore_id' => null,
            'transfer_passive_user_id' => $passiveUserId,
            'transaction_type' => $transactionType,
            'transaction_approval_type' => $transactionApprovalType,
            'transaction_amount' => $amount,
            'approval_requested' => true,
            'approval_request_date' => date('Y-m-d H:i:s', time()),
            'approval_status' => TransactionApprovalStatuses::$PENDING
        ]);
    }
}
