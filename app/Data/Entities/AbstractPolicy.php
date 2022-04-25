<?php

namespace App\Data\Abstractions;

use App\Models\User;

abstract class AbstractPolicy
{
    public function before(User $user)
    {
        if ($user->isParent()) {
            return true;
        }

        if ($user->isChild()) {
            return false;
        }
    }

    public function access()
    {
        return true;
    }
}
