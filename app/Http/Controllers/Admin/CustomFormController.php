<?php

namespace App\Http\Controllers\Admin;

use App\Forms\Enums\FormConditionAction;
use App\Forms\Enums\FormConditionOperator;
use App\Forms\Enums\FormQuestionType;
use App\Forms\Models\CustomForm;
use App\Forms\Services\FormDefinitionService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Forms\StoreCustomFormRequest;
use App\Http\Requests\Admin\Forms\UpdateCustomFormRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CustomFormController extends Controller
{
    public function __construct(private readonly FormDefinitionService $service)
    {
    }

    public function index(): View
    {
        $forms = CustomForm::query()
            ->withCount('questions')
            ->orderBy('name')
            ->get();

        $availableAudiences = $this->service->availableAudiences();

        return view('admin.custom-forms.index', compact('forms', 'availableAudiences'));
    }

    public function create(): View|RedirectResponse
    {
        $audiences = $this->service->availableAudiences();

        if ($audiences === []) {
            return redirect()->route('admin.custom-forms.index')
                ->with('error', 'Each audience already has a form. Open an existing form to edit it, or delete one first.');
        }

        return view('admin.custom-forms.create', compact('audiences'));
    }

    public function store(StoreCustomFormRequest $request): RedirectResponse
    {
        $form = $this->service->createForm($request->validated());

        return redirect()->route('admin.custom-forms.edit', $form)
            ->with('success', 'Custom form created. Add your first question.');
    }

    public function edit(CustomForm $custom_form): View
    {
        $custom_form->load([
            'questions.options',
            'questions.targetConditions.sourceQuestion',
        ]);

        return view('admin.custom-forms.edit', [
            'form' => $custom_form,
            'audiences' => [$custom_form->audience],
            'questionTypes' => FormQuestionType::cases(),
            'operators' => FormConditionOperator::cases(),
            'actions' => FormConditionAction::cases(),
            'audienceLocked' => true,
        ]);
    }

    public function update(UpdateCustomFormRequest $request, CustomForm $custom_form): RedirectResponse
    {
        $this->service->updateForm($custom_form, $request->validated());

        return back()->with('success', 'Form details updated.');
    }

    public function destroy(CustomForm $custom_form): RedirectResponse
    {
        $custom_form->delete();

        return redirect()->route('admin.custom-forms.index')
            ->with('success', 'Custom form deleted.');
    }
}
