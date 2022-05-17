<?php

namespace App\Data\Traits;

use App\Models\Permission;
use App\Models\UsersPermissions;
use Exception;

trait UserTrait
{
    public function addMoneyToWallet($user, $amount)
    {
        $user['wallet'] = $user['wallet'] + $amount;
        $user->save();
    }

    public function removeMoneyFromWallet($user, $amount)
    {
        $user['wallet'] = $user['wallet'] - $amount;
        $user->save();
    }
}
