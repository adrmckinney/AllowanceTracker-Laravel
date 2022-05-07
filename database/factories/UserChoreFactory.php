<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\UserChore>
 */
class UserChoreFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            // 'user_id' => User::factory(),
            // 'approval_requested' => false,
            // 'approval_request_date' => null,
            // 'approval_status' => ChoreApprovalStatuses::$NONE,
            // 'approval_date' => null,
        ];
    }
}
