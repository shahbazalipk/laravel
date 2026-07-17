<?php

namespace App\Registration\Services;

use App\Models\Event;
use App\Registration\Models\RegistrationDraft;
use App\Services\ProviderManager;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;
use InvalidArgumentException;

class RegistrationOtpService
{
    public const OTP_TTL_MINUTES = 15;

    public const RESEND_COOLDOWN_SECONDS = 60;

    public const MAX_ATTEMPTS = 5;

    public function __construct(
        private ProviderManager $providerManager
    ) {}

    public function issueAndSend(RegistrationDraft $draft, Event $event, string $resumeUrl): string
    {
        if ($draft->otp_last_sent_at && $draft->otp_last_sent_at->gt(now()->subSeconds(self::RESEND_COOLDOWN_SECONDS))) {
            throw new InvalidArgumentException('Please wait before requesting another verification code.');
        }

        $otp = (string) random_int(100000, 999999);

        $draft->forceFill([
            'otp_hash' => Hash::make($otp),
            'otp_expires_at' => now()->addMinutes(self::OTP_TTL_MINUTES),
            'otp_attempts' => 0,
            'otp_last_sent_at' => now(),
        ])->save();

        $this->sendMail($draft, $event, $otp, $resumeUrl);

        return $otp;
    }

    public function verify(RegistrationDraft $draft, string $code): void
    {
        if ($draft->isEmailVerified()) {
            return;
        }

        if (!$draft->otp_hash || !$draft->otp_expires_at || $draft->otp_expires_at->isPast()) {
            throw new InvalidArgumentException('Your verification code has expired. Please request a new one.');
        }

        if ($draft->otp_attempts >= self::MAX_ATTEMPTS) {
            throw new InvalidArgumentException('Too many invalid attempts. Please request a new verification code.');
        }

        if (!Hash::check(trim($code), $draft->otp_hash)) {
            $draft->increment('otp_attempts');
            throw new InvalidArgumentException('Invalid verification code.');
        }

        $draft->forceFill([
            'email_verified_at' => now(),
            'otp_hash' => null,
            'otp_expires_at' => null,
            'otp_attempts' => 0,
        ])->save();
    }

    private function sendMail(RegistrationDraft $draft, Event $event, string $otp, string $resumeUrl): void
    {
        $html = View::make('emails.registration-otp', [
            'event' => $event,
            'otp' => $otp,
            'resumeUrl' => $resumeUrl,
            'expiresMinutes' => self::OTP_TTL_MINUTES,
        ])->render();

        $text = "Your verification code for {$event->title} is {$otp}. It expires in ".self::OTP_TTL_MINUTES." minutes.\n\nResume your registration: {$resumeUrl}";

        try {
            $this->providerManager->getProvider()->send(
                $draft->email,
                "Verify your email for {$event->title}",
                $html,
                $text,
                [
                    'from_email' => $event->smtp_username ?: config('mail.from.address'),
                    'from_name' => $event->title ?: config('mail.from.name'),
                ]
            );
        } catch (\Throwable $exception) {
            Log::warning('Failed to send registration OTP email', [
                'draft_id' => $draft->id,
                'email' => $draft->email,
                'error' => $exception->getMessage(),
            ]);

            // In local/testing, allow flow to continue with logged OTP.
            if (!app()->environment('production')) {
                Log::info('Registration OTP (non-production)', [
                    'email' => $draft->email,
                    'otp' => $otp,
                ]);

                return;
            }

            throw new InvalidArgumentException('Unable to send verification email right now. Please try again shortly.');
        }
    }
}
