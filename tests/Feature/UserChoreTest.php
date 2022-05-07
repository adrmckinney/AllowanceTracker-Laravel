<?php

namespace Tests\Feature;

use App\Data\Enums\ChoreApprovalStatuses;
use App\Models\Chore;
use Tests\APITestCase;


class UserChoreTest extends APITestCase
{
    protected $chore;

    public function setUp(): void
    {
        parent::setUp();

        $this->chore = Chore::factory()->create();
    }

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

    // /** @test */
    // public function admin_user_can_approve_chore()
    // {
    //     $this->initAdminUser();
    //     $this->canApproveChore();
    // }

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
