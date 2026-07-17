<?php

namespace App\Forms\Models;

use App\Forms\Enums\FormAudience;
use App\Models\Event;
use App\Traits\HasEventScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CustomForm extends Model
{
    use HasEventScope, SoftDeletes;

    protected $fillable = [
        'public_id',
        'event_id',
        'org_id',
        'name',
        'slug',
        'description',
        'audience',
        'audience_unique',
        'is_active',
        'settings',
    ];

    protected $casts = [
        'audience' => FormAudience::class,
        'is_active' => 'boolean',
        'settings' => 'array',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $form): void {
            if (empty($form->public_id)) {
                $form->public_id = (string) Str::uuid();
            }

            $form->syncAudienceUniqueSlot();
        });

        static::updating(function (self $form): void {
            if (! $form->trashed()) {
                $form->syncAudienceUniqueSlot();
            }
        });

        static::deleted(function (self $form): void {
            if ($form->trashed()) {
                DB::table('custom_forms')
                    ->where('id', $form->getKey())
                    ->update(['audience_unique' => null]);
                $form->audience_unique = null;
            }
        });

        static::restoring(function (self $form): void {
            $form->syncAudienceUniqueSlot();
        });
    }

    public function syncAudienceUniqueSlot(): void
    {
        $audience = $this->audience instanceof FormAudience
            ? $this->audience->value
            : $this->audience;

        $this->audience_unique = $audience;
    }

    public function getRouteKeyName(): string
    {
        return 'public_id';
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function questions(): HasMany
    {
        return $this->hasMany(CustomFormQuestion::class)->orderBy('sort_order');
    }

    public function conditions(): HasMany
    {
        return $this->hasMany(CustomFormCondition::class)->orderBy('sort_order');
    }

    public function responses(): HasMany
    {
        return $this->hasMany(CustomFormResponse::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForAudience($query, FormAudience|string $audience)
    {
        $value = $audience instanceof FormAudience ? $audience->value : $audience;

        return $query->where('audience', $value);
    }
}
