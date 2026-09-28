<?php

namespace App\Providers;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RateLimiter::for('login', function ($request) {
            $email = strtolower((string) $request->input('email'));

            return Limit::perMinute(5)->by(
                $request->ip() . '|' . $email
            );
        });

        VerifyEmail::createUrlUsing(function ($notifiable): string {
            $verificationUrl = URL::temporarySignedRoute(
                'verification.verify',
                now()->addMinutes(config('auth.verification.expire', 60)),
                [
                    'id' => $notifiable->getKey(),
                    'hash' => sha1($notifiable->getEmailForVerification()),
                ]
            );

            $query = [];
            parse_str(
                (string) parse_url($verificationUrl, PHP_URL_QUERY),
                $query
            );

            $query['id'] = $notifiable->getKey();

            return rtrim(config('app.web_app_url'), '/')
                . '/verify-email?'
                . http_build_query($query, '', '&', PHP_QUERY_RFC3986);
        });

        ResetPassword::createUrlUsing(function ($notifiable, string $token): string {
            return rtrim(config('app.web_app_url'), '/')
                . '/reset-password?'
                . http_build_query([
                    'token' => $token,
                    'email' => $notifiable->getEmailForPasswordReset(),
                ], '', '&', PHP_QUERY_RFC3986);
        });
    }
}
