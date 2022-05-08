<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserChorePolicy extends AbstractPolicy
{
    use HandlesAuthorization;

    /**
     * Determine if the given permission can be updated by the user.
     *
     * @param  \App\Models\User  $user
     * @return bool
     */
    public function getOne(User $user)
    {
        $permissionId = $user->permissions->toArray()[0]['permission_id'];

        return $this->isChildOrHigher($permissionId);
    }

    /**
     * Determine if the given permission can be updated by the user.
     *
     * @param  \App\Models\User  $user
     * @return bool
     */
    public function getMany(User $user)
    {
        $permissionId = $user->permissions->toArray()[0]['permission_id'];

        return $this->isChildOrHigher($permissionId);
    }

    /**
     * Determine if the given permission can be updated by the user.
     *
     * @param  \App\Models\User  $user
     * @return bool
     */
    public function requestApproval(User $user)
    {
        $permissionId = $user->permissions->toArray()[0]['permission_id'];

        return $this->isChildOrHigher($permissionId);
    }

    /**
     * Determine if the given permission can be updated by the user.
     *
     * @param  \App\Models\User  $user
     * @return bool
     */
    public function approveWork(User $user)
    {
        $permissionId = $user->permissions->toArray()[0]['permission_id'];

        return $this->isParent($permissionId);
    }

    /**
     * Determine if the given permission can be updated by the user.
     *
     * @param  \App\Models\User  $user
     * @return bool
     */
    public function add(User $user)
    {
        $permissionId = $user->permissions->toArray()[0]['permission_id'];

        return $this->isChildOrHigher($permissionId);
    }

    /**
     * Determine if the given permission can be updated by the user.
     *
     * @param  \App\Models\User  $user
     * @return bool
     */
    public function remove(User $user)
    {
        $permissionId = $user->permissions->toArray()[0]['permission_id'];

        return $this->isChildOrHigher($permissionId);
    }
}
