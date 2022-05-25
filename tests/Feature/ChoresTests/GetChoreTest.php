<?php

namespace Tests\Feature\ChoresTests;

use App\Models\Chore;
use Tests\APITestCase;


class GetChoreTest extends APITestCase
{
    protected $chore;

    public function setUp(): void
    {
        parent::setUp();

        $this->chore = Chore::factory()->create();
    }

    /** @test */
    public function admin_user_can_get_chore()
    {
        $this->initAdminUser();
        $this->canGetChore();
    }

    /** @test */
    public function parent_user_can_get_chore()
    {
        $this->initParentUser();
        $this->canGetChore();
    }

    /** @test */
    public function child_user_can_get_chore()
    {
        $this->initChildUser();
        $this->canGetChore();
    }

    /** @test */
    public function no_access_user_cannot_get_chore()
    {
        $this->initNoAccessUser();
        $this->cannotGetChore();
    }

    /** @test */
    public function no_access_user_cannot_get_chore_without_token()
    {
        $this->initNoTokenAccessUser();
        $this->cannotGetChoreWithoutToken();
    }

    private function canGetChore()
    {
        $response = $this->urlConfig('get', 'chore', $this->chore->id);

        $response->assertStatus(200);
        $response->assertJsonPath('name', $this->chore['name']);
    }

    private function cannotGetChore()
    {
        $response = $this->urlConfig('get', 'chore', $this->chore->id);

        $errorMessage = $response->exception->getMessage();

        $response->assertStatus(403);
        $this->assertEquals('You do not have access to get this chore', $errorMessage);
    }

    public function cannotGetChoreWithoutToken()
    {
        $user = $this->authUser;

        $response = $this->urlConfig('get', 'chore', $this->chore->id);

        $errorMessage = $response->exception->getMessage();

        $response->assertStatus(401);
        $this->assertEquals('Unauthenticated.', $errorMessage);
    }
}
