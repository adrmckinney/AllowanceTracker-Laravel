<?php

namespace Database\Factories;

use App\Data\Enums\ChoreApprovalStatuses;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Chore>
 */
class ChoreFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'name' => $this->faker->name,
            'description' => $this->faker->paragraph(),
            'cost' => 0,
            'user_id' => User::factory()->create()->id,
            'approval_requested' => false,
            'approval_request_date' => null,
            'approval_status' => ChoreApprovalStatuses::$NONE,
            'approval_date' => null,
        ];
    }
}
