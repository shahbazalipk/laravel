<?php

namespace App\Http\Controllers\Admin\Sales;

use App\Http\Controllers\Controller;
use App\Sales\Enums\SubmissionStatus;
use App\Sales\Models\InquiryForm;
use App\Sales\Models\InquirySubmission;
use App\Sales\Services\InquirySubmissionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class InquirySubmissionController extends Controller
{
    public function __construct(private readonly InquirySubmissionService $service)
    {
    }

    public function index(Request $request): View
    {
        $submissions = $this->service->list($request->only(['form_id', 'status', 'search']));
        $forms = InquiryForm::query()->orderBy('name')->get(['id', 'name', 'public_id']);

        return view('admin.sales.submissions.index', [
            'submissions' => $submissions,
            'forms' => $forms,
            'statuses' => SubmissionStatus::cases(),
        ]);
    }

    public function show(InquirySubmission $inquiry_submission): View
    {
        $inquiry_submission->load(['form.fields', 'files', 'deal']);

        return view('admin.sales.submissions.show', [
            'submission' => $inquiry_submission,
            'statuses' => SubmissionStatus::cases(),
        ]);
    }

    public function updateStatus(Request $request, InquirySubmission $inquiry_submission): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', 'string'],
            'internal_notes' => ['nullable', 'string', 'max:5000'],
            'assigned_admin_id' => ['nullable', 'integer'],
        ]);

        $status = SubmissionStatus::from($data['status']);
        $this->service->updateStatus($inquiry_submission, $status, $data['internal_notes'] ?? null);

        if (array_key_exists('assigned_admin_id', $data)) {
            $this->service->assign($inquiry_submission, $data['assigned_admin_id']);
        }

        return back()->with('success', 'Submission updated.');
    }

    public function convert(InquirySubmission $inquiry_submission): RedirectResponse
    {
        $submission = $this->service->convertToDeal($inquiry_submission);

        return redirect()->route('admin.sales.deals.show', $submission->deal)
            ->with('success', 'Submission converted to deal.');
    }

    public function destroy(InquirySubmission $inquiry_submission): RedirectResponse
    {
        $this->service->delete($inquiry_submission);

        return redirect()->route('admin.sales.submissions.index')
            ->with('success', 'Submission deleted.');
    }

    public function export(Request $request): StreamedResponse
    {
        $filters = $request->only(['form_id', 'status', 'search']);
        $filename = 'sales-submissions-'.now()->format('Y-m-d-His').'.csv';

        return response()->streamDownload(function () use ($filters) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Reference', 'Form', 'Status', 'Name', 'Email', 'Company', 'Submitted At']);

            InquirySubmission::query()
                ->with('form')
                ->when(! empty($filters['form_id']), fn ($q) => $q->where('sales_inquiry_form_id', $filters['form_id']))
                ->when(! empty($filters['status']), fn ($q) => $q->where('status', $filters['status']))
                ->when(! empty($filters['search']), function ($q) use ($filters) {
                    $search = $filters['search'];
                    $q->where(function ($inner) use ($search) {
                        $inner->where('reference', 'like', "%{$search}%")
                            ->orWhere('submitter_name', 'like', "%{$search}%")
                            ->orWhere('submitter_email', 'like', "%{$search}%")
                            ->orWhere('company_name', 'like', "%{$search}%");
                    });
                })
                ->orderByDesc('created_at')
                ->chunk(200, function ($chunk) use ($handle) {
                    foreach ($chunk as $submission) {
                        fputcsv($handle, [
                            $submission->reference,
                            $submission->form?->name,
                            $submission->status->value,
                            $submission->submitter_name,
                            $submission->submitter_email,
                            $submission->company_name,
                            optional($submission->created_at)->toDateTimeString(),
                        ]);
                    }
                });

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }
}
