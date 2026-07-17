<?php

namespace App\Sales\Models;

use App\Traits\HasEventScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PipelineTypeFieldCondition extends Model
{
    use HasEventScope, SoftDeletes;

    protected $table = 'sales_pipeline_type_field_conditions';

    protected $fillable = [
        'event_id',
        'org_id',
        'sales_pipeline_type_id',
        'target_field_id',
        'source_field_id',
        'operator',
        'compare_value',
        'action',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'compare_value' => 'array',
        'sort_order' => 'integer',
        'is_active' => 'boolean',
    ];

    public function type(): BelongsTo
    {
        return $this->belongsTo(PipelineType::class, 'sales_pipeline_type_id');
    }

    public function targetField(): BelongsTo
    {
        return $this->belongsTo(PipelineTypeField::class, 'target_field_id');
    }

    public function sourceField(): BelongsTo
    {
        return $this->belongsTo(PipelineTypeField::class, 'source_field_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
