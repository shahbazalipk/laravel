<?php

namespace App\Providers;

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
        // Register email provider manager as singleton
        $this->app->singleton(\App\Services\ProviderManager::class, function ($app) {
            $manager = new \App\Services\ProviderManager();
            
            // Register available providers
            $manager->registerProvider('smtp', \App\Services\EmailProviders\SmtpProvider::class);
            $manager->registerProvider('infobip', \App\Services\EmailProviders\InfobipProvider::class);
            $manager->registerProvider('mailchimp', \App\Services\EmailProviders\MailchimpProvider::class);
            
            return $manager;
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Force HTTPS in production
        if ($this->app->environment('production')) {
            \URL::forceScheme('https');
        }
        
        // Configure rate limiting for email sending
        RateLimiter::for('email-sending', function ($job) {
            return Limit::perMinute(100)->by('email-sending');
        });
    }
}
