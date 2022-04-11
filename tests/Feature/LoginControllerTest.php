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
        $response = $this->post('/api/login', ['username' => $user->username, 'password' => 'password']);

        $response->assertStatus(200);
        $response->assertJsonPath('username', $user->username);
    }

    /** @test */
    public function user_login_unsuccessful()
    {
        $user = User::factory()->create();
        $response = $this->post('/api/login', ['username' => $user->email, 'password' => 'password']);
        $error = $response['error'];

        $response->assertStatus(200);
        $this->assertEquals("Username or Password is incorrect", $error);
    }
}
