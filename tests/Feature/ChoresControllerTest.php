<?php

namespace Tests\Feature;

use App\Data\Enums\ChoreApprovalStatuses;
use App\Models\Chore;
use Tests\APITestCase;


class ChoresControllerTest extends APITestCase
{
    /** @test */
    // public function user_can_create_chore()
    // {
    //     $this->initTestUser();
    //     $this->canCreateChore();
    // }

    // /** @test */
    // public function user_update_chore_general()
    // {
    //     $input = [
    //         'name' => 'A New Name',
    //         'description' => 'A new Description',
    //         'cost' => 100
    //     ];
    //     $this->initTestUser();
    //     $this->canUpdateChore($input);
    // }

    /** @test */
    public function user_update_chore_approval_request()
    {
        $input = [
            'name' => 'Name for approval request',
            'approval_requested' => true
        ];
        $this->initTestUser();
        $this->canUpdateChoreApprovalRequest($input);
    }


    private function canCreateChore()
    {
        $input = [
            'name' => $this->faker->name,
            'description' => $this->faker->paragraph(),
            'cost' => 0,
            'user_id' => $this->authUser->id,
            'approval_requested' => false,
            'approval_request_date' => null,
            'approval_status' => ChoreApprovalStatuses::$NONE,
            'approval_date' => date('Y-m-d H:i:s', time()),
        ];

        $response = $this->post('/api/chore', $input);

        $response->assertStatus(201);
        $response->assertJsonPath('name', $input['name'])
            ->assertJsonPath('approval_status', $input['approval_status']);
    }

    private function canUpdateChore($input)
    {
        $chore = Chore::factory()->create();
        $response = $this->put('/api/chore', ['id' => $chore->id, ...$input]);

        $this->assertEquals($input['name'], $response['name']);
        $this->assertEquals($input['description'], $response['description']);
        $this->assertEquals($input['cost'], $response['cost']);
    }

    private function canUpdateChoreApprovalRequest($input)
    {
        $chore = Chore::factory()->create();
        $response = $this->put('/api/chore', ['id' => $chore->id, ...$input]);
        $this->echoResponse($response);
        $this->assertEquals($input['name'], $response['name']);
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
