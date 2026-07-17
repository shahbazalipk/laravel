<?php

namespace App\Http\Controllers\PublicRegistration;

use App\Http\Requests\PublicRegistration\CompleteRegistrationRequest;
use App\Models\Industry;
use App\Models\RegistrationCategory;
use App\Registration\Enums\RegistrationWizardStep;
use App\Registration\Services\CompleteRegistrationFromDraft;
use App\Registration\Services\OnlineRegistrationContext;
use App\Registration\Services\RegistrationDraftService;
use App\Services\RegistrationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use InvalidArgumentException;

class ConfirmationStepController extends WizardController
{
    public function __construct(
        OnlineRegistrationContext $context,
        RegistrationDraftService $drafts,
        private CompleteRegistrationFromDraft $completion,
        private RegistrationService $registrationService
    ) {
        parent::__construct($context, $drafts);
    }

    public function show(Request $request, string $slug): View|RedirectResponse
    {
        [$event, $eventUrl] = $this->bootContext($slug);

        try {
            $draft = $this->requireDraft($request, $event);
            $this->drafts->assertCanAccessStep($draft, RegistrationWizardStep::Confirmation);
        } catch (InvalidArgumentException $exception) {
            if (isset($draft)) {
                return $this->redirectToStep($slug, RegistrationWizardStep::Email, $draft)
                    ->with('error', $exception->getMessage());
            }

            return redirect()
                ->route('online.registration.step.email', ['slug' => $slug])
                ->with('error', $exception->getMessage());
        }

        if ($redirect = $this->ensureDraftUrl($request, $slug, $draft, RegistrationWizardStep::Confirmation)) {
            return $redirect;
        }

        $payload = $draft->payload ?? [];
        $category = !empty($payload['registration_category_id'])
            ? RegistrationCategory::query()->find($payload['registration_category_id'])
            : null;
        $industry = !empty($payload['industry_id'])
            ? Industry::query()->find($payload['industry_id'])
            : null;
        $pricing = $payload['pricing'] ?? ($category
            ? $this->registrationService->calculatePrice($category, $event)
            : null);

        return $this->wizardView(
            'online.wizard.confirmation',
            $event,
            $eventUrl,
            $slug,
            $draft,
            RegistrationWizardStep::Confirmation,
            [
                'category' => $category,
                'industry' => $industry,
                'pricing' => $pricing,
            ]
        );
    }

    public function store(CompleteRegistrationRequest $request, string $slug): RedirectResponse
    {
        [$event] = $this->bootContext($slug);

        try {
            $draft = $this->requireDraft($request, $event);
            $this->drafts->savePayload($draft, [
                'terms_accepted' => true,
            ]);
            $registration = $this->completion->execute($draft->fresh(), $event, $request);
        } catch (InvalidArgumentException $exception) {
            return redirect()
                ->route('online.registration.step.email', ['slug' => $slug])
                ->withInput()
                ->with('error', $exception->getMessage());
        }

        $message = ((float) $registration->total_amount) <= 0
            ? 'Registration confirmed successfully! Your registration is complete.'
            : 'Registration submitted successfully! Please complete payment to confirm your registration.';

        return redirect()
            ->route('registration.confirmation', $registration->hash)
            ->with('success', $message);
    }
}
