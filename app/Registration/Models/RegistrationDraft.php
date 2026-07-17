<?php

namespace App\Registration\Models;

use App\Models\Event;
use App\Models\EventUrl;
use App\Models\Registration;
use App\Registration\Enums\RegistrationWizardStep;
use App\Traits\HasEventScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class RegistrationDraft extends Model
{
    use HasEventScope;

    protected $fillable = [
        'public_id',
        'event_id',
        'org_id',
        'event_url_id',
        'email',
        'resume_token_hash',
        'payload',
        'current_step',
        'email_verified_at',
        'otp_hash',
        'otp_expires_at',
        'otp_attempts',
        'otp_last_sent_at',
        'registration_id',
        'expires_at',
        'completed_at',
    ];

    protected $casts = [
        'payload' => 'encrypted:array',
        'current_step' => RegistrationWizardStep::class,
        'email_verified_at' => 'datetime',
        'otp_expires_at' => 'datetime',
        'otp_last_sent_at' => 'datetime',
        'expires_at' => 'datetime',
        'completed_at' => 'datetime',
        'otp_attempts' => 'integer',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $draft): void {
            if (empty($draft->public_id)) {
                $draft->public_id = (string) Str::uuid();
            }
        });
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function eventUrl(): BelongsTo
    {
        return $this->belongsTo(EventUrl::class);
    }

    public function registration(): BelongsTo
    {
        return $this->belongsTo(Registration::class);
    }

    public function getRouteKeyName(): string
    {
        return 'public_id';
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    public function isCompleted(): bool
    {
        return $this->completed_at !== null || $this->registration_id !== null;
    }

    public function isEmailVerified(): bool
    {
        return $this->email_verified_at !== null;
    }

    public function payloadValue(string $key, mixed $default = null): mixed
    {
        return data_get($this->payload ?? [], $key, $default);
    }

    public function mergePayload(array $data): void
    {
        $this->payload = array_merge($this->payload ?? [], $data);
    }
}
