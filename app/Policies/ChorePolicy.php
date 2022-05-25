<?php

namespace App\Policies;

use App\Data\Enums\PermissionTypes;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ChorePolicy extends AbstractPolicy
{
    use HandlesAuthorization;

    /**
     *
     * @param  \App\Models\User
     * @return bool
     */
    public function getOne(User $user)
    {
        $permissionId = $user->permissions->toArray()[0]['permission_id'];

        return $this->isChildOrHigher($permissionId);
    }

    /**
     *
     * @param  \App\Models\User
     * @return bool
     */
    public function getMany(User $user)
    {
        $permissionId = $user->permissions->toArray()[0]['permission_id'];

        return $this->isChildOrHigher($permissionId);
    }

    /**
     *
     * @param  \App\Models\User
     * @return bool
     */
    public function create(User $user)
    {
        $permissionId = $user->permissions->toArray()[0]['permission_id'];

        return $this->isParent($permissionId);
    }

    /**
     *
     * @param  \App\Models\User
     * @return bool
     */
    public function update(User $user)
    {
        $permissionId = $user->permissions->toArray()[0]['permission_id'];

        return $this->isParent($permissionId);
    }
}
