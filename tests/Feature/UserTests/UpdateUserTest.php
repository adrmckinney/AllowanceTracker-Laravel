<?php

namespace Tests\Feature\UserTests;

use App\Models\User;
use Tests\APITestCase;


class UpdateUserTest extends APITestCase
{
    /** @test */
    public function admin_user_can_update_username()
    {
        $this->initAdminUser();
        $oldUserName = $this->authUser->username;
        $newUserName = $this->faker()->userName();
        $this->canUpdateUser('username', $oldUserName, $newUserName);
    }

    /** @test */
    public function parent_user_can_update_username()
    {
        $this->initParentUser();
        $oldUserName = $this->authUser->username;
        $newUserName = $this->faker()->userName();
        $this->canUpdateUser('username', $oldUserName, $newUserName);
    }

    /** @test */
    public function child_user_can_update_username()
    {
        $this->initChildUser();
        $oldUserName = $this->authUser->username;
        $newUserName = $this->faker()->userName();
        $this->canUpdateUser('username', $oldUserName, $newUserName);
    }

    /** @test */
    public function no_access_user_cannot_update_username()
    {
        $this->initNoAccessUser();
        $oldUserName = $this->authUser->username;
        $newUserName = $this->faker()->userName();
        $this->cannotUpdateUser('username', $oldUserName, $newUserName);
    }

    /** @test */
    public function admin_user_can_update_name()
    {
        $this->initAdminUser();
        $oldName = $this->authUser->name;
        $newName = $this->faker()->name();
        $this->canUpdateUser('name', $oldName, $newName);
    }

    /** @test */
    public function parent_user_can_update_name()
    {
        $this->initParentUser();
        $oldName = $this->authUser->name;
        $newName = $this->faker()->name();
        $this->canUpdateUser('name', $oldName, $newName);
    }

    /** @test */
    public function child_user_can_update_name()
    {
        $this->initChildUser();
        $oldName = $this->authUser->name;
        $newName = $this->faker()->name();
        $this->canUpdateUser('name', $oldName, $newName);
    }

    /** @test */
    public function admin_user_can_update_email()
    {
        $this->initAdminUser();
        $oldEmail = $this->authUser->email;
        $newEmail = $this->faker()->email();
        $this->canUpdateUser('email', $oldEmail, $newEmail);
    }

    /** @test */
    public function parent_user_can_update_email()
    {
        $this->initParentUser();
        $oldEmail = $this->authUser->email;
        $newEmail = $this->faker()->email();
        $this->canUpdateUser('email', $oldEmail, $newEmail);
    }

    /** @test */
    public function child_user_can_update_email()
    {
        $this->initChildUser();
        $oldEmail = $this->authUser->email;
        $newEmail = $this->faker()->email();
        $this->canUpdateUser('email', $oldEmail, $newEmail);
    }

    /** @test */
    public function admin_user_can_update_wallet()
    {
        $this->initAdminUser();
        $oldAccountBalance = $this->authUser->wallet;
        $newAccountBalance = 100;
        $this->canUpdateUser('wallet', $oldAccountBalance, $newAccountBalance);
    }

    /** @test */
    public function parent_user_can_update_wallet()
    {
        $this->initParentUser();
        $oldAccountBalance = $this->authUser->wallet;
        $newAccountBalance = 100;
        $this->canUpdateUser('wallet', $oldAccountBalance, $newAccountBalance);
    }

    /** @test */
    public function child_user_cannot_update_wallet()
    {
        $this->initChildUser();
        $oldAccountBalance = $this->authUser->wallet;
        $newAccountBalance = 100;
        $this->cannotUpdateUser('wallet', $oldAccountBalance, $newAccountBalance);
    }

    /** @test */
    public function no_access_user_cannot_update_name_without_token()
    {
        $this->initNoTokenAccessUser();
        $oldName = $this->authUser->name;
        $newName = $this->faker()->name();
        $this->cannotGetUsersWithoutToken('name', $oldName, $newName);
    }

    private function canUpdateUser($target, $old, $new)
    {
        // Change to a spend management
        // admin/parent can spend money for child
        // child can spend money for themselves
        // can not spend more than would take below $0
        // What about undo spend? Does this need parent permission
        // Probably not, a record of what has been spent needs to be kept
        // An undo can just be reversing spend and saved amount is put back in
        // Really seems like I will need an audit for this
        // How do I save the amount spent

        $response = $this->urlConfig('put', 'user/update', [$target => $new]);

        $response->assertStatus(200);
        $response->assertJsonPath($target, $new);
        $this->assertNotEquals($old, $new);
    }

    public function cannotUpdateUser($target, $old, $new)
    {
        $response = $this->urlConfig('put', 'user/update', [$target => $new]);

        $errorMessage = $response->exception->getMessage();

        $response->assertStatus(403);
        $this->assertEquals('You do not have access', $errorMessage);
    }

    public function cannotGetUsersWithoutToken($target, $old, $new)
    {
        $user = $this->authUser;

        $response = $this->urlConfig('put', 'user/update', [$target => $new]);

        $errorMessage = $response->exception->getMessage();

        $response->assertStatus(401);
        $this->assertEquals('Unauthenticated.', $errorMessage);
    }
}
