<?php

namespace App\Http\Controllers\PublicRegistration;

use App\Http\Requests\PublicRegistration\StartEmailStepRequest;
use App\Http\Requests\PublicRegistration\VerifyOtpRequest;
use App\Registration\Enums\RegistrationWizardStep;
use App\Registration\Services\OnlineRegistrationContext;
use App\Registration\Services\RegistrationDraftService;
use App\Registration\Services\RegistrationOtpService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use InvalidArgumentException;

class EmailStepController extends WizardController
{
    public function __construct(
        OnlineRegistrationContext $context,
        RegistrationDraftService $drafts,
        private RegistrationOtpService $otp
    ) {
        parent::__construct($context, $drafts);
    }

    public function show(Request $request, string $slug): View|RedirectResponse
    {
        [$event, $eventUrl] = $this->bootContext($slug);
        $draft = $this->draftFromRequest($request, $event);

        if ($draft) {
            $awaitingVerification = $event->email_verification_required && !$draft->isEmailVerified();
            $targetStep = ($draft->current_step !== RegistrationWizardStep::Email && !$awaitingVerification)
                ? $draft->current_step
                : RegistrationWizardStep::Email;

            // Always keep the encrypted draft key in the URL once a draft exists.
            if (!$this->draftKeyFromRequest($request) || $targetStep !== RegistrationWizardStep::Email) {
                return $this->redirectToStep($slug, $targetStep, $draft);
            }

            return $this->wizardView(
                'online.wizard.email',
                $event,
                $eventUrl,
                $slug,
                $draft,
                RegistrationWizardStep::Email,
                ['awaitingVerification' => $awaitingVerification]
            );
        }

        return $this->wizardView(
            'online.wizard.email',
            $event,
            $eventUrl,
            $slug,
            null,
            RegistrationWizardStep::Email,
            ['awaitingVerification' => false]
        );
    }

    public function store(StartEmailStepRequest $request, string $slug): RedirectResponse
    {
        [$event, $eventUrl] = $this->bootContext($slug);

        $email = $this->drafts->normalizeEmail($request->validated('email'));
        $existing = $this->draftFromRequest($request, $event);

        if ($existing && $existing->email === $email && !$existing->isExpired()) {
            $draft = $existing;
            $plainToken = $request->cookie(RegistrationDraftService::COOKIE_NAME);
            if (!$plainToken) {
                $plainToken = $this->drafts->rotateToken($draft);
            }
        } else {
            [$draft, $plainToken] = $this->drafts->start($event, $eventUrl, $email);
        }

        $this->drafts->queueResumeCookie($plainToken);

        if ($event->email_verification_required && !$draft->isEmailVerified()) {
            try {
                $resumeUrl = route('online.registration.resume', [
                    'slug' => $slug,
                    'token' => $plainToken,
                ]);
                $this->otp->issueAndSend($draft, $event, $resumeUrl);
            } catch (InvalidArgumentException $exception) {
                return back()->withInput()->with('error', $exception->getMessage());
            }

            return $this->redirectToStep($slug, RegistrationWizardStep::Email, $draft)
                ->with('success', 'We sent a verification code to your email. Enter it below to continue.');
        }

        $draft = $this->drafts->advanceTo($draft, RegistrationWizardStep::Category);

        return $this->redirectToStep($slug, RegistrationWizardStep::Category, $draft)
            ->with('success', 'Progress saved. Choose your registration category.');
    }

    public function verify(VerifyOtpRequest $request, string $slug): RedirectResponse
    {
        [$event] = $this->bootContext($slug);

        try {
            $draft = $this->requireDraft($request, $event);
            $this->otp->verify($draft, $request->validated('otp'));
            $draft = $this->drafts->advanceTo($draft->fresh(), RegistrationWizardStep::Category);
        } catch (InvalidArgumentException $exception) {
            return back()->withInput()->with('error', $exception->getMessage());
        }

        return $this->redirectToStep($slug, RegistrationWizardStep::Category, $draft)
            ->with('success', 'Email verified. Choose your registration category.');
    }

    public function resend(Request $request, string $slug): RedirectResponse
    {
        [$event] = $this->bootContext($slug);

        try {
            $draft = $this->requireDraft($request, $event);
            $plainToken = $request->cookie(RegistrationDraftService::COOKIE_NAME) ?: $this->drafts->rotateToken($draft);
            $this->drafts->queueResumeCookie($plainToken);
            $resumeUrl = route('online.registration.resume', [
                'slug' => $slug,
                'token' => $plainToken,
            ]);
            $this->otp->issueAndSend($draft, $event, $resumeUrl);
        } catch (InvalidArgumentException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return back()->with('success', 'A new verification code has been sent.');
    }

    public function resume(Request $request, string $slug, string $token): RedirectResponse
    {
        [$event] = $this->bootContext($slug);

        $draft = $this->drafts->findByPlainToken($token, $event);
        if (!$draft) {
            return redirect()
                ->route('online.registration.step.email', ['slug' => $slug])
                ->with('error', 'This resume link is invalid or expired. Please start again.');
        }

        try {
            $this->drafts->assertAccessible($draft, $event);
        } catch (InvalidArgumentException $exception) {
            return redirect()
                ->route('online.registration.step.email', ['slug' => $slug])
                ->with('error', $exception->getMessage());
        }

        $this->drafts->queueResumeCookie($token);

        if ($event->email_verification_required && !$draft->isEmailVerified()) {
            return $this->redirectToStep($slug, RegistrationWizardStep::Email, $draft)
                ->with('success', 'Welcome back. Enter your verification code to continue.');
        }

        return $this->redirectToStep($slug, $draft->current_step, $draft)
            ->with('success', 'Welcome back. Your progress has been restored.');
    }
}
