<?php

namespace App\Policies;

use App\Models\Permission;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PermissionPolicy extends AbstractPolicy
{
    use HandlesAuthorization;

    /**
     * Create a new policy instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
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
