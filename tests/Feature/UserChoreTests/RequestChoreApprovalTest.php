<?php

namespace Tests\Feature\UserChoreTests;

use App\Data\Enums\UserChoreApprovalStatuses;
use App\Models\Chore;
use Tests\APITestCase;


class RequestChoreApprovalTest extends APITestCase
{
    protected $chore;

    public function setUp(): void
    {
        parent::setUp();

        $this->chore = Chore::factory()->create();
    }

    /** @test */
    public function admin_user_can_request_approval()
    {
        $this->initAdminUser();
        $this->canRequestApproval();
    }

    /** @test */
    public function parent_user_can_request_approval()
    {
        $this->initParentUser();
        $this->canRequestApproval();
    }

    /** @test */
    public function child_user_can_request_approval()
    {
        $this->initChildUser();
        $this->canRequestApproval();
    }

    /** @test */
    public function no_access_user_cannot_request_approval()
    {
        $this->initNoAccessUser();
        $this->cannotRequestApproval();
    }

    private function canRequestApproval()
    {
        $userChore = $this->createUserChore();

        $response = $this->put('/api/user-chore/request-approval', [
            'id' => $userChore->id,
            'approval_requested' => true
        ]);

        $response->assertJsonPath('chore_id', $this->chore->id)
            ->assertJsonPath('approval_requested', true)
            ->assertJsonPath('approval_status', UserChoreApprovalStatuses::$PENDING);
        $this->assertNotNull($response['approval_request_date']);
    }

    private function cannotRequestApproval()
    {
        $userChore = $this->createUserChore();
        $response = $this->put('/api/user-chore/request-approval', [
            'id' => $userChore->id,
            'approval_request' => true
        ]);
        $errorMessage = $response->exception->getMessage();

        $response->assertStatus(403);
        $this->assertEquals('You do not have access to request approval', $errorMessage);
    }

    private function canApproveChore()
    {
        $input = ['approval_requested' => true];
        $response = $this->put('/api/chore', ['id' => $this->chore->id, ...$input]);

        $this->assertEquals($this->chore->name, $response['name']);
        $this->assertEquals($input['approval_requested'], $response['approval_requested']);
        $this->assertNotNull($response['approval_request_date']);
        $this->assertEquals(UserChoreApprovalStatuses::$PENDING, $response['approval_status']);
    }
}
