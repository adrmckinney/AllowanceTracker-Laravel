<?php

namespace App\Data\Traits;

use App\Models\User;
use App\Types\Users\UserType;

trait UserTrait
{
    public function createUser(UserType $userData)
    {
        return User::create($userData->toCreateArray());
    }

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
