<?php

namespace Tests\Feature;

use App\Data\Enums\ChoreApprovalStatuses;
use App\Models\Chore;
use App\Models\Permission;
use App\Models\UsersPermissions;
use Illuminate\Auth\Access\Gate;
use Tests\APITestCase;


class ChoresControllerTest extends APITestCase
{
    protected $chore, $createChoreInput, $input;

    public function setUp(): void
    {
        parent::setUp();

        $this->chore = Chore::factory()->create();

        $this->createChoreInput = [
            'name' => $this->faker->name,
            'description' => $this->faker->paragraph(),
            'cost' => 0,
            'approval_requested' => false,
            'approval_request_date' => null,
            'approval_status' => ChoreApprovalStatuses::$NONE,
            'approval_date' => date('Y-m-d H:i:s', time()),
        ];

        $this->input = [
            'name' => 'A New Name',
            'description' => 'A new Description',
            'cost' => 100
        ];
    }

    // /** @test */
    // public function admin_user_can_create_chore()
    // {
    //     // $this->initTestUser();
    //     $this->initAdminUser();
    //     $this->canCreateChore($this->authUser->id);
    // }

    // /** @test */
    // public function parent_user_can_create_chore()
    // {
    //     // $this->initTestUser();
    //     $this->initParentUser();
    //     $this->canCreateChore($this->authUser->id);
    // }

    // /** @test */
    // public function child_user_cannot_create_chore()
    // {
    //     // $this->initTestUser();
    //     $this->initChildUser();
    //     $this->cannotCreateChore($this->authUser->id);
    // }

    // /** @test */
    // public function admin_user_update_chore_general()
    // {

    //     $this->initAdminUser();
    //     $this->canUpdateChore($this->input);
    // }

    // /** @test */
    // public function parent_user_update_chore_general()
    // {
    //     $this->initParentUser();
    //     $this->canUpdateChore($this->input);
    // }

    // /** @test */
    // public function child_user_cannot_update_chore_general()
    // {
    //     $this->initChildUser();
    //     $this->cannotUpdateChore($this->input);
    // }

    // /** @test */
    // public function no_access_user_cannot_update_chore_general()
    // {
    //     $this->initNoAccessUser();
    //     $this->cannotUpdateChore($this->input);
    // }

    // /** @test */
    // public function admin_user_can_request_chore_approval()
    // {
    //     $this->initAdminUser();
    //     $this->canRequestChoreApproval();
    // }

    // /** @test */
    // public function parent_user_can_request_chore_approval()
    // {
    //     $this->initParentUser();
    //     $this->canRequestChoreApproval();
    // }

    // /** @test */
    // public function child_user_can_request_chore_approval()
    // {
    //     $this->initChildUser();
    //     $this->canRequestChoreApproval();
    // }

    // /** @test */
    // public function no_access_user_cannot_request_chore_approval()
    // {
    //     $this->initNoAccessUser();
    //     $this->cannotRequestChoreApproval();
    // }

    /** @test */
    public function admin_user_can_approve_chore()
    {
        $this->initAdminUser();
        $this->canApproveChore();
    }


    private function canCreateChore($userId)
    {

        $response = $this->post('/api/chore', ['user_id' => $userId, ...$this->createChoreInput]);

        $response->assertStatus(201);
        $response->assertJsonPath('name', $this->createChoreInput['name'])
            ->assertJsonPath('approval_status', $this->createChoreInput['approval_status']);
    }

    private function cannotCreateChore($userId)
    {

        $response = $this->post('/api/chore', ['user_id' => $userId, ...$this->createChoreInput]);

        $errorMessage = $response->exception->getMessage();

        $response->assertStatus(403);
        $this->assertEquals('You do not have access to create a chore', $errorMessage);
    }

    private function canUpdateChore($input)
    {
        $response = $this->put('/api/chore', ['id' => $this->chore->id, ...$input]);

        $this->assertEquals($input['name'], $response['name']);
        $this->assertEquals($input['description'], $response['description']);
        $this->assertEquals($input['cost'], $response['cost']);
    }

    private function cannotUpdateChore($input)
    {
        $response = $this->put('/api/chore', ['id' => $this->chore->id, ...$input]);
        $errorMessage = $response->exception->getMessage();

        $response->assertStatus(403);
        $this->assertEquals('You do not have access to update this chore', $errorMessage);
    }

    private function canRequestChoreApproval()
    {
        $input = ['approval_requested' => true];
        $response = $this->put('/api/chore', ['id' => $this->chore->id, ...$input]);

        $this->assertEquals($this->chore->name, $response['name']);
        $this->assertEquals($input['approval_requested'], $response['approval_requested']);
        $this->assertNotNull($response['approval_request_date']);
        $this->assertEquals(ChoreApprovalStatuses::$PENDING, $response['approval_status']);
    }

    private function cannotRequestChoreApproval()
    {
        $input = ['approval_requested' => true];
        $response = $this->put('/api/chore', ['id' => $this->chore->id, ...$input]);
        $errorMessage = $response->exception->getMessage();

        $response->assertStatus(403);
        $this->assertEquals('You do not have access to update this chore', $errorMessage);
    }

    private function canApproveChore()
    {
        $input = ['approval_requested' => true];
        $response = $this->put('/api/chore', ['id' => $this->chore->id, ...$input]);

        $this->assertEquals($this->chore->name, $response['name']);
        $this->assertEquals($input['approval_requested'], $response['approval_requested']);
        $this->assertNotNull($response['approval_request_date']);
        $this->assertEquals(ChoreApprovalStatuses::$PENDING, $response['approval_status']);
    }

    // public function cannotUpdateUser($target, $old, $new)
    // {
    //     $response = $this->put('/api/user/update', [$target => $new]);
    //     $error = $response['error'];

    //     $response->assertStatus(200);
    //     $this->assertEquals("Only a parent has access to change this", $error);
    // }
}
