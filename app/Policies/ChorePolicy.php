<?php

namespace App\Policies;

use App\Data\Enums\PermissionTypes;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ChorePolicy extends AbstractPolicy
{
    use HandlesAuthorization;

    /**
     * Determine if the given permission can be updated by the user.
     *
     * @param  \App\Models\User  $user
     * @return bool
     */
    public function create(User $user)
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
    public function update(User $user)
    {
        $permissionId = $user->permissions->toArray()[0]['permission_id'];

        return $this->isParent($permissionId);
    }
}
