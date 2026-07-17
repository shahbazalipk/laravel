<?php

namespace App\Sales\Models;

use App\Sales\Enums\StageCategory;
use App\Traits\HasEventScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class PipelineStage extends Model
{
    use HasEventScope, SoftDeletes;

    protected $table = 'sales_pipeline_stages';

    protected $fillable = [
        'public_id',
        'event_id',
        'org_id',
        'sales_pipeline_id',
        'source_type_stage_id',
        'name',
        'slug',
        'description',
        'color',
        'sort_order',
        'probability',
        'category',
        'is_default',
        'is_active',
        'sla_hours',
        'instructions',
        'required_field_keys',
    ];

    protected $casts = [
        'category' => StageCategory::class,
        'probability' => 'integer',
        'sort_order' => 'integer',
        'is_default' => 'boolean',
        'is_active' => 'boolean',
        'sla_hours' => 'integer',
        'required_field_keys' => 'array',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $stage): void {
            if (empty($stage->public_id)) {
                $stage->public_id = (string) Str::uuid();
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'public_id';
    }

    public function pipeline(): BelongsTo
    {
        return $this->belongsTo(Pipeline::class, 'sales_pipeline_id');
    }

    public function sourceTypeStage(): BelongsTo
    {
        return $this->belongsTo(PipelineTypeStage::class, 'source_type_stage_id');
    }

    public function deals(): HasMany
    {
        return $this->hasMany(Deal::class, 'sales_pipeline_stage_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
