<?php

namespace App\Policies;

use App\Models\Permission;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PermissionPolicy extends AbstractPolicy
{
    use HandlesAuthorization;


    /**
     * Determine if all permissions can be fetched by the user.
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

    /**
     * Determine if the given permission can be updated by the user.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Permission
     * @return bool
     */
    public function add(User $userPermissionId)
    {
        dump('hello');
        // $permissionId = $user->permissions->permission_id;
        dump('perm id', $userPermissionId);
        // return $user->id === $permission->user_id;
    }
}
