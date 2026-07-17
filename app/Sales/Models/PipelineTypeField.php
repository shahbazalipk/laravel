<?php

namespace App\Sales\Models;

use App\Sales\Enums\FieldScope;
use App\Sales\Enums\SalesFieldType;
use App\Traits\HasEventScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class PipelineTypeField extends Model
{
    use HasEventScope, SoftDeletes;

    protected $table = 'sales_pipeline_type_fields';

    protected $fillable = [
        'public_id',
        'event_id',
        'org_id',
        'sales_pipeline_type_id',
        'scope',
        'key',
        'label',
        'type',
        'help_text',
        'placeholder',
        'default_value',
        'is_required',
        'is_active',
        'sort_order',
        'validation',
        'options',
        'settings',
    ];

    protected $casts = [
        'scope' => FieldScope::class,
        'type' => SalesFieldType::class,
        'default_value' => 'array',
        'is_required' => 'boolean',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
        'validation' => 'array',
        'options' => 'array',
        'settings' => 'array',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $field): void {
            if (empty($field->public_id)) {
                $field->public_id = (string) Str::uuid();
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

    public function targetConditions(): HasMany
    {
        return $this->hasMany(PipelineTypeFieldCondition::class, 'target_field_id');
    }

    public function sourceConditions(): HasMany
    {
        return $this->hasMany(PipelineTypeFieldCondition::class, 'source_field_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
