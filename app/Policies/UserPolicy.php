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

    public function admin()
    {
        return false;
    }

    /**
     * Determine if user can be fetched user data.
     *
     * @param  \App\Models\User  $user
     * @return bool
     */
    public function update(User $user, User $routeUser)
    {
        $permissionId = $user->permissions->toArray()[0]['permission_id'];
        return $this->isParent($permissionId);
    }

    public function viewDashboard(User $user)
    {
        return $user->isPurchaserToAnyOffice() || $user->isOrgPurchaserOrHigher();
    }

    public function userAdmin(User $authUser, $args)
    {
        $userId = get_array_key('id', $args) ?? get_array_key('user_id', $args);
        $user = User::find($userId);

        return $authUser->organization_id == $user->organization->id &&
            ($authUser->id == $userId || $authUser->isAdminToUser($userId));
    }

    public function adminOffices(User $user)
    {
        return $user->isOrganizationAdmin() || $user->isAdminToAnyOffice();
    }
}
