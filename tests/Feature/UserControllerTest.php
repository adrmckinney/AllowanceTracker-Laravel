<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\APITestCase;


class UserControllerTest extends APITestCase
{
    /** @test */
    public function user_can_update_username()
    {
        $this->initTestUser();
        $oldUserName = $this->authUser->username;
        $newUserName = $this->faker()->userName();
        $this->canUpdateUser('username', $oldUserName, $newUserName);
    }

    /** @test */
    public function user_can_update_name()
    {
        $this->initTestUser();
        $oldName = $this->authUser->name;
        $newName = $this->faker()->name();
        $this->canUpdateUser('name', $oldName, $newName);
    }

    /** @test */
    public function user_can_update_email()
    {
        $this->initTestUser();
        $oldEmail = $this->authUser->email;
        $newEmail = $this->faker()->email();
        $this->canUpdateUser('email', $oldEmail, $newEmail);
    }

    /** @test */
    public function user_can_update_wallet()
    {
        $this->initTestUser();
        $oldAccountBalance = $this->authUser->wallet;
        $newAccountBalance = 100;
        $this->canUpdateUser('wallet', $oldAccountBalance, $newAccountBalance);
    }

    /** @test */
    public function can_get_user_by_id()
    {
        $this->initTestUser();
        $this->canGetUser();
    }

    /** @test */
    public function can_get_all_users()
    {
        $this->initTestUser();
        $this->canGetAllUsers();
    }

    private function canUpdateUser($target, $old, $new)
    {
        $response = $this->put('/api/user/update', [$target => $new]);

        $response->assertStatus(200);
        $response->assertJsonPath($target, $new);
        $this->assertNotEquals($old, $new);
    }


    public function cannotUpdateUser($target, $old, $new)
    {
        $response = $this->put('/api/user/update', [$target => $new]);
        $error = $response['error'];

        $response->assertStatus(200);
        $this->assertEquals("Only a parent has access to change this", $error);
    }

    private function canGetUser()
    {
        $user = $this->authUser;
        $response = $this->get("/api/user/{$user->id}");

        $response->assertStatus(200);
        $response->assertJsonPath('name', $user->name);
        $response->assertJsonPath('api_token', $user->api_token);
    }

    private function canGetAllUsers()
    {
        User::factory()->count(3)->create();
        $users = User::all();

        $userNames = collect($users)->map(function ($user) {
            return $user->name;
        });

        $response = $this->get("/api/users");
        $responseUserNames = $response->baseResponse->original->map(function ($user) {
            return $user->name;
        });

        $response->assertStatus(200);
        $this->assertEquals($userNames, $responseUserNames);
    }
}
