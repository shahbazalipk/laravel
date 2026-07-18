<?php

namespace App\Submissions\Services;

use App\Submissions\Models\SubmissionType;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final class SubmissionTypeService
{
    /** @param array<string, mixed> $data */
    public function create(array $data): SubmissionType
    {
        return DB::transaction(function () use ($data): SubmissionType {
            $type = SubmissionType::query()->create($this->attributes($data));
            $type->workflowStages()->createMany([
                ['name' => 'Draft', 'slug' => 'draft', 'category' => 'draft', 'sort_order' => 0, 'is_initial' => true],
                ['name' => 'Submitted', 'slug' => 'submitted', 'category' => 'submitted', 'sort_order' => 1],
                ['name' => 'Under Review', 'slug' => 'under-review', 'category' => 'review', 'sort_order' => 2],
                ['name' => 'Selected', 'slug' => 'selected', 'category' => 'accepted', 'sort_order' => 3, 'is_terminal' => true],
                ['name' => 'Rejected', 'slug' => 'rejected', 'category' => 'rejected', 'sort_order' => 4, 'is_terminal' => true],
            ]);
            $type->forceFill(['default_stage_id' => $type->workflowStages()->where('is_initial', true)->value('id')])->save();
            $stages = $type->workflowStages()->get()->keyBy('slug');
            foreach ([['draft', 'submitted'], ['submitted', 'under-review'], ['under-review', 'selected'], ['under-review', 'rejected']] as [$from, $to]) {
                $type->stageTransitions()->create([
                    'from_stage_id' => $stages[$from]->getKey(),
                    'to_stage_id' => $stages[$to]->getKey(),
                    'name' => "{$stages[$from]->name} to {$stages[$to]->name}",
                    'is_active' => true,
                ]);
            }
            $type->speakerChecklists()->create([
                'name' => 'Speaker onboarding',
                'category' => 'onboarding',
                'items' => [
                    ['key' => 'biography_approved', 'label' => 'Biography approved'],
                    ['key' => 'profile_picture_approved', 'label' => 'Profile picture approved'],
                    ['key' => 'contract_signed', 'label' => 'Contract signed'],
                    ['key' => 'presentation_uploaded', 'label' => 'Presentation uploaded'],
                    ['key' => 'session_assigned', 'label' => 'Session assigned'],
                ],
                'is_active' => true,
            ]);

            return $type->refresh();
        });
    }

    /** @param array<string, mixed> $data */
    public function update(SubmissionType $type, array $data): SubmissionType
    {
        $type->update($this->attributes($data));

        return $type->refresh();
    }

    public function duplicate(SubmissionType $source): SubmissionType
    {
        return DB::transaction(function () use ($source): SubmissionType {
            $source->load(['sections.questions.options', 'conditionalRules', 'workflowStages.outgoingTransitions', 'scorecards.criteria']);
            $copy = $source->replicate(['public_id', 'number_sequence', 'published_form_version', 'published_at']);
            $copy->name .= ' (Copy)';
            $copy->code = $this->uniqueCode($source->code.'-COPY');
            $copy->slug = Str::slug($copy->name).'-'.Str::lower(Str::random(5));
            $copy->status = 'draft';
            $copy->save();

            $questionMap = [];
            foreach ($source->sections as $section) {
                $newSection = $copy->sections()->create($section->replicate(['public_id'])->toArray());
                foreach ($section->questions as $question) {
                    $newQuestion = $newSection->questions()->create($question->replicate(['public_id'])->toArray());
                    $questionMap[$question->getKey()] = $newQuestion->getKey();
                    foreach ($question->options as $option) {
                        $newQuestion->options()->create($option->replicate(['public_id'])->toArray());
                    }
                }
            }
            foreach ($source->conditionalRules as $rule) {
                if (isset($questionMap[$rule->source_question_id], $questionMap[$rule->target_question_id])) {
                    $copy->conditionalRules()->create([
                        ...$rule->replicate(['public_id'])->toArray(),
                        'source_question_id' => $questionMap[$rule->source_question_id],
                        'target_question_id' => $questionMap[$rule->target_question_id],
                    ]);
                }
            }
            $stageMap = [];
            foreach ($source->workflowStages as $stage) {
                $newStage = $copy->workflowStages()->create($stage->replicate(['public_id'])->toArray());
                $stageMap[$stage->getKey()] = $newStage->getKey();
            }
            foreach ($source->workflowStages as $stage) {
                foreach ($stage->outgoingTransitions as $transition) {
                    $copy->stageTransitions()->create([
                        ...$transition->replicate(['public_id'])->toArray(),
                        'from_stage_id' => $stageMap[$transition->from_stage_id],
                        'to_stage_id' => $stageMap[$transition->to_stage_id],
                    ]);
                }
            }
            foreach ($source->scorecards as $scorecard) {
                $new = $copy->scorecards()->create($scorecard->replicate(['public_id'])->toArray());
                foreach ($scorecard->criteria as $criterion) {
                    $new->criteria()->create($criterion->replicate(['public_id'])->toArray());
                }
            }
            if ($source->default_stage_id && isset($stageMap[$source->default_stage_id])) {
                $copy->forceFill(['default_stage_id' => $stageMap[$source->default_stage_id]])->save();
            }

            return $copy->refresh();
        });
    }

    public function publish(SubmissionType $type): SubmissionType
    {
        if (! $type->questions()->where('submission_questions.is_active', true)->exists()) {
            throw ValidationException::withMessages(['questions' => 'Add an active question before publishing.']);
        }
        $version = ((int) $type->published_form_version) + 1;
        $schema = $type->load(['sections.questions.options', 'conditionalRules', 'workflowStages', 'scorecards.criteria'])->toArray();
        $type->schemaVersions()->create([
            'version' => $version,
            'status' => 'published',
            'schema_snapshot' => $schema,
            'published_at' => now(),
            'published_by' => session('admin_id'),
        ]);
        $type->forceFill(['published_form_version' => $version, 'published_at' => now(), 'status' => 'active'])->save();

        return $type->refresh();
    }

    /** @param array<string, mixed> $data */
    private function attributes(array $data): array
    {
        return [
            ...$data,
            'slug' => Str::slug($data['slug'] ?? $data['name']),
            'allow_drafts' => (bool) ($data['allow_drafts'] ?? false),
            'allow_editing_after_submission' => (bool) ($data['allow_editing_after_submission'] ?? false),
            'allow_anonymous_review' => (bool) ($data['allow_anonymous_review'] ?? false),
            'enable_scoring' => (bool) ($data['enable_scoring'] ?? false),
            'enable_revisions' => (bool) ($data['enable_revisions'] ?? false),
            'enable_speaker_onboarding' => (bool) ($data['enable_speaker_onboarding'] ?? false),
            'created_by' => session('admin_id'),
            'updated_by' => session('admin_id'),
        ];
    }

    private function uniqueCode(string $base): string
    {
        $code = strtoupper(Str::slug($base, '-'));
        while (SubmissionType::withTrashed()->where('code', $code)->exists()) {
            $code = substr($code, 0, 24).'-'.Str::upper(Str::random(4));
        }

        return $code;
    }
}
