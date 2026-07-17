<?php

namespace App\Forms\Models;

use App\Forms\Enums\FormQuestionType;
use App\Traits\HasEventScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class CustomFormQuestion extends Model
{
    use HasEventScope, SoftDeletes;

    protected $fillable = [
        'public_id',
        'event_id',
        'org_id',
        'custom_form_id',
        'key',
        'label',
        'help_text',
        'placeholder',
        'type',
        'is_required',
        'sort_order',
        'is_active',
        'validation',
        'settings',
    ];

    protected $casts = [
        'type' => FormQuestionType::class,
        'is_required' => 'boolean',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
        'validation' => 'array',
        'settings' => 'array',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $question): void {
            if (empty($question->public_id)) {
                $question->public_id = (string) Str::uuid();
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

    public function options(): HasMany
    {
        return $this->hasMany(CustomFormQuestionOption::class)->orderBy('sort_order');
    }

    public function activeOptions(): HasMany
    {
        return $this->options()->where('is_active', true);
    }

    public function targetConditions(): HasMany
    {
        return $this->hasMany(CustomFormCondition::class, 'target_question_id');
    }

    public function sourceConditions(): HasMany
    {
        return $this->hasMany(CustomFormCondition::class, 'source_question_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Active option values for validation / visibility compares.
     *
     * @return list<string>
     */
    public function optionValues(): array
    {
        return $this->relationLoaded('options')
            ? $this->options->where('is_active', true)->pluck('value')->map(fn ($v) => (string) $v)->all()
            : $this->activeOptions()->pluck('value')->map(fn ($v) => (string) $v)->all();
    }
}
