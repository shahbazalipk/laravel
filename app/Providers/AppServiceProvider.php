<?php

namespace App\Providers;

use App\Models\Exhibitor;
use App\Models\Group;
use App\Models\Registration;
use App\Registration\Models\RegistrationDraft;
use App\Sales\Models\Deal;
use App\Sales\Models\InquiryForm;
use App\Sales\Models\InquirySubmission;
use App\Sales\Models\Pipeline;
use App\Sales\Models\PipelineType;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Relations\Relation;
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

        // Stable morph aliases for custom form respondents (and shared domain models).
        Relation::morphMap([
            'registration_draft' => RegistrationDraft::class,
            'registration' => Registration::class,
            'exhibitor' => Exhibitor::class,
            'group' => Group::class,
            'sales_pipeline_type' => PipelineType::class,
            'sales_pipeline' => Pipeline::class,
            'sales_deal' => Deal::class,
            'sales_inquiry_form' => InquiryForm::class,
            'sales_inquiry_submission' => InquirySubmission::class,
            'pipeline_type' => PipelineType::class,
            'pipeline' => Pipeline::class,
            'deal' => Deal::class,
            'inquiry_form' => InquiryForm::class,
            'inquiry_submission' => InquirySubmission::class,
        ]);
        
        // Configure rate limiting for email sending
        RateLimiter::for('email-sending', function ($job) {
            return Limit::perMinute(100)->by('email-sending');
        });

        RateLimiter::for('registration-draft', function ($request) {
            return Limit::perMinute(20)->by($request->ip());
        });

        RateLimiter::for('registration-otp-send', function ($request) {
            return Limit::perMinute(5)->by(strtolower((string) $request->cookie('registration_resume_token')).'|'.$request->ip());
        });

        RateLimiter::for('registration-otp-verify', function ($request) {
            return Limit::perMinute(10)->by(strtolower((string) $request->cookie('registration_resume_token')).'|'.$request->ip());
        });

        RateLimiter::for('registration-complete', function ($request) {
            return Limit::perMinute(10)->by($request->ip());
        });

        RateLimiter::for('registration-resume', function ($request) {
            return Limit::perMinute(30)->by($request->ip());
        });
    }
}
