<?php

namespace Tests\Feature\UserChoreTests;

use App\Data\Enums\UserChoreApprovalStatuses;
use App\Models\Chore;
use Tests\APITestCase;


class AddChoreToUserTest extends APITestCase
{
    protected $chore;

    public function setUp(): void
    {
        parent::setUp();

        $this->chore = Chore::factory()->create();
    }

    /** @test */
    public function admin_user_can_assign_chore_to_user()
    {
        $this->initAdminUser();
        $this->canAssignChoreToUser();
    }

    /** @test */
    public function parent_user_can_assign_chore_to_user()
    {
        $this->initParentUser();
        $this->canAssignChoreToUser();
    }

    /** @test */
    public function child_user_can_assign_chore_to_user()
    {
        $this->initChildUser();
        $this->canAssignChoreToUser();
    }

    /** @test */
    public function no_access_user_cannot_assign_chore_to_user()
    {
        $this->initNoAccessUser();
        $this->cannotAssignChoreToUser();
    }

    /** @test */
    public function no_access_cannot_add_chore_to_user_without_token()
    {
        $this->initNoTokenAccessUser();
        $this->cannotAddChoreToUserWithoutToken();
    }

    private function canAssignChoreToUser()
    {
        $response = $this->urlConfig('post', "user/{$this->authUser->id}/chore/{$this->chore->id}/add");

        $response->assertJsonPath('chore_id', (string) $this->chore->id);
        $this->assertDatabaseHas('user_chore', [
            'user_id' => $this->authUser->id,
            'chore_id' => $this->chore->id,
            'approval_requested' => false,
            'approval_request_date' => NULL,
            'approval_status' => UserChoreApprovalStatuses::$NONE,
            'approval_date' => NULL,
        ]);
    }

    private function cannotAssignChoreToUser()
    {
        $response = $this->urlConfig('post', "user/{$this->authUser->id}/chore/{$this->chore->id}/add");
        $errorMessage = $response->exception->getMessage();

        $response->assertStatus(403);
        $this->assertEquals('You do not have access to be added to this chore', $errorMessage);
    }

    public function cannotAddChoreToUserWithoutToken()
    {
        $response = $this->urlConfig('post', "user/{$this->authUser->id}/chore/{$this->chore->id}/add");

        $errorMessage = $response->exception->getMessage();

        $response->assertStatus(401);
        $this->assertEquals('Unauthenticated.', $errorMessage);
    }
}
