<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Hash;
use Tests\APITestCase;

class PasswordResetTest extends APITestCase
{
    /** @test */
    public function admin_user_can_reset_password()
    {
        $this->initAdminUser();
        $this->canResetPassword();
    }

    /** @test */
    public function parent_user_can_reset_password()
    {
        $this->initParentUser();
        $this->canResetPassword();
    }

    /** @test */
    public function child_user_can_reset_password()
    {
        $this->initChildUser();
        $this->canResetPassword();
    }

    /** @test */
    public function parent_user_cannot_reset_password_with_bad_old_password()
    {
        $this->initParentUser();
        $this->cannotResetPasswordWithBadOldPassword();
    }

    /** @test */
    public function parent_user_cannot_reset_password_with_invalid_email()
    {
        $this->initParentUser();
        $this->cannotResetPasswordWithInvalidEmail();
    }

    /** @test */
    public function parent_user_cannot_reset_password_with_invalid_password()
    {
        $this->initParentUser();
        $this->cannotResetPasswordWithInvalidPassword();
    }

    /** @test */
    public function parent_user_cannot_reset_password_with_short_password()
    {
        $this->initParentUser();
        $this->cannotResetPasswordWithShortPassword();
    }

    public function canResetPassword()
    {
        $userOldPassword = $this->authUser->password;
        $response = $this->post('/api/password/reset', [
            'id' => $this->authUser->id,
            'email' => $this->authUser->email,
            'oldPassword' => 'password',
            'password' => 'newPassword',
            'password_confirmation' => 'newPassword',
            'api_token' => $this->authUser->api_token,
        ]);

        $this->authUser->refresh();
        $pwdMatches = Hash::check($this->authUser->password, $userOldPassword);

        $response->assertStatus(200)
            ->assertJsonPath('api_token', $this->authUser->api_token);
        $this->assertFalse($pwdMatches);
    }

    public function cannotResetPasswordWithBadOldPassword()
    {
        $response = $this->post('/api/password/reset', [
            'id' => $this->authUser->id,
            'email' => $this->authUser->email,
            'oldPassword' => 'notOriginalPassword',
            'password' => 'newPassword',
            'password_confirmation' => 'newPassword',
            'api_token' => $this->authUser->api_token,
        ]);
        $errorMessage = $response->exception->getMessage();

        $response->assertStatus(403);
        $this->assertEquals('The old password did not match', $errorMessage);
    }

    public function cannotResetPasswordWithInvalidEmail()
    {
        $response = $this->post('/api/password/reset', [
            'id' => $this->authUser->id,
            'email' => $this->faker->safeEmail(),
            'oldPassword' => 'password',
            'password' => 'aNewPassword1',
            'password_confirmation' => 'aNewPassword1',
            'api_token' => $this->authUser->api_token,
        ]);

        $errorMessage = $response->exception->getMessage();

        $response->assertStatus(422);
        $this->assertEquals('The email you provided is not correct', $errorMessage);
    }

    public function cannotResetPasswordWithInvalidPassword()
    {
        $response = $this->post('/api/password/reset', [
            'id' => $this->authUser->id,
            'email' => $this->authUser->email,
            'oldPassword' => 'password',
            'password' => 'anew',
            'password_confirmation' => 'ane',
            'api_token' => $this->authUser->api_token,
        ]);

        $errorMessage = $response->exception->getMessage();

        $response->assertStatus(422);
        $this->assertEquals('You new password does not match your confirmed password', $errorMessage);
    }

    public function cannotResetPasswordWithShortPassword()
    {
        $response = $this->post('/api/password/reset', [
            'id' => $this->authUser->id,
            'email' => $this->authUser->email,
            'oldPassword' => 'password',
            'password' => 'short',
            'password_confirmation' => 'short',
            'api_token' => $this->authUser->api_token,
        ]);

        $decodedResponse = $response->decodeResponseJson();
        $errorMessage = $decodedResponse['message']['password'][0];

        $response->assertStatus(422);
        $this->assertEquals('The password must be at least 8 characters.', $errorMessage);
    }
}
