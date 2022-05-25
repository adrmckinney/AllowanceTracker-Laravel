<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy extends AbstractPolicy
{

    use HandlesAuthorization;

    public function view()
    {
        return true;
    }

    /**
     * Determine if user can fetched user data.
     *
     * @param  \App\Models\User
     * @return bool
     */
    public function viewOne(User $user)
    {
        $permissionId = $user->permissions->toArray()[0]['permission_id'];
        return $this->isChildOrHigher($permissionId);
    }

    /**
     * Determine if user can fetched user data.
     *
     * @param  \App\Models\User
     * @return bool
     */
    public function viewAll(User $user)
    {
        $permissionId = $user->permissions->toArray()[0]['permission_id'];
        return $this->isParent($permissionId);
    }

    /**
     * Determine if user can update user data.
     *
     * @param  \App\Models\User
     * @return bool
     */
    public function update(User $user)
    {
        $permissionId = $user->permissions->toArray()[0]['permission_id'];
        return $this->isChildOrHigher($permissionId);
    }

    /**
     * Determine if user can update user wallet.
     *
     * @param  \App\Models\User
     * @return bool
     */
    public function updateWallet(User $user)
    {
        $permissionId = $user->permissions->toArray()[0]['permission_id'];
        return $this->isParent($permissionId);
    }
}
