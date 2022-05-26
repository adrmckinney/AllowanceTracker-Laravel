<?php

namespace Tests\Feature\ChoresTests;

use App\Models\Chore;
use Tests\APITestCase;


class CreateChoresTest extends APITestCase
{
    protected $createChoreInput;

    public function setUp(): void
    {
        parent::setUp();

        $this->createChoreInput = [
            'name' => $this->faker->name,
            'description' => $this->faker->paragraph(),
            'cost' => 0,
        ];
    }

    /** @test */
    public function admin_user_can_create_chore()
    {
        $this->initAdminUser();
        $this->canCreateChore();
    }

    /** @test */
    public function parent_user_can_create_chore()
    {
        $this->initParentUser();
        $this->canCreateChore();
    }

    /** @test */
    public function child_user_cannot_create_chore()
    {
        $this->initChildUser();
        $this->cannotCreateChore();
    }

    /** @test */
    public function admin_user_cannot_create_duplicate_chore()
    {
        $this->initAdminUser();
        $this->cannotCreateDuplicateChore();
    }

    private function canCreateChore()
    {
        $response = $this->urlConfig('post', 'chore', $this->createChoreInput);

        $response->assertStatus(201);
        $response->assertJsonPath('name', $this->createChoreInput['name']);
    }

    private function cannotCreateChore()
    {
        $response = $this->urlConfig('post', 'chore', $this->createChoreInput);

        $errorMessage = $response->exception->getMessage();

        $response->assertStatus(403);
        $this->assertEquals('You do not have access to create a chore', $errorMessage);
    }

    private function cannotCreateDuplicateChore()
    {
        $chore = Chore::factory()->create();

        $response = $this->urlConfig('post', 'chore', [...$this->createChoreInput, 'name' => $chore->name]);

        $errorMessage = $response->exception->getMessage();

        $response->assertStatus(406);
        $this->assertEquals('A chore with this name already exists.', $errorMessage);
    }
}
