<?php

namespace Tests\Feature\ChoresTests;

use App\Models\Chore;
use Tests\APITestCase;


class GetChoreListTest extends APITestCase
{
    protected $chores;

    public function setUp(): void
    {
        parent::setUp();

        $this->chores = Chore::factory()->count(3)->create();
    }

    /** @test */
    public function admin_user_can_get_chores()
    {
        $this->initAdminUser();
        $this->canGetChores();
    }

    /** @test */
    public function parent_user_can_get_chores()
    {
        $this->initParentUser();
        $this->canGetChores();
    }

    /** @test */
    public function child_user_can_get_chores()
    {
        $this->initChildUser();
        $this->canGetChores();
    }

    /** @test */
    public function no_access_user_cannot_get_chores()
    {
        $this->initNoAccessUser();
        $this->cannotGetChores();
    }

    /** @test */
    public function no_access_user_cannot_get_chores_without_token()
    {
        $this->initNoTokenAccessUser();
        $this->cannotGetChoresWithoutToken();
    }

    private function canGetChores()
    {
        $choreNames = $this->chores->map(function ($chore) {
            return $chore->name;
        });

        $response = $this->urlConfig('get', 'chores');

        $responseNames = $response->baseResponse->original->map(function ($chore) {
            return $chore->name;
        });

        $response->assertStatus(200)
            ->assertJsonCount(3);
        $this->assertEquals($choreNames, $responseNames);
    }

    private function cannotGetChores()
    {
        $response = $this->urlConfig('get', 'chores');

        $errorMessage = $response->exception->getMessage();

        $response->assertStatus(403);
        $this->assertEquals('You do not have access to get chores', $errorMessage);
    }

    public function cannotGetChoresWithoutToken()
    {
        $user = $this->authUser;

        $response = $this->urlConfig('get', 'chores');

        $errorMessage = $response->exception->getMessage();

        $response->assertStatus(401);
        $this->assertEquals('Unauthenticated.', $errorMessage);
    }
}
