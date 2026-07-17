<?php

namespace App\Forms\Models;

use App\Forms\Enums\FormResponseStatus;
use App\Traits\HasEventScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class CustomFormResponse extends Model
{
    use HasEventScope, SoftDeletes;

    protected $fillable = [
        'public_id',
        'event_id',
        'org_id',
        'custom_form_id',
        'respondent_type',
        'respondent_id',
        'status',
        'form_version',
        'submitted_at',
        'metadata',
    ];

    protected $casts = [
        'status' => FormResponseStatus::class,
        'form_version' => 'integer',
        'submitted_at' => 'datetime',
        'metadata' => 'array',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $response): void {
            if (empty($response->public_id)) {
                $response->public_id = (string) Str::uuid();
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'public_id';
    }

    public function form(): BelongsTo
    {
        return $this->belongsTo(CustomForm::class, 'custom_form_id');
    }

    public function respondent(): MorphTo
    {
        return $this->morphTo();
    }

    public function answers(): HasMany
    {
        return $this->hasMany(CustomFormAnswer::class);
    }

    public function isSubmitted(): bool
    {
        return $this->status === FormResponseStatus::Submitted;
    }

    public function isDraft(): bool
    {
        return $this->status === FormResponseStatus::Draft;
    }
}
