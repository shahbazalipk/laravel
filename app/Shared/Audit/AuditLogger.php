<?php

namespace App\Shared\Audit;

use App\Shared\Audit\Models\PlatformAuditEntry;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class AuditLogger
{
    private const REDACTED = '[REDACTED]';

    private const SENSITIVE_KEYS = [
        'account_number',
        'bank_account',
        'card_number',
        'credentials',
        'iban',
        'passport_number',
        'password',
        'secret',
        'token',
    ];

    public function record(
        string $module,
        string $action,
        Model $subject,
        array $before = [],
        array $after = [],
        ?string $reason = null,
        array $metadata = [],
        ?string $idempotencyKey = null,
    ): PlatformAuditEntry {
        return PlatformAuditEntry::query()->create([
            'module' => $module,
            'action' => $action,
            'subject_type' => $subject->getMorphClass(),
            'subject_public_id' => $subject->getAttribute('public_id'),
            'subject_id' => $subject->getKey(),
            'actor_type' => session('admin_type'),
            'actor_id' => session('admin_id'),
            'actor_name' => session('admin_name'),
            'actor_email' => session('admin_email'),
            'ip_address' => request()?->ip(),
            'user_agent' => request()?->userAgent(),
            'reason' => $reason,
            'before_values' => $this->redact($before),
            'after_values' => $this->redact($after),
            'metadata' => $this->redact($metadata),
            'correlation_id' => request()?->headers->get('X-Correlation-ID') ?: (string) Str::uuid(),
            'idempotency_key' => $idempotencyKey,
            'occurred_at' => now(),
        ]);
    }

    private function redact(array $values): array
    {
        foreach ($values as $key => $value) {
            if (in_array(Str::snake((string) $key), self::SENSITIVE_KEYS, true)) {
                $values[$key] = self::REDACTED;
            } elseif (is_array($value)) {
                $values[$key] = $this->redact($value);
            }
        }

        return $values;
    }
}
