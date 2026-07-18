<?php

namespace App\Submissions\Models;

use App\Submissions\Enums\QuestionType;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Question extends SubmissionModel
{
    use SoftDeletes;

    protected $table = 'submission_questions';

    protected $casts = [
        'type' => QuestionType::class,
        'is_required' => 'boolean',
        'is_active' => 'boolean',
        'allow_reviewer_access' => 'boolean',
        'hidden_during_anonymous_review' => 'boolean',
        'validation' => 'array',
        'settings' => 'array',
    ];

    public function section(): BelongsTo
    {
        return $this->belongsTo(FormSection::class, 'form_section_id');
    }

    public function options(): HasMany
    {
        return $this->hasMany(QuestionOption::class)->orderBy('sort_order');
    }

    public function answers(): HasMany
    {
        return $this->hasMany(SubmissionAnswer::class);
    }

    public function conditionalRules(): HasMany
    {
        return $this->hasMany(ConditionalRule::class, 'target_question_id');
    }
}
