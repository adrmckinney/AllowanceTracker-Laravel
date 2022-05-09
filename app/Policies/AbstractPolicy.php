<?php

namespace App\Policies;

use App\Data\Traits\UserAuthorizableTrait;
use App\Models\User;

abstract class AbstractPolicy
{
    use UserAuthorizableTrait;

    public function before(User $user)
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isNoAccessUser()) {
            return false;
        }
    }
}
