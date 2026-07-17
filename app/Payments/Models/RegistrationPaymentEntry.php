<?php

namespace App\Payments\Models;

use App\Models\Registration;
use App\Payments\Enums\PaymentEntryStatus;
use App\Payments\Enums\PaymentEntryType;
use App\Traits\HasEventScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;
use LogicException;

class RegistrationPaymentEntry extends Model
{
    use HasEventScope;

    public $timestamps = false;

    protected $table = 'registration_payment_entries';

    protected $fillable = [
        'public_id',
        'event_id',
        'org_id',
        'registration_id',
        'type',
        'status',
        'amount',
        'currency',
        'method',
        'reference',
        'occurred_at',
        'notes',
        'reverses_entry_id',
        'recorded_by_type',
        'recorded_by_id',
        'recorded_by_name',
        'recorded_by_email',
        'metadata',
        'created_at',
    ];

    protected $casts = [
        'type' => PaymentEntryType::class,
        'status' => PaymentEntryStatus::class,
        'amount' => 'decimal:2',
        'occurred_at' => 'datetime',
        'metadata' => 'array',
        'created_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $entry): void {
            if (empty($entry->public_id)) {
                $entry->public_id = (string) Str::uuid();
            }

            if (empty($entry->created_at)) {
                $entry->created_at = now();
            }
        });

        static::updating(function (): void {
            throw new LogicException('Registration payment entries are immutable and cannot be updated.');
        });

        static::deleting(function (): void {
            throw new LogicException('Registration payment entries are immutable and cannot be deleted.');
        });
    }

    public function getRouteKeyName(): string
    {
        return 'public_id';
    }

    public function registration(): BelongsTo
    {
        return $this->belongsTo(Registration::class);
    }

    public function reversesEntry(): BelongsTo
    {
        return $this->belongsTo(self::class, 'reverses_entry_id');
    }

    public function reversalEntries()
    {
        return $this->hasMany(self::class, 'reverses_entry_id');
    }

    public function isSucceeded(): bool
    {
        return $this->status === PaymentEntryStatus::Succeeded;
    }

    public function isPayment(): bool
    {
        return $this->type === PaymentEntryType::Payment;
    }

    public function isRefund(): bool
    {
        return $this->type === PaymentEntryType::Refund;
    }

    public function isReversal(): bool
    {
        return $this->type === PaymentEntryType::Reversal;
    }

    public function signedAmount(): float
    {
        if (!$this->isSucceeded()) {
            return 0.0;
        }

        return match ($this->type) {
            PaymentEntryType::Payment => (float) $this->amount,
            PaymentEntryType::Refund, PaymentEntryType::Reversal => -1 * (float) $this->amount,
        };
    }
}
