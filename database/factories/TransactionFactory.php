<?php

namespace Database\Factories;

use App\Data\Enums\TransactionApprovalStatuses;
use App\Data\Enums\TransactionApprovalTypes;
use App\Data\Enums\TransactionTypes;
use App\Models\Chore;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Transaction>
 */
class TransactionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'user_id' => User::factory(),
            'chore_id' => Chore::factory(),
            'transaction_amount' => 1000,
            'transaction_type' => TransactionTypes::$WITHDRAW,
            'transaction_approval_type' => TransactionApprovalTypes::$NO_APPROVAL_NEEDED,
            'approval_requested' => false,
            'approval_request_date' => null,
            'approval_status' => TransactionApprovalStatuses::$NONE,
            'approval_date' => null
        ];
    }
}
