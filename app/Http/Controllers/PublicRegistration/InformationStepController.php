<?php

namespace App\Http\Controllers\PublicRegistration;

use App\Http\Requests\PublicRegistration\SaveInformationStepRequest;
use App\Registration\Enums\RegistrationWizardStep;
use App\Registration\Services\CompleteRegistrationFromDraft;
use App\Registration\Services\OnlineRegistrationContext;
use App\Registration\Services\RegistrationDraftService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use InvalidArgumentException;

class InformationStepController extends WizardController
{
    public function __construct(
        OnlineRegistrationContext $context,
        RegistrationDraftService $drafts,
        private CompleteRegistrationFromDraft $completion
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
                return $this->redirectToStep($slug, RegistrationWizardStep::Email, $draft)
                    ->with('error', $exception->getMessage());
            }

            return redirect()
                ->route('online.registration.step.email', ['slug' => $slug])
                ->with('error', $exception->getMessage());
        }

        if ($redirect = $this->ensureDraftUrl($request, $slug, $draft, RegistrationWizardStep::Information)) {
            return $redirect;
        }

        return $this->wizardView(
            'online.wizard.information',
            $event,
            $eventUrl,
            $slug,
            $draft,
            RegistrationWizardStep::Information,
            [
                'industries' => $this->context->industries($event),
            ]
        );
    }

    public function store(SaveInformationStepRequest $request, string $slug): RedirectResponse
    {
        [$event] = $this->bootContext($slug);

        try {
            $draft = $this->requireDraft($request, $event);
            $this->drafts->assertCanAccessStep($draft, RegistrationWizardStep::Information);

            $data = $request->validated();
            $profilePath = $this->completion->storeProfileImage($request, $draft);
            if ($profilePath) {
                $data['profile_picture'] = $profilePath;
            }
            unset($data['profile_picture_data']);

            $draft = $this->drafts->savePayload($draft, $data, RegistrationWizardStep::Category);
        } catch (InvalidArgumentException $exception) {
            return back()->withInput()->with('error', $exception->getMessage());
        }

        return $this->redirectToStep($slug, RegistrationWizardStep::Category, $draft)
            ->with('success', 'Your details were saved.');
    }
}
