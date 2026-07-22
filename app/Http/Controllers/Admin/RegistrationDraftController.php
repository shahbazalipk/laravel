<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PurgeRegistrationDraftRequest;
use App\Models\RegistrationCategory;
use App\Registration\Models\RegistrationDraft;
use App\Services\PurgeRegistrationDraft;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class RegistrationDraftController extends Controller
{
    public function show(RegistrationDraft $draft): View
    {
        if ($draft->isCompleted()) {
            abort(404);
        }

        $draft->load([
            'customFormResponses' => fn ($query) => $query
                ->where('status', 'submitted')
                ->orderBy('submitted_at')
                ->with([
                    'form',
                    'answers.files',
                    'answers.question.options',
                ]),
        ]);

        $categoryId = (int) $draft->payloadValue('registration_category_id');
        $category = $categoryId
            ? RegistrationCategory::query()->find($categoryId)
            : null;

        return view('admin.registration-drafts.show', [
            'draft' => $draft,
            'payload' => $draft->payload ?? [],
            'category' => $category,
        ]);
    }

    public function destroy(
        PurgeRegistrationDraftRequest $request,
        RegistrationDraft $draft,
        PurgeRegistrationDraft $purgeRegistrationDraft
    ): RedirectResponse {
        if ($draft->isCompleted()) {
            abort(404);
        }

        $reference = $draft->displayReference();
        $purgeRegistrationDraft->execute($draft);

        return redirect()
            ->route('admin.registrations.index', ['stage' => 'draft'])
            ->with('success', "Draft {$reference} was permanently deleted.");
    }
}
