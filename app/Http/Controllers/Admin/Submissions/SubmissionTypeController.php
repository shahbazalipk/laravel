<?php

namespace App\Http\Controllers\Admin\Submissions;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Submissions\StoreSubmissionTypeRequest;
use App\Submissions\Models\SubmissionType;
use App\Submissions\Services\SubmissionTypeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SubmissionTypeController extends Controller
{
    public function __construct(private SubmissionTypeService $types) {}

    public function index(): View
    {
        $types = SubmissionType::query()->withCount(['submissions', 'questions'])->latest()->paginate(15);

        return view('admin.submissions.types.index', compact('types'));
    }

    public function create(): View
    {
        return view('admin.submissions.types.form', ['type' => new SubmissionType]);
    }

    public function store(StoreSubmissionTypeRequest $request): RedirectResponse
    {
        $type = $this->types->create($request->validated());

        return redirect()->route('admin.submissions.types.edit', $type)->with('success', 'Submission type created.');
    }

    public function edit(SubmissionType $submissionType): View
    {
        $submissionType->load(['sections.questions.options', 'conditionalRules', 'workflowStages', 'scorecards.criteria']);

        return view('admin.submissions.types.form', ['type' => $submissionType]);
    }

    public function update(StoreSubmissionTypeRequest $request, SubmissionType $submissionType): RedirectResponse
    {
        $this->types->update($submissionType, $request->validated());

        return back()->with('success', 'Submission type updated.');
    }

    public function destroy(SubmissionType $submissionType): RedirectResponse
    {
        if ($submissionType->submissions()->exists()) {
            return back()->with('error', 'Archive types with submissions instead of deleting them.');
        }
        $submissionType->delete();

        return redirect()->route('admin.submissions.types.index')->with('success', 'Submission type deleted.');
    }

    public function duplicate(SubmissionType $submissionType): RedirectResponse
    {
        $copy = $this->types->duplicate($submissionType);

        return redirect()->route('admin.submissions.types.edit', $copy)->with('success', 'Submission type duplicated.');
    }

    public function publish(SubmissionType $submissionType): RedirectResponse
    {
        $this->types->publish($submissionType);

        return back()->with('success', 'Submission type published.');
    }
}
