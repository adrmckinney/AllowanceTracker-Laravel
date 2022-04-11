<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\APITestCase;

class RegisterControllerTest extends APITestCase
{
    /** @test */
    public function register_creates_and_authenticates_a_user()
    {
        $name = $this->faker->name();
        $username = $this->faker->userName();
        $email = $this->faker->safeEmail();
        $password = $this->faker->password(8);

        $response = $this->post('/api/register', [
            'name' => $name,
            'email' => $email,
            'username' => $username,
            'api_token' => 'zOTsFDQa5YtRiMQi5Guf1m3p7hacGMqxiWi7Hj2ifY7Z73J8ot5CQKTux4EQ',
            'password' => $password,
            'password_confirmation' => $password,
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('users', [
            'name' => $name,
            'email' => $email,
            'username' => $username
        ]);
        $user = User::where('email', $email)->where('name', $name)->first();
        $this->assertNotNull($user);

        $this->assertAuthenticatedAs($user);
    }
}
