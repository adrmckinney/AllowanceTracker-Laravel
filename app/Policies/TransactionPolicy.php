<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class TransactionPolicy extends AbstractPolicy
{
    use HandlesAuthorization;


    /**
     * Determine if all permissions can be fetched by the user.
     *
     * @param  \App\Models\User  $user
     * @return bool
     */
    public function add(User $user)
    {
        $permissionId = $user->permissions->toArray()[0]['permission_id'];

        return $this->isParent($permissionId);
    }

    /**
     * Determine if all permissions can be fetched by the user.
     *
     * @param  \App\Models\User  $user
     * @return bool
     */
    public function update(User $user)
    {
        $permissionId = $user->permissions->toArray()[0]['permission_id'];

        return $this->isParent($permissionId);
    }

    /**
     * Determine if all permissions can be fetched by the user.
     *
     * @param  \App\Models\User  $user
     * @return bool
     */
    public function getMany(User $user)
    {
        $permissionId = $user->permissions->toArray()[0]['permission_id'];

        return $this->isParent($permissionId);
    }
}
