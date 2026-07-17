<?php

namespace App\Http\Controllers\PublicRegistration;

use App\Http\Requests\PublicRegistration\SaveCategoryStepRequest;
use App\Models\RegistrationCategory;
use App\Registration\Enums\RegistrationWizardStep;
use App\Registration\Services\OnlineRegistrationContext;
use App\Registration\Services\RegistrationDraftService;
use App\Services\RegistrationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use InvalidArgumentException;

class CategoryStepController extends WizardController
{
    public function __construct(
        OnlineRegistrationContext $context,
        RegistrationDraftService $drafts,
        private RegistrationService $registrationService
    ) {
        parent::__construct($context, $drafts);
    }

    public function show(Request $request, string $slug): View|RedirectResponse
    {
        [$event, $eventUrl] = $this->bootContext($slug);

        try {
            $draft = $this->requireDraft($request, $event);
            $this->drafts->assertCanAccessStep($draft, RegistrationWizardStep::Category);
        } catch (InvalidArgumentException $exception) {
            if (isset($draft)) {
                return $this->redirectToStep($slug, RegistrationWizardStep::Email, $draft)
                    ->with('error', $exception->getMessage());
            }

            return redirect()
                ->route('online.registration.step.email', ['slug' => $slug])
                ->with('error', $exception->getMessage());
        }

        if ($redirect = $this->ensureDraftUrl($request, $slug, $draft, RegistrationWizardStep::Category)) {
            return $redirect;
        }

        return $this->wizardView(
            'online.wizard.category',
            $event,
            $eventUrl,
            $slug,
            $draft,
            RegistrationWizardStep::Category,
            [
                'categories' => $this->context->categoriesWithPricing($event, $eventUrl),
            ]
        );
    }

    public function store(SaveCategoryStepRequest $request, string $slug): RedirectResponse
    {
        [$event, $eventUrl] = $this->bootContext($slug);

        try {
            $draft = $this->requireDraft($request, $event);
            $this->drafts->assertCanAccessStep($draft, RegistrationWizardStep::Category);

            $category = RegistrationCategory::query()->findOrFail($request->validated('registration_category_id'));
            $this->context->assertCategoryAllowed($category, $event, $eventUrl);

            $payload = array_merge($draft->payload ?? [], $request->validated());
            $errors = $this->registrationService->validateCategory($category, $payload);
            if (!empty($errors)) {
                return back()->withInput()->withErrors($errors);
            }

            $pricing = $this->registrationService->calculatePrice($category, $event);
            $draft = $this->drafts->savePayload($draft, array_merge($request->validated(), [
                'pricing' => $pricing,
            ]), RegistrationWizardStep::Confirmation);
        } catch (InvalidArgumentException $exception) {
            return back()->withInput()->with('error', $exception->getMessage());
        }

        return $this->redirectToStep($slug, RegistrationWizardStep::Confirmation, $draft)
            ->with('success', 'Category selected. Review and confirm your registration.');
    }
}
