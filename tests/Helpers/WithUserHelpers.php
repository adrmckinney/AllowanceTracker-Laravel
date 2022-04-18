<?php

namespace Tests\Helpers;

use App\Models\User;

/**
 * Trait WithUserHelpers
 * @package Tests\Helpers
 */
trait WithUserHelpers
{
    protected $authUser;

    protected function initTestUser()
    {
        $this->authUser = User::factory()->create();
        $this->post('/api/login', ['username' => $this->authUser->username, 'password' => 'password']);

        return $this->authUser;
    }
}
