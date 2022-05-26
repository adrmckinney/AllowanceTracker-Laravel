<?php

namespace Tests\Feature\UserChoreTests;

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
    }

    /** @test */
    public function admin_user_can_remove_chore_from_user()
    {
        $this->initAdminUser();
        $this->canRemoveChoreFromUser();
    }

    /** @test */
    public function parent_user_can_remove_chore_from_user()
    {
        $this->initParentUser();
        $this->canRemoveChoreFromUser();
    }

    /** @test */
    public function child_user_can_remove_chore_from_user()
    {
        $this->initChildUser();
        $this->canRemoveChoreFromUser();
    }

    /** @test */
    public function no_access_user_cannot_remove_chore_from_user()
    {
        $this->initNoAccessUser();
        $this->cannotRemoveChoreFromUser();
    }

    /** @test */
    public function no_access_cannot_remove_user_chore_without_token()
    {
        $this->initNoTokenAccessUser();
        $this->cannotDeleteUserChoreWithoutToken();
    }

    private function canRemoveChoreFromUser()
    {
        $userChore = $this->createUserChore();

        $response = $this->urlConfig('delete', "user-chore/{$userChore->id}/remove");

        $response->assertJsonPath('id', $userChore->id);
        $response->assertJsonPath('chore_id', $userChore->chore_id);
        $this->assertDatabaseMissing('user_chore', ['id' => $userChore->id]);
    }

    private function cannotRemoveChoreFromUser()
    {
        $userChore = UserChore::factory()->create([
            'user_id' => $this->authUser->id,
            'chore_id' => $this->chore->id
        ]);

        $response = $this->urlConfig('delete', "user-chore/{$userChore->id}/remove");
        $errorMessage = $response->exception->getMessage();

        $response->assertStatus(403);
        $this->assertEquals('You do not have access to remove to this chore', $errorMessage);
    }

    public function cannotDeleteUserChoreWithoutToken()
    {
        $userChore = UserChore::factory()->create([
            'user_id' => $this->authUser->id,
            'chore_id' => $this->chore->id
        ]);

        $response = $this->urlConfig('delete', "user-chore/{$userChore->id}/remove");

        $errorMessage = $response->exception->getMessage();

        $response->assertStatus(401);
        $this->assertEquals('Unauthenticated.', $errorMessage);
    }
}
