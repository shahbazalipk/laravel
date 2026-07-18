<?php

namespace App\Http\Controllers\Admin\Submissions;

use App\Http\Controllers\Controller;
use App\Submissions\Models\FormSection;
use App\Submissions\Models\Question;
use App\Submissions\Models\SubmissionType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ConfigurationController extends Controller
{
    public function storeSection(Request $request, SubmissionType $submissionType): RedirectResponse
    {
        $data = $request->validate(['title' => ['required', 'string', 'max:255'], 'description' => ['nullable', 'string']]);
        $submissionType->sections()->create([
            ...$data,
            'slug' => Str::slug($data['title']).'-'.Str::lower(Str::random(4)),
            'sort_order' => $submissionType->sections()->count(),
        ]);

        return back()->with('success', 'Section added.');
    }

    public function storeQuestion(Request $request, FormSection $section): RedirectResponse
    {
        $data = $request->validate([
            'label' => ['required', 'string', 'max:255'],
            'key' => ['required', 'alpha_dash', 'max:100'],
            'type' => ['required', Rule::in([
                'text', 'textarea', 'rich_text', 'email', 'phone', 'number', 'decimal', 'date', 'datetime', 'time',
                'select', 'multiselect', 'radio', 'checkbox', 'yes_no', 'country', 'city', 'url', 'file', 'image',
                'rating', 'consent', 'heading', 'description', 'divider', 'hidden', 'calculated', 'repeater',
            ])],
            'help_text' => ['nullable', 'string'],
            'placeholder' => ['nullable', 'string', 'max:255'],
            'is_required' => ['nullable', 'boolean'],
            'options' => ['nullable', 'string'],
        ]);
        $question = $section->questions()->create([
            ...$data,
            'is_required' => (bool) ($data['is_required'] ?? false),
            'is_active' => true,
            'sort_order' => $section->questions()->count(),
        ]);
        foreach (preg_split('/\r\n|\r|\n/', $data['options'] ?? '') ?: [] as $index => $option) {
            if (filled($option)) {
                $question->options()->create(['label' => trim($option), 'value' => str($option)->slug(), 'sort_order' => $index]);
            }
        }

        return back()->with('success', 'Question added.');
    }

    public function destroyQuestion(Question $question): RedirectResponse
    {
        $question->delete();

        return back()->with('success', 'Question archived; historical answers were retained.');
    }

    public function storeRule(Request $request, SubmissionType $submissionType): RedirectResponse
    {
        $data = $request->validate([
            'source_question_id' => ['required', 'integer'],
            'target_question_id' => ['required', 'integer'],
            'operator' => ['required', Rule::in(['equals', 'not_equals', 'contains', 'not_contains', 'greater_than', 'less_than', 'greater_or_equal', 'less_or_equal', 'is_empty', 'is_not_empty', 'is_selected', 'is_not_selected', 'includes_any', 'includes_all', 'date_before', 'date_after'])],
            'action' => ['required', Rule::in(['show', 'hide', 'require', 'optional', 'enable', 'disable', 'set_default', 'clear'])],
            'compare_value' => ['nullable', 'string', 'max:1000'],
            'match' => ['nullable', Rule::in(['all', 'any'])],
        ]);
        $questionIds = $submissionType->questions()->pluck('submission_questions.id');
        abort_unless($questionIds->contains((int) $data['source_question_id']) && $questionIds->contains((int) $data['target_question_id']), 422);
        $submissionType->conditionalRules()->create([
            'source_question_id' => $data['source_question_id'],
            'target_question_id' => $data['target_question_id'],
            'operator' => $data['operator'],
            'action' => $data['action'],
            'compare_value' => $data['compare_value'],
            'sort_order' => $submissionType->conditionalRules()->count(),
            'is_active' => true,
            'settings' => ['match' => $data['match'] ?? 'all'],
        ]);

        return back()->with('success', 'Conditional rule added.');
    }

    public function storeStage(Request $request, SubmissionType $submissionType): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'slug' => ['required', 'alpha_dash', 'max:60'],
            'category' => ['required', 'string', 'max:40'],
            'color' => ['nullable', 'string', 'max:20'],
        ]);
        $submissionType->workflowStages()->create([...$data, 'sort_order' => $submissionType->workflowStages()->count()]);

        return back()->with('success', 'Workflow stage added.');
    }

    public function storeCriterion(Request $request, SubmissionType $submissionType): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::in(['numeric', 'rating', 'yes_no', 'single', 'multi', 'text', 'recommendation', 'compliance'])],
            'minimum_score' => ['nullable', 'numeric'],
            'maximum_score' => ['nullable', 'numeric', 'gte:minimum_score'],
            'weight' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ]);
        $scorecard = $submissionType->scorecards()->firstOrCreate(
            ['name' => 'Default Scorecard'],
            ['status' => 'active', 'settings' => ['version' => 1]],
        );
        $scorecard->criteria()->create([
            'name' => $data['name'],
            'type' => $data['type'],
            'min_score' => $data['minimum_score'] ?? 0,
            'max_score' => $data['maximum_score'] ?? 5,
            'weight' => $data['weight'] ?? 1,
            'sort_order' => $scorecard->criteria()->count(),
            'is_required' => true,
        ]);

        return back()->with('success', 'Review criterion added.');
    }
}
