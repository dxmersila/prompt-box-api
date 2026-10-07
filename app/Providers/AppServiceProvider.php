<?php

namespace App\Providers;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;
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

        RateLimiter::for('analytics-events', function ($request) {
            return Limit::perMinute(60)->by($request->ip());
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

            return rtrim(config('app.web_app_url'), '/')
                . '/verify-email?'
                . http_build_query($query, '', '&', PHP_QUERY_RFC3986)
                . '#id='
                . rawurlencode((string) $notifiable->getKey());
        });

        VerifyEmail::toMailUsing(function ($notifiable, string $url): MailMessage {
            return (new MailMessage)
                ->subject('Verify your email address / تأیید آدرس ایمیل')
                ->line('Hello! / سلام!')
                ->line('Please click the button below to verify your email address. / برای تأیید آدرس ایمیل خود، روی دکمه زیر کلیک کنید.')
                ->action('Verify Email Address / تأیید آدرس ایمیل', $url)
                ->line('If you did not create an account, no further action is required. / اگر شما این حساب کاربری را ایجاد نکرده‌اید، نیازی به انجام کاری نیست.')
                ->line('If you do not see this email in your Inbox, please check your Spam, Junk, or Promotions folder. / اگر این ایمیل را در Inbox نمی‌بینید، پوشه‌های Spam، Junk یا Promotions را هم بررسی کنید.');
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
