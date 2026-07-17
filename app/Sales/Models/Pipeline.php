<?php

namespace App\Sales\Models;

use App\Sales\Enums\PipelineStatus;
use App\Traits\HasEventScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Pipeline extends Model
{
    use HasEventScope, SoftDeletes;

    protected $table = 'sales_pipelines';

    protected $fillable = [
        'public_id',
        'event_id',
        'org_id',
        'sales_pipeline_type_id',
        'name',
        'slug',
        'description',
        'status',
        'start_date',
        'end_date',
        'currency',
        'revenue_target',
        'owner_admin_id',
        'custom_field_answers',
        'team_admin_ids',
        'settings',
        'created_by',
        'updated_by',
        'last_activity_at',
    ];

    protected $casts = [
        'status' => PipelineStatus::class,
        'start_date' => 'date',
        'end_date' => 'date',
        'revenue_target' => 'decimal:2',
        'custom_field_answers' => 'array',
        'team_admin_ids' => 'array',
        'settings' => 'array',
        'last_activity_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $pipeline): void {
            if (empty($pipeline->public_id)) {
                $pipeline->public_id = (string) Str::uuid();
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'public_id';
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(PipelineType::class, 'sales_pipeline_type_id');
    }

    public function stages(): HasMany
    {
        return $this->hasMany(PipelineStage::class, 'sales_pipeline_id')->orderBy('sort_order');
    }

    public function deals(): HasMany
    {
        return $this->hasMany(Deal::class, 'sales_pipeline_id');
    }
}
