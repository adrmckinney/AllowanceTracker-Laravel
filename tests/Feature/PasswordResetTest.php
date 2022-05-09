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
    const ROUTE_PASSWORD_EMAIL = 'password.email';
    const ROUTE_PASSWORD_REQUEST = 'password.request';
    const ROUTE_PASSWORD_RESET = 'password.reset';
    const ROUTE_PASSWORD_RESET_SUBMIT = 'password.reset.submit';

    const USER_ORIGINAL_PASSWORD = 'secret';

    /** @test */
    public function admin_user_can_reset_password()
    {
        $this->initAdminUser();
        $this->canResetPassword();
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

        // $this->echoResponse($response);
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
