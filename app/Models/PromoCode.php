<?php

namespace App\Models;

use App\Enums\PromoDiscountType;
use App\Traits\HasEventScope;
use App\Traits\HasHashedRoutes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PromoCode extends Model
{
    use HasEventScope, HasHashedRoutes;

    protected $fillable = [
        'event_id',
        'org_id',
        'code',
        'name',
        'description',
        'discount_type',
        'discount_value',
        'currency',
        'starts_at',
        'expires_at',
        'max_total_uses',
        'max_uses_per_email',
        'used_count',
        'is_active',
        'restrict_to_email_list',
    ];

    protected $casts = [
        'discount_type' => PromoDiscountType::class,
        'discount_value' => 'decimal:2',
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
        'max_total_uses' => 'integer',
        'max_uses_per_email' => 'integer',
        'used_count' => 'integer',
        'is_active' => 'boolean',
        'restrict_to_email_list' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::saving(function (self $promoCode): void {
            $promoCode->code = strtoupper(trim((string) $promoCode->code));
        });
    }

    public function emails(): HasMany
    {
        return $this->hasMany(PromoCodeEmail::class);
    }

    public function isEmailAllowed(string $email): bool
    {
        if (! $this->restrict_to_email_list) {
            return true;
        }

        $normalized = strtolower(trim($email));
        if ($normalized === '') {
            return false;
        }

        return $this->emails()->where('email', $normalized)->exists();
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    public function hasStarted(): bool
    {
        return $this->starts_at === null || $this->starts_at->isPast();
    }

    public function hasRemainingUses(): bool
    {
        return $this->max_total_uses === null || $this->used_count < $this->max_total_uses;
    }

    public function remainingUses(): ?int
    {
        if ($this->max_total_uses === null) {
            return null;
        }

        return max(0, $this->max_total_uses - $this->used_count);
    }

    public function isCurrentlyValid(): bool
    {
        return $this->is_active
            && $this->hasStarted()
            && ! $this->isExpired()
            && $this->hasRemainingUses();
    }

    public function discountLabel(): string
    {
        if ($this->discount_type === PromoDiscountType::Percentage) {
            return rtrim(rtrim(number_format((float) $this->discount_value, 2, '.', ''), '0'), '.').'%';
        }

        $currency = $this->currency ?: 'AED';

        return $currency.' '.number_format((float) $this->discount_value, 2);
    }

    public function usageLabel(): string
    {
        if ($this->max_total_uses === null) {
            return $this->used_count.' / ∞';
        }

        return $this->used_count.' / '.$this->max_total_uses;
    }
}
