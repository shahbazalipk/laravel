<?php

namespace App\Submissions\Models;

use App\Submissions\Enums\SubmissionCategory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;

class SubmissionType extends SubmissionModel
{
    use SoftDeletes;

    protected $casts = [
        'category' => SubmissionCategory::class,
        'settings' => 'array',
        'opens_at' => 'datetime',
        'closes_at' => 'datetime',
        'allow_drafts' => 'boolean',
        'allow_editing_after_submission' => 'boolean',
        'allow_anonymous_review' => 'boolean',
        'enable_scoring' => 'boolean',
        'enable_revisions' => 'boolean',
        'enable_speaker_onboarding' => 'boolean',
        'published_at' => 'datetime',
        'decision_rules' => 'array',
    ];

    public function sections(): HasMany
    {
        return $this->hasMany(FormSection::class)->orderBy('sort_order');
    }

    public function schemaVersions(): HasMany
    {
        return $this->hasMany(SubmissionSchemaVersion::class);
    }

    public function workflowStages(): HasMany
    {
        return $this->hasMany(WorkflowStage::class)->orderBy('sort_order');
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(Submission::class);
    }

    public function scorecards(): HasMany
    {
        return $this->hasMany(Scorecard::class);
    }

    public function questions(): HasManyThrough
    {
        return $this->hasManyThrough(Question::class, FormSection::class, 'submission_type_id', 'form_section_id');
    }

    public function conditionalRules(): HasMany
    {
        return $this->hasMany(ConditionalRule::class);
    }

    public function stageTransitions(): HasMany
    {
        return $this->hasMany(StageTransition::class);
    }

    public function speakerChecklists(): HasMany
    {
        return $this->hasMany(SpeakerChecklist::class);
    }
}
