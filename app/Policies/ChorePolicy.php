<?php

namespace App\Policies;

use App\Data\Enums\PermissionTypes;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ChorePolicy extends AbstractPolicy
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
    public function update(User $user)
    {
        $permissionId = $user->permissions->toArray()[0]['permission_id'];
        // $permissionId = $user->permissions->permission_id;
        dump('perm id', $permissionId);
        dump($permissionId === PermissionTypes::$PARENT);
        return $permissionId === PermissionTypes::$PARENT;
    }
}
