<?php

namespace Tests\Feature\ChoresTests;

use App\Models\Chore;
use Tests\APITestCase;


class DeleteChoreTest extends APITestCase
{
    protected $chore;

    public function setUp(): void
    {
        parent::setUp();

        $this->chore = Chore::factory()->create();
    }

    /** @test */
    public function admin_user_can_delete_chore()
    {
        $this->initAdminUser();
        $this->canDeleteChore();
    }

    /** @test */
    public function parent_user_can_delete_chore()
    {
        $this->initParentUser();
        $this->canDeleteChore();
    }

    /** @test */
    public function child_user_can_delete_chore()
    {
        $this->initChildUser();
        $this->cannotDeleteChore();
    }

    /** @test */
    public function no_access_user_cannot_delete_chore()
    {
        $this->initNoAccessUser();
        $this->cannotDeleteChore();
    }

    /** @test */
    public function no_access_user_cannot_delete_chore_without_token()
    {
        $this->initNoTokenAccessUser();
        $this->cannotDeleteChoreWithoutToken();
    }

    private function canDeleteChore()
    {
        $response = $this->urlConfig('delete', "chore/{$this->chore->id}");

        $response->assertStatus(200);
        $response->assertJsonPath('name', $this->chore['name']);
    }

    private function cannotDeleteChore()
    {
        $response = $this->urlConfig('delete', "chore/{$this->chore->id}");

        $errorMessage = $response->exception->getMessage();

        $response->assertStatus(403);
        $this->assertEquals('You do not have access to delete this chore', $errorMessage);
    }

    public function cannotDeleteChoreWithoutToken()
    {
        $response = $this->urlConfig('delete', "chore/{$this->chore->id}");

        $errorMessage = $response->exception->getMessage();

        $response->assertStatus(401);
        $this->assertEquals('Unauthenticated.', $errorMessage);
    }
}
