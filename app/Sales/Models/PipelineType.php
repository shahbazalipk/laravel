<?php

namespace App\Sales\Models;

use App\Traits\HasEventScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class PipelineType extends Model
{
    use HasEventScope, SoftDeletes;

    protected $table = 'sales_pipeline_types';

    protected $fillable = [
        'public_id',
        'event_id',
        'org_id',
        'name',
        'slug',
        'description',
        'color',
        'is_active',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $type): void {
            if (empty($type->public_id)) {
                $type->public_id = (string) Str::uuid();
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'public_id';
    }

    public function stages(): HasMany
    {
        return $this->hasMany(PipelineTypeStage::class, 'sales_pipeline_type_id')->orderBy('sort_order');
    }

    public function fields(): HasMany
    {
        return $this->hasMany(PipelineTypeField::class, 'sales_pipeline_type_id')->orderBy('sort_order');
    }

    public function conditions(): HasMany
    {
        return $this->hasMany(PipelineTypeFieldCondition::class, 'sales_pipeline_type_id')->orderBy('sort_order');
    }

    public function pipelines(): HasMany
    {
        return $this->hasMany(Pipeline::class, 'sales_pipeline_type_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
