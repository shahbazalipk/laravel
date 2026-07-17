<?php

namespace App\Forms\Models;

use App\Forms\Enums\FormConditionAction;
use App\Forms\Enums\FormConditionOperator;
use App\Traits\HasEventScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class CustomFormCondition extends Model
{
    use HasEventScope, SoftDeletes;

    protected $fillable = [
        'event_id',
        'org_id',
        'custom_form_id',
        'target_question_id',
        'source_question_id',
        'operator',
        'compare_value',
        'action',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'operator' => FormConditionOperator::class,
        'action' => FormConditionAction::class,
        'compare_value' => 'json',
        'sort_order' => 'integer',
        'is_active' => 'boolean',
    ];

    public function form(): BelongsTo
    {
        return $this->belongsTo(CustomForm::class, 'custom_form_id');
    }

    public function targetQuestion(): BelongsTo
    {
        return $this->belongsTo(CustomFormQuestion::class, 'target_question_id');
    }

    public function sourceQuestion(): BelongsTo
    {
        return $this->belongsTo(CustomFormQuestion::class, 'source_question_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
