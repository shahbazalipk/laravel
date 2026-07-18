<?php

namespace App\Http\Controllers\PublicRegistration;

use App\Forms\Enums\FormAudience;
use App\Forms\Models\CustomFormResponse;
use App\Forms\Services\DynamicFormValidator;
use App\Forms\Services\FormResolver;
use App\Forms\Services\FormResponseService;
use App\Http\Requests\PublicRegistration\SaveInformationStepRequest;
use App\Registration\Enums\RegistrationWizardStep;
use App\Registration\Services\CompleteRegistrationFromDraft;
use App\Registration\Services\OnlineRegistrationContext;
use App\Registration\Services\RegistrationDraftService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use InvalidArgumentException;

class InformationStepController extends WizardController
{
    public function __construct(
        OnlineRegistrationContext $context,
        RegistrationDraftService $drafts,
        private CompleteRegistrationFromDraft $completion,
        private FormResolver $forms,
        private FormResponseService $formResponses,
        private DynamicFormValidator $formValidator
    ) {
        parent::__construct($context, $drafts);
    }

    public function show(Request $request, string $slug): View|RedirectResponse
    {
        [$event, $eventUrl] = $this->bootContext($slug);

        try {
            $draft = $this->requireDraft($request, $event);
            $this->drafts->assertCanAccessStep($draft, RegistrationWizardStep::Information);
        } catch (InvalidArgumentException $exception) {
            if (isset($draft)) {
                return $this->redirectToStep($slug, $draft->current_step, $draft)
                    ->with('error', $exception->getMessage());
            }

            return redirect()
                ->route('online.registration.step.email', ['slug' => $slug])
                ->with('error', $exception->getMessage());
        }

        if ($redirect = $this->ensureDraftUrl($request, $slug, $draft, RegistrationWizardStep::Information)) {
            return $redirect;
        }

        $customForms = $this->forms->activeForAudience(FormAudience::Registration);
        $existingResponses = $draft->customFormResponses()
            ->whereIn('custom_form_id', $customForms->modelKeys())
            ->with('answers.files')
            ->get()
            ->keyBy('custom_form_id');

        return $this->wizardView(
            'online.wizard.information',
            $event,
            $eventUrl,
            $slug,
            $draft,
            RegistrationWizardStep::Information,
            [
                'industries' => $this->context->industries($event),
                'customForms' => $customForms,
                'customFormResponses' => $existingResponses,
            ]
        );
    }

    public function store(SaveInformationStepRequest $request, string $slug): RedirectResponse
    {
        [$event] = $this->bootContext($slug);

        try {
            $draft = $this->requireDraft($request, $event);
            $this->drafts->assertCanAccessStep($draft, RegistrationWizardStep::Information);

            $customForms = $this->forms->activeForAudience(FormAudience::Registration);
            $submittedForms = $request->all('custom_forms')['custom_forms'] ?? [];

            $draft = DB::transaction(function () use (
                $request,
                $draft,
                $customForms,
                $submittedForms
            ) {
                $lockedDraft = $draft->newQuery()
                    ->whereKey($draft->getKey())
                    ->lockForUpdate()
                    ->firstOrFail();
                $existingResponses = $lockedDraft->customFormResponses()
                    ->whereIn('custom_form_id', $customForms->modelKeys())
                    ->with('answers.files')
                    ->get()
                    ->keyBy('custom_form_id');

                $customFormErrors = [];
                foreach ($customForms as $form) {
                    $answers = $submittedForms[$form->public_id] ?? [];
                    $existing = $existingResponses->get($form->id);

                    try {
                        $this->formValidator->validate(
                            $form,
                            is_array($answers) ? $answers : [],
                            $existing instanceof CustomFormResponse ? $existing : null
                        );
                    } catch (ValidationException $exception) {
                        foreach ($exception->errors() as $key => $errors) {
                            $customFormErrors["custom_forms.{$form->public_id}.{$key}"] = $errors;
                        }
                    }
                }

                if ($customFormErrors !== []) {
                    throw ValidationException::withMessages($customFormErrors);
                }

                $data = $request->validated();
                $profilePath = $this->completion->storeProfileImage($request, $lockedDraft);
                if ($profilePath) {
                    $data['profile_picture'] = $profilePath;
                }
                unset($data['profile_picture_data']);

                foreach ($customForms as $form) {
                    $answers = $submittedForms[$form->public_id] ?? [];
                    $existing = $existingResponses->get($form->id);

                    $this->formResponses->submit(
                        $form,
                        $lockedDraft,
                        is_array($answers) ? $answers : [],
                        ['source' => 'online_registration'],
                        $existing instanceof CustomFormResponse ? $existing : null
                    );
                }

                return $this->drafts->savePayload($lockedDraft, $data, RegistrationWizardStep::Confirmation);
            });
        } catch (ValidationException $exception) {
            return back()->withInput()->withErrors($exception->errors());
        } catch (InvalidArgumentException $exception) {
            return back()->withInput()->with('error', $exception->getMessage());
        }

        return $this->redirectToStep($slug, RegistrationWizardStep::Confirmation, $draft)
            ->with('success', 'Your details were saved. Review and confirm your registration.');
    }
}
