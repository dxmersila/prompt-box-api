<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register(): void
    {
        Notification::fake();

        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response
            ->assertStatus(201)
            ->assertJson([
                'message' => 'Please verify your email address.',
            ])
            ->assertJsonStructure([
                'message',
                'user',
            ])
            ->assertJsonPath('user.email_verified', false);

        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
            'role' => 'user',
            'email_verified_at' => null,
        ]);

        Notification::assertSentTo(
            User::where('email', 'test@example.com')->first(),
            VerifyEmail::class
        );
    }

    public function test_email_must_be_unique(): void
    {
        User::factory()->create([
            'email' => 'test@example.com',
        ]);

        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(422);
    }

    public function test_user_can_login(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'user',
                'token',
            ]);
    }

    public function test_unverified_user_cannot_login(): void
    {
        $user = User::factory()->unverified()->create([
            'email' => 'unverified@example.com',
            'password' => 'password123',
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'unverified@example.com',
            'password' => 'password123',
        ]);

        $response
            ->assertStatus(401)
            ->assertJson([
                'message' => 'Please verify your email address before logging in.',
                'code' => 'email_not_verified',
            ]);

        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    public function test_invalid_credentials_return_401(): void
    {
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'test@example.com',
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(401);
    }

    public function test_password_reset_notification_uses_web_app_url(): void
    {
        Notification::fake();

        $user = User::factory()->create([
            'email' => 'reset@example.com',
        ]);

        $this->postJson('/api/v1/auth/password/forgot', [
            'email' => 'reset@example.com',
        ])->assertStatus(200);

        Notification::assertSentTo(
            $user,
            ResetPassword::class,
            function (ResetPassword $notification) use ($user): bool {
                $mail = $notification->toMail($user);
                $url = $mail->actionUrl;

                $parts = parse_url($url);

                parse_str($parts['query'] ?? '', $query);

                return ($parts['path'] ?? '') === '/reset-password'
                    && ($query['email'] ?? null) === $user->email
                    && isset($query['token']);
            }
        );
    }

    public function test_email_verification_notification_uses_web_app_url(): void
    {
        Notification::fake();

        $user = User::factory()->unverified()->create([
            'email' => 'verify-url@example.com',
        ]);

        $this->postJson('/api/v1/auth/email/verification-notification', [
            'email' => $user->email,
        ])->assertStatus(200);

        Notification::assertSentTo(
            $user,
            VerifyEmail::class,
            function (VerifyEmail $notification) use ($user): bool {
                $mail = $notification->toMail($user);
                $url = $mail->actionUrl;

                $parts = parse_url($url);

                parse_str($parts['query'] ?? '', $query);

                parse_str(
                    ltrim($parts['fragment'] ?? '', '#'),
                    $fragment
                );

                return ($parts['path'] ?? '') === '/verify-email'
                    && ($fragment['id'] ?? null) === (string) $user->id
                    && ($query['hash'] ?? null) === sha1($user->email)
                    && isset($query['expires'])
                    && isset($query['signature']);
            }
        );
    }

    public function test_user_can_request_password_reset(): void
    {
        Notification::fake();

        $user = User::factory()->create([
            'email' => 'reset@example.com',
        ]);

        $response = $this->postJson('/api/v1/auth/password/forgot', [
            'email' => 'reset@example.com',
        ]);

        $response
            ->assertStatus(200)
            ->assertJson([
                'message' => 'If an account exists for this email address, a password reset link has been sent.',
            ]);

        Notification::assertSentTo($user, ResetPassword::class);
    }

    public function test_password_reset_request_uses_generic_response_for_unknown_email(): void
    {
        Notification::fake();

        $response = $this->postJson('/api/v1/auth/password/forgot', [
            'email' => 'unknown@example.com',
        ]);

        $response
            ->assertStatus(200)
            ->assertJson([
                'message' => 'If an account exists for this email address, a password reset link has been sent.',
            ]);

        Notification::assertNothingSent();
    }

    public function test_user_can_reset_password(): void
    {
        $user = User::factory()->create([
            'email' => 'reset@example.com',
            'password' => 'old-password',
        ]);

        $token = app('auth.password.broker')->createToken($user);

        $oldToken = $user->createToken('old-token')->plainTextToken;

        $response = $this->postJson('/api/v1/auth/password/reset', [
            'token' => $token,
            'email' => 'reset@example.com',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ]);

        $response
            ->assertStatus(200)
            ->assertJson([
                'message' => 'Password reset successfully.',
            ]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
        ]);

        $this->assertDatabaseCount('personal_access_tokens', 0);

        $login = $this->postJson('/api/v1/auth/login', [
            'email' => 'reset@example.com',
            'password' => 'new-password',
        ]);

        $login
            ->assertStatus(200)
            ->assertJsonStructure([
                'user',
                'token',
            ]);

        $this->withHeader('Authorization', 'Bearer '.$oldToken)
            ->getJson('/api/v1/auth/me')
            ->assertStatus(401);
    }

    public function test_invalid_password_reset_token_is_rejected(): void
    {
        User::factory()->create([
            'email' => 'reset@example.com',
        ]);

        $response = $this->postJson('/api/v1/auth/password/reset', [
            'token' => 'invalid-token',
            'email' => 'reset@example.com',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ]);

        $response
            ->assertStatus(422)
            ->assertJson([
                'message' => 'The password reset token is invalid or has expired.',
                'code' => 'invalid_password_reset_token',
            ]);
    }

    public function test_password_reset_requires_password_confirmation(): void
    {
        $user = User::factory()->create([
            'email' => 'reset@example.com',
        ]);

        $token = app('auth.password.broker')->createToken($user);

        $response = $this->postJson('/api/v1/auth/password/reset', [
            'token' => $token,
            'email' => 'reset@example.com',
            'password' => 'new-password',
            'password_confirmation' => 'different-password',
        ]);

        $response->assertStatus(422);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
        ]);
    }

    public function test_user_can_verify_email_with_signed_url(): void
    {
        $user = User::factory()->unverified()->create([
            'email' => 'verify@example.com',
        ]);

        $url = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            [
                'id' => $user->id,
                'hash' => sha1($user->email),
            ]
        );

        $response = $this->getJson($url);

        $response
            ->assertStatus(200)
            ->assertJson([
                'message' => 'Email address verified successfully.',
            ]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
        ]);

        $this->assertNotNull($user->fresh()->email_verified_at);
    }

    public function test_invalid_email_verification_link_is_rejected(): void
    {
        $user = User::factory()->unverified()->create();

        $response = $this->getJson(
            route('verification.verify', [
                'id' => $user->id,
                'hash' => 'invalid-hash',
                'expires' => now()->addMinutes(60)->timestamp,
                'signature' => 'invalid-signature',
            ])
        );

        $response->assertStatus(403);

        $this->assertNull($user->fresh()->email_verified_at);
    }

    public function test_user_can_resend_verification_email(): void
    {
        Notification::fake();

        $user = User::factory()->unverified()->create([
            'email' => 'resend@example.com',
        ]);

        $response = $this->postJson('/api/v1/auth/email/verification-notification', [
            'email' => 'resend@example.com',
        ]);

        $response
            ->assertStatus(200)
            ->assertJson([
                'message' => 'If an unverified account exists for this email address, a verification email has been sent.',
            ]);

        Notification::assertSentTo($user, VerifyEmail::class);
    }

    public function test_resend_verification_uses_generic_response_for_unknown_email(): void
    {
        Notification::fake();

        $response = $this->postJson('/api/v1/auth/email/verification-notification', [
            'email' => 'unknown@example.com',
        ]);

        $response
            ->assertStatus(200)
            ->assertJson([
                'message' => 'If an unverified account exists for this email address, a verification email has been sent.',
            ]);

        Notification::assertNothingSent();
    }

    public function test_authenticated_user_can_view_profile(): void
    {
        $user = User::factory()->create();

        $token = $user->createToken('auth-token')->plainTextToken;

        $response = $this
            ->withToken($token)
            ->getJson('/api/v1/auth/me');

        $response
            ->assertStatus(200)
            ->assertJsonFragment([
                'email' => $user->email,
            ]);
    }

    public function test_guest_cannot_view_profile(): void
    {
        $response = $this->getJson('/api/v1/auth/me');

        $response->assertStatus(401);
    }

    public function test_authenticated_user_can_logout(): void
    {
        $user = User::factory()->create();

        $token = $user->createToken('auth-token')->plainTextToken;

        $response = $this
            ->withToken($token)
            ->postJson('/api/v1/auth/logout');

        $response
            ->assertStatus(200)
            ->assertJson([
                'message' => 'Logged out successfully',
            ]);
    }

    public function test_login_is_rate_limited(): void
    {
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        for ($attempt = 1; $attempt <= 5; $attempt++) {
            $this->postJson('/api/v1/auth/login', [
                'email' => 'test@example.com',
                'password' => 'wrong-password',
            ])->assertStatus(401);
        }

        $this->postJson('/api/v1/auth/login', [
            'email' => 'test@example.com',
            'password' => 'wrong-password',
        ])->assertStatus(429);
    }
}
