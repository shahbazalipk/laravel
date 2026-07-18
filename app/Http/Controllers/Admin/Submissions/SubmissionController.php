<?php

namespace App\Http\Controllers\Admin\Submissions;

use App\Http\Controllers\Controller;
use App\Submissions\Models\Reviewer;
use App\Submissions\Models\Submission;
use App\Submissions\Models\SubmissionType;
use App\Submissions\Models\WorkflowStage;
use App\Submissions\Services\SubmissionWorkflowService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\View\View;

class SubmissionController extends Controller
{
    public function dashboard(): View
    {
        $metrics = [
            'total' => Submission::query()->count(),
            'draft' => Submission::query()->where('is_draft', true)->count(),
            'submitted' => Submission::query()->whereNotNull('submitted_at')->count(),
            'under_review' => Submission::query()->where('status', 'under_review')->count(),
            'selected' => Submission::query()->where('final_decision', 'selected')->count(),
            'rejected' => Submission::query()->where('final_decision', 'rejected')->count(),
        ];
        $byType = SubmissionType::query()->withCount('submissions')->orderByDesc('submissions_count')->limit(8)->get();

        return view('admin.submissions.dashboard', compact('metrics', 'byType'));
    }

    public function index(Request $request): View
    {
        $submissions = Submission::query()
            ->with(['type', 'currentStage', 'applicant'])
            ->withCount(['reviewAssignments', 'reviews'])
            ->when($request->filled('type'), fn ($query) => $query->where('submission_type_id', $request->integer('type')))
            ->when($request->filled('stage'), fn ($query) => $query->where('current_stage_id', $request->integer('stage')))
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->when($request->filled('search'), fn ($query) => $query->where(
                fn ($nested) => $nested->where('reference_number', 'like', '%'.$request->string('search').'%')
                    ->orWhere('title', 'like', '%'.$request->string('search').'%'),
            ))
            ->latest('updated_at')
            ->paginate(25)
            ->withQueryString();
        $types = SubmissionType::query()->orderBy('name')->get();
        $stages = WorkflowStage::query()->orderBy('sort_order')->get();

        return view('admin.submissions.index', compact('submissions', 'types', 'stages'));
    }

    public function kanban(): View
    {
        $stages = WorkflowStage::query()->with(['submissions' => fn ($query) => $query->where('is_draft', false)->latest()])->orderBy('sort_order')->get();

        return view('admin.submissions.kanban', compact('stages'));
    }

    public function show(Submission $submission): View
    {
        $submission->load([
            'type', 'currentStage', 'answers.question', 'people', 'files', 'reviewAssignments.reviewer',
            'applicant', 'reviews.assignment.reviewer', 'revisions', 'decision', 'activities', 'speakerLinks.speaker',
        ]);
        $availableStages = $submission->type->workflowStages()->orderBy('sort_order')->get();
        $reviewers = Reviewer::query()->where('status', 'active')->orderBy('name')->get();

        return view('admin.submissions.show', compact('submission', 'availableStages', 'reviewers'));
    }

    public function move(Request $request, Submission $submission, SubmissionWorkflowService $workflow): RedirectResponse
    {
        $data = $request->validate([
            'stage_id' => ['required', 'integer'],
            'note' => ['nullable', 'string', 'max:5000'],
            'override_reason' => ['nullable', 'string', 'max:5000'],
        ]);
        $stage = $submission->type->workflowStages()->findOrFail($data['stage_id']);
        $workflow->move($submission, $stage, $data['note'] ?? null, $data['override_reason'] ?? null);

        return back()->with('success', 'Stage updated.');
    }

    public function export(Request $request)
    {
        $name = 'submissions-'.now()->format('Ymd-His').'.csv';

        return Response::streamDownload(function (): void {
            $output = fopen('php://output', 'w');
            fputcsv($output, ['Reference', 'Title', 'Type', 'Status', 'Decision', 'Submitted']);
            Submission::query()->with('type')->orderBy('id')->chunk(500, function ($rows) use ($output): void {
                foreach ($rows as $submission) {
                    fputcsv($output, array_map(fn ($value) => $this->escapeFormula($value), [
                        $submission->reference_number,
                        $submission->title,
                        $submission->type->name,
                        $submission->status->value,
                        $submission->final_decision,
                        optional($submission->submitted_at)->toIso8601String(),
                    ]));
                }
            });
            fclose($output);
        }, $name, ['Content-Type' => 'text/csv']);
    }

    private function escapeFormula(mixed $value): string
    {
        $value = (string) $value;

        return preg_match('/^[=+\\-@]/', $value) ? "'{$value}" : $value;
    }
}
