<?php

namespace App\Policies;

use App\Models\User;

abstract class AbstractPolicy
{
    // public function before(User $user)
    // {
    //     if ($user->isAdmin()) {
    //         return true;
    //     }

    //     if ($user->noAccess()) {
    //         return false;
    //     }
    // }

    // public function access()
    // {
    //     return true;
    // }
}
