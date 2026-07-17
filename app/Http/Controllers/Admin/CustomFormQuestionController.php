<?php

namespace App\Http\Controllers\Admin;

use App\Forms\Models\CustomForm;
use App\Forms\Models\CustomFormQuestion;
use App\Forms\Services\FormDefinitionService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Forms\ReorderQuestionRequest;
use App\Http\Requests\Admin\Forms\StoreQuestionRequest;
use App\Http\Requests\Admin\Forms\UpdateQuestionRequest;
use Illuminate\Http\RedirectResponse;

class CustomFormQuestionController extends Controller
{
    public function __construct(private readonly FormDefinitionService $service)
    {
    }

    public function store(StoreQuestionRequest $request, CustomForm $custom_form): RedirectResponse
    {
        $this->service->createQuestion($custom_form, $request->validated());

        return back()->with('success', 'Question added.');
    }

    public function update(
        UpdateQuestionRequest $request,
        CustomForm $custom_form,
        CustomFormQuestion $question
    ): RedirectResponse {
        $this->service->updateQuestion($custom_form, $question, $request->validated());

        return back()->with('success', 'Question updated.');
    }

    public function destroy(CustomForm $custom_form, CustomFormQuestion $question): RedirectResponse
    {
        $this->service->deleteQuestion($custom_form, $question);

        return back()->with('success', 'Question deleted.');
    }

    public function reorder(
        ReorderQuestionRequest $request,
        CustomForm $custom_form,
        CustomFormQuestion $question
    ): RedirectResponse {
        $this->service->reorderQuestion($custom_form, $question, $request->validated('direction'));

        return back()->with('success', 'Question order updated.');
    }
}
