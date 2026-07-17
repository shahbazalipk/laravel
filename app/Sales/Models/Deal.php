<?php

namespace App\Sales\Models;

use App\Sales\Enums\DealStatus;
use App\Traits\HasEventScope;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Deal extends Model
{
    use HasEventScope, SoftDeletes;

    protected $table = 'sales_deals';

    protected $fillable = [
        'public_id',
        'event_id',
        'org_id',
        'sales_pipeline_id',
        'sales_pipeline_stage_id',
        'title',
        'reference',
        'value',
        'currency',
        'probability',
        'expected_close_date',
        'actual_close_date',
        'owner_admin_id',
        'assigned_admin_ids',
        'lead_source',
        'status',
        'priority',
        'description',
        'lost_reason',
        'won_notes',
        'tags',
        'custom_field_answers',
        'created_by',
        'updated_by',
        'last_activity_at',
    ];

    protected $casts = [
        'status' => DealStatus::class,
        'value' => 'decimal:2',
        'probability' => 'integer',
        'expected_close_date' => 'date',
        'actual_close_date' => 'date',
        'assigned_admin_ids' => 'array',
        'tags' => 'array',
        'custom_field_answers' => 'array',
        'last_activity_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $deal): void {
            if (empty($deal->public_id)) {
                $deal->public_id = (string) Str::uuid();
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'public_id';
    }

    protected function weightedValue(): Attribute
    {
        return Attribute::get(function (): ?float {
            if ($this->value === null) {
                return null;
            }

            $probability = $this->probability ?? 0;

            return (float) $this->value * ($probability / 100);
        });
    }

    public function pipeline(): BelongsTo
    {
        return $this->belongsTo(Pipeline::class, 'sales_pipeline_id');
    }

    public function stage(): BelongsTo
    {
        return $this->belongsTo(PipelineStage::class, 'sales_pipeline_stage_id');
    }

    public function contacts(): HasMany
    {
        return $this->hasMany(DealContact::class, 'sales_deal_id');
    }

    public function notes(): HasMany
    {
        return $this->hasMany(DealNote::class, 'sales_deal_id');
    }

    public function stageHistories(): HasMany
    {
        return $this->hasMany(DealStageHistory::class, 'sales_deal_id');
    }

    public function activities(): MorphMany
    {
        return $this->morphMany(SalesActivity::class, 'subject')->latest();
    }
}
