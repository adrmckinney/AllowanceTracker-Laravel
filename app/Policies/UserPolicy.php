<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy extends AbstractPolicy
{
    public function view()
    {
        return true;
    }

    public function admin()
    {
        return false;
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
