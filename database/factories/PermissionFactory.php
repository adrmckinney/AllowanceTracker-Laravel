<?php

namespace Database\Factories;

use App\Data\Enums\ChoreApprovalStatuses;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Chore>
 */
class PermissionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $name = $this->faker->name();

        return [
            'name' => strtolower(str_replace(' ', '_', $name)),
            'display_name' => $name,
        ];
    }
}
