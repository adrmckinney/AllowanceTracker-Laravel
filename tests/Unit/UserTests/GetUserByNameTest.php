<?php

namespace Tests\Feature;

use App\Http\Controllers\UserController;
use App\Models\User;
use Tests\APITestCase;

class GetUserByNameTest extends ApiTestCase
{
    /** @test */
    public function user_can_update_username()
    {
        $user = User::factory()->create();
        $userFromMethod = UserController::getUserByName($user->name)->first();

        $this->assertEquals($user->name, $userFromMethod->name);
    }
}
