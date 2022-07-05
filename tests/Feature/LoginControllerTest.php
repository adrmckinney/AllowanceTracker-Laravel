<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\APITestCase;

class LoginControllerTest extends APITestCase
{
    /** @test */
    public function user_can_login_successfully()
    {
        $user = User::factory()->create();

        $response = $this->urlConfig(
            'post',
            'login',
            [
                'username' => $user->username,
                'password' => 'password'
            ],
            true
        );

        $response->assertStatus(200);
        $this->assertDatabaseHas('users', [
            'name' => $user->name,
            'email' => $user->email,
            'username' => $user->username,
            'api_token' => $user->api_token,
        ]);
    }

    /** @test */
    public function user_login_unsuccessful()
    {
        $user = User::factory()->create();
        $response = $this->urlConfig(
            'post',
            'login',
            [
                'username' => $user->name,
                'password' => 'password'
            ],
            true
        );
        $error = $response['error'];

        $response->assertStatus(200);
        $this->assertEquals("Username or Password is incorrect", $error);
    }
}
