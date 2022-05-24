<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Tests\APITestCase;
use Illuminate\Support\Str;

// examples from https://medium.com/@brice_hartmann/testing-laravel-password-resets-858c58c16b79

class PasswordResetTest extends APITestCase
{
    // /** @test */
    // public function admin_user_can_reset_password()
    // {
    //     $this->initAdminUser();
    //     $this->canResetPassword();
    // }

    // /** @test */
    // public function parent_user_can_reset_password()
    // {
    //     $this->initParentUser();
    //     $this->canResetPassword();
    // }

    // /** @test */
    // public function child_user_can_reset_password()
    // {
    //     $this->initChildUser();
    //     $this->canResetPassword();
    // }

    // /** @test */
    // public function parent_user_cannot_reset_password_with_invalid_email()
    // {
    //     $this->initParentUser();
    //     $this->cannotResetPasswordWithInvalidEmail();
    // }

    /** @test */
    public function parent_user_cannot_reset_password_with_invalid_password()
    {
        $this->initParentUser();
        $this->cannotResetPasswordWithInvalidPassword();
    }

    public function canResetPassword()
    {
        $response = $this->post('/api/password/reset', [
            'id' => $this->authUser->id,
            'email' => $this->authUser->email,
            'password' => 'aNewPassword1',
            'password_confirmation' => 'aNewPassword1',
            'api_token' => $this->authUser->api_token,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('api_token', $this->authUser->api_token);
        $this->assertNotEquals($this->authUser->password, $response->baseResponse->original->password);
    }

    public function cannotResetPasswordWithInvalidEmail()
    {
        $response = $this->post('/api/password/reset', [
            'id' => $this->authUser->id,
            'email' => $this->faker->safeEmail(),
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
            'password' => 'anew',
            'password_confirmation' => 'ane',
            'api_token' => $this->authUser->api_token,
        ]);

        $errorMessage = $response->exception->getMessage();

        $response->assertStatus(403);
        $this->assertEquals('The email you provided is not correct', $errorMessage);
    }

    // /** @test */
    // public function testSubmitPasswordResetRequestInvalidEmail()
    // {
    //     $this
    //         ->followingRedirects()
    //         ->from(route(self::ROUTE_PASSWORD_REQUEST))
    //         ->post(route(self::ROUTE_PASSWORD_EMAIL), [
    //             'email' => Str::random(8),
    //         ])
    //         ->assertSuccessful()
    //         ->assertSee(__('validation.email', [
    //             'attribute' => 'email',
    //         ]));
    // }

    // /** @test */
    // public function testSubmitPasswordResetRequestEmailNotFound()
    // {
    //     $this
    //         ->followingRedirects()
    //         ->from(route(self::ROUTE_PASSWORD_REQUEST))
    //         ->post(route(self::ROUTE_PASSWORD_EMAIL), [
    //             'email' => $this->faker->unique()->safeEmail,
    //         ])
    //         ->assertSuccessful()
    //         ->assertSee(e(__('passwords.user')));
    // }

    // /** @test */
    // public function testSubmitPasswordResetRequest()
    // {
    //     $user = User::factory()->create();

    //     $this
    //         ->followingRedirects()
    //         ->from(route(self::ROUTE_PASSWORD_REQUEST))
    //         ->post(route(self::ROUTE_PASSWORD_EMAIL), [
    //             'email' => $user->email,
    //         ])
    //         ->assertSuccessful()
    //         ->assertSee(__('passwords.sent'));

    //     $this->Notification::assertSentTo($user, ResetPassword::class);
    // }

    // /** @test */
    // public function testSubmitPasswordResetInvalidEmail()
    // {
    //     $user = User::factory()->create([
    //         'password' => bcrypt(self::USER_ORIGINAL_PASSWORD),
    //     ]);

    //     $token = Password::broker()->createToken($user);

    //     $password = Str::random();

    //     $this
    //         ->followingRedirects()
    //         ->from(route(self::ROUTE_PASSWORD_RESET, [
    //             'token' => $token,
    //         ]))
    //         ->post(route(self::ROUTE_PASSWORD_RESET_SUBMIT), [
    //             'token' => $token,
    //             'email' => Str::random(),
    //             'password' => $password,
    //             'password_confirmation' => $password,
    //         ])
    //         ->assertSuccessful()
    //         ->assertSee(__('validation.email', [
    //             'attribute' => 'email',
    //         ]));

    //     $user->refresh();

    //     $this->assertFalse(Hash::check($password, $user->password));

    //     $this->assertTrue(Hash::check(
    //         self::USER_ORIGINAL_PASSWORD,
    //         $user->password
    //     ));
    // }

    // /** @test */
    // public function testSubmitPasswordResetEmailNotFound()
    // {
    //     $user = User::factory()->create([
    //         'password' => bcrypt(self::USER_ORIGINAL_PASSWORD),
    //     ]);

    //     $token = Password::broker()->createToken($user);

    //     $password = Str::random();

    //     $this
    //         ->followingRedirects()
    //         ->from(route(self::ROUTE_PASSWORD_RESET, [
    //             'token' => $token,
    //         ]))
    //         ->post(route(self::ROUTE_PASSWORD_RESET_SUBMIT), [
    //             'token' => $token,
    //             'email' => $this->faker->unique()->safeEmail,
    //             'password' => $password,
    //             'password_confirmation' => $password,
    //         ])
    //         ->assertSuccessful()
    //         ->assertSee(e(__('passwords.user')));

    //     $user->refresh();

    //     $this->assertFalse(Hash::check($password, $user->password));

    //     $this->assertTrue(Hash::check(
    //         self::USER_ORIGINAL_PASSWORD,
    //         $user->password
    //     ));
    // }

    // /** @test */
    // public function testSubmitPasswordResetPasswordMismatch()
    // {
    //     $user = User::factory()->create([
    //         'password' => bcrypt(self::USER_ORIGINAL_PASSWORD),
    //     ]);

    //     $token = Password::broker()->createToken($user);

    //     $password = Str::random();
    //     $password_confirmation = Str::random();

    //     $this
    //         ->followingRedirects()
    //         ->from(route(self::ROUTE_PASSWORD_RESET, [
    //             'token' => $token,
    //         ]))
    //         ->post(route(self::ROUTE_PASSWORD_RESET_SUBMIT), [
    //             'token' => $token,
    //             'email' => $user->email,
    //             'password' => $password,
    //             'password_confirmation' => $password_confirmation,
    //         ])
    //         ->assertSuccessful()
    //         ->assertSee(__('validation.confirmed', [
    //             'attribute' => 'password',
    //         ]));

    //     $user->refresh();

    //     $this->assertFalse(Hash::check($password, $user->password));

    //     $this->assertTrue(Hash::check(
    //         self::USER_ORIGINAL_PASSWORD,
    //         $user->password
    //     ));
    // }

    // /** @test */
    // public function testSubmitPasswordResetPasswordTooShort()
    // {
    //     $user = User::factory()->create([
    //         'password' => bcrypt(self::USER_ORIGINAL_PASSWORD),
    //     ]);

    //     $token = Password::broker()->createToken($user);

    //     $password = Str::random(5);

    //     $this
    //         ->followingRedirects()
    //         ->from(route(self::ROUTE_PASSWORD_RESET, [
    //             'token' => $token,
    //         ]))
    //         ->post(route(self::ROUTE_PASSWORD_RESET_SUBMIT), [
    //             'token' => $token,
    //             'email' => $user->email,
    //             'password' => $password,
    //             'password_confirmation' => $password,
    //         ])
    //         ->assertSuccessful()
    //         ->assertSee(__('validation.min.string', [
    //             'attribute' => 'password',
    //             'min' => 6,
    //         ]));

    //     $user->refresh();

    //     $this->assertFalse(Hash::check($password, $user->password));

    //     $this->assertTrue(Hash::check(
    //         self::USER_ORIGINAL_PASSWORD,
    //         $user->password
    //     ));
    // }

    // /** @test */
    // public function testSubmitPasswordReset()
    // {
    //     $user = User::factory()->create([
    //         'password' => bcrypt(self::USER_ORIGINAL_PASSWORD),
    //     ]);

    //     $token = Password::broker()->createToken($user);

    //     $password = Str::random();

    //     $this
    //         ->followingRedirects()
    //         ->from(route(self::ROUTE_PASSWORD_RESET, [
    //             'token' => $token,
    //         ]))
    //         ->post(route(self::ROUTE_PASSWORD_RESET_SUBMIT), [
    //             'token' => $token,
    //             'email' => $user->email,
    //             'password' => $password,
    //             'password_confirmation' => $password,
    //         ])
    //         ->assertSuccessful()
    //         ->assertSee(__('passwords.reset'));

    //     $user->refresh();

    //     $this->assertFalse(Hash::check(
    //         self::USER_ORIGINAL_PASSWORD,
    //         $user->password
    //     ));

    //     $this->assertTrue(Hash::check($password, $user->password));
    // }
}
