<?php

namespace App\Http\Controllers\PublicSales;

use App\Http\Controllers\Controller;
use App\Sales\Models\InquiryForm;
use App\Sales\Services\InquirySubmissionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InquiryFormController extends Controller
{
    public function __construct(private readonly InquirySubmissionService $submissions)
    {
    }

    public function show(string $slug): View
    {
        $form = InquiryForm::query()
            ->where('slug', $slug)
            ->with(['fields' => fn ($q) => $q->where('is_active', true)->orderBy('sort_order')])
            ->firstOrFail();

        abort_unless($form->status->isPubliclyAvailable(), 404);

        return view('sales.public.form', compact('form'));
    }

    public function store(Request $request, string $slug): RedirectResponse|View
    {
        $form = InquiryForm::query()
            ->where('slug', $slug)
            ->with('fields')
            ->firstOrFail();

        abort_unless($form->status->isPubliclyAvailable(), 404);

        $submission = $this->submissions->submit($form, [
            'answers' => $request->input('answers', []),
            'submitter_name' => $request->input('submitter_name'),
            'submitter_email' => $request->input('submitter_email'),
            'submitter_phone' => $request->input('submitter_phone'),
            'company_name' => $request->input('company_name'),
            'source_url' => $request->input('source_url'),
            'files' => $request->allFiles()['answers'] ?? [],
        ], $request);

        if ($form->redirect_url) {
            return redirect()->away($form->redirect_url);
        }

        return view('sales.public.form', [
            'form' => $form,
            'submitted' => true,
            'submission' => $submission,
        ]);
    }
}
