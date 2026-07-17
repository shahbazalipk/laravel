<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RegistrationCategory;
use App\Registration\Models\RegistrationDraft;
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
}
