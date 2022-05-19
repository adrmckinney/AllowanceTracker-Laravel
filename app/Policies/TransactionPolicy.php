<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class TransactionPolicy extends AbstractPolicy
{
    use HandlesAuthorization;


    /**
     *
     * @param  \App\Models\User  $user
     * @return bool
     */
    public function viewOne(User $user)
    {
        $permissionId = $user->permissions->toArray()[0]['permission_id'];

        return $this->isChildOrHigher($permissionId);
    }

    /**
     *
     * @param  \App\Models\User  $user
     * @return bool
     */
    public function viewMany(User $user)
    {
        $permissionId = $user->permissions->toArray()[0]['permission_id'];

        return $this->isChildOrHigher($permissionId);
    }

    /**
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
     *
     * @param  \App\Models\User  $user
     * @return bool
     */
    public function spend(User $user)
    {
        $permissionId = $user->permissions->toArray()[0]['permission_id'];

        return $this->isChildOrHigher($permissionId);
    }

    /**
     *
     * @param  \App\Models\User $user
     * @return bool
     */
    public function update(User $user)
    {
        $permissionId = $user->permissions->toArray()[0]['permission_id'];

        return $this->isParent($permissionId);
    }

    /**
     *
     * @param  \App\Models\User $user
     * @return bool
     */
    public function getMany(User $user)
    {
        $permissionId = $user->permissions->toArray()[0]['permission_id'];

        return $this->isParent($permissionId);
    }
}
