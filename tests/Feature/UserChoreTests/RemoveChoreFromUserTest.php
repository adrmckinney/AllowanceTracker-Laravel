<?php

namespace Tests\Feature\UserChoreTests;

use App\Data\Enums\ChoreApprovalStatuses;
use App\Models\Chore;
use App\Models\UserChore;
use Tests\APITestCase;


class RemoveChoreFromUserTest extends APITestCase
{
    protected $chore, $userChore;

    public function setUp(): void
    {
        parent::setUp();

        $this->chore = Chore::factory()->create();
        // $this->userChore = UserChore::factory()->create([
        //     'user_id' => $this->authUser->id,
        //     'chore_id' => $this->chore->id
        // ]);
    }

    /** @test */
    public function admin_user_can_remove_chore_from_user()
    {
        $this->initAdminUser();
        $this->canRemoveChoreFromUser();
    }

    // /** @test */
    // public function parent_user_can_request_chore_approval()
    // {
    //     $this->initParentUser();
    //     $this->canAssignChoreToUser();
    // }

    // /** @test */
    // public function child_user_can_request_chore_approval()
    // {
    //     $this->initChildUser();
    //     $this->canAssignChoreToUser();
    // }

    // /** @test */
    // public function no_access_user_cannot_request_chore_approval()
    // {
    //     $this->initNoAccessUser();
    //     $this->cannotAssignChoreToUser();
    // }

    // /** @test */
    // public function admin_user_can_approve_chore()
    // {
    //     $this->initAdminUser();
    //     $this->canApproveChore();
    // }


    private function canRemoveChoreFromUser()
    {
        $userChore = UserChore::factory()->create([
            'user_id' => $this->authUser->id,
            'chore_id' => $this->chore->id
        ]);

        // $response = $this->post('/api/user-chore/add', [
        //     'user_id' => $this->authUser->id,
        //     'chore_id' => $this->chore->id
        // ]);

        // $response->assertJsonPath('chore_id', $this->chore->id);
        // $this->assertDatabaseHas('user_chore', [
        //     'user_id' => $this->authUser->id,
        //     'chore_id' => $this->chore->id,
        //     'approval_requested' => false,
        //     'approval_status' => ChoreApprovalStatuses::$PENDING,
        //     'approval_date' => NULL,
        // ]);
    }

    private function cannotAssignChoreToUser()
    {
        $response = $this->post('/api/user-chore/add', [
            'user_id' => $this->authUser->id,
            'chore_id' => $this->chore->id
        ]);
        $errorMessage = $response->exception->getMessage();

        $response->assertStatus(403);
        $this->assertEquals('You do not have access to be added to this chore', $errorMessage);
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
}
