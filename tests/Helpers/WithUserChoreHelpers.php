<?php

namespace Tests\Helpers;

use App\Models\UserChore;

/**
 * Trait WithUserChoreHelpers
 * @package Tests\Helpers
 */
trait WithUserChoreHelpers
{
    public function createUserChore()
    {
        $userChore = UserChore::factory()->create([
            'user_id' => $this->authUser->id,
            'chore_id' => $this->chore->id
        ]);

        return $userChore;
    }

    public function createChoresWithSameUser($user, $chores)
    {
        return collect($chores)->map(function ($chore) use ($user) {
            return UserChore::factory()->create([
                'user_id' => $user->id,
                'chore_id' => $chore->id
            ]);
        });
    }

    public function createChoreWithMultipleUsers($users, $chore)
    {
        return collect($users)->map(function ($user) use ($chore) {
            return UserChore::factory()->create([
                'user_id' => $user->id,
                'chore_id' => $chore->id
            ]);
        });
    }
}
