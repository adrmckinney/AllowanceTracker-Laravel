<?php

namespace Tests\Feature\ChoresTests;

use App\Models\Chore;
use Tests\APITestCase;


class UpdateChoresTest extends APITestCase
{
    protected $chore, $input;

    public function setUp(): void
    {
        parent::setUp();

        $this->chore = Chore::factory()->create();

        $this->input = [
            'name' => 'A New Name',
            'description' => 'A new Description',
            'cost' => 100
        ];
    }

    /** @test */
    public function admin_user_can_update_chore()
    {
        $this->initAdminUser();
        $this->canUpdateChore();
    }

    /** @test */
    public function parent_user_can_update_chore()
    {
        $this->initParentUser();
        $this->canUpdateChore();
    }

    /** @test */
    public function child_user_cannot_update_chore()
    {
        $this->initChildUser();
        $this->cannotUpdateChore();
    }

    /** @test */
    public function no_access_user_cannot_update_chore()
    {
        $this->initNoAccessUser();
        $this->cannotUpdateChore();
    }

    /** @test */
    public function admin_user_cannot_update_chore_with_duplicate_name()
    {
        $this->initAdminUser();
        $this->cannotUpdateAsDuplicateChore();
    }


    private function canUpdateChore()
    {
        $response = $this->urlConfig('put', 'chore', [...$this->input, 'id' => $this->chore->id]);

        $this->assertEquals($this->input['name'], $response['name']);
        $this->assertEquals($this->input['description'], $response['description']);
        $this->assertEquals($this->input['cost'], $response['cost']);
    }

    private function cannotUpdateChore()
    {
        $response = $this->urlConfig('put', 'chore', [...$this->input, 'id' => $this->chore->id]);
        $errorMessage = $response->exception->getMessage();

        $response->assertStatus(403);
        $this->assertEquals('You do not have access to update this chore', $errorMessage);
    }

    private function cannotUpdateAsDuplicateChore()
    {
        $chore2 = Chore::factory()->create();

        $response = $this->urlConfig('put', 'chore', [
            ...$this->input,
            'id' => $this->chore->id,
            'name' => $chore2->name
        ]);
        $errorMessage = $response->exception->getMessage();

        $response->assertStatus(406);
        $this->assertEquals('A chore with this name already exists.', $errorMessage);
    }
}
