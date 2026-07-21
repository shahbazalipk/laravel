<?php

namespace App\Http\Controllers\PublicRegistration;

use App\Registration\Enums\RegistrationWizardStep;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class EntryController extends WizardController
{
    public function __invoke(string $slug): RedirectResponse
    {
        [$event, $eventUrl] = $this->bootContext($slug);

        if ($eventUrl->usesSinglePageRegistration()) {
            $draft = $this->draftFromRequest(request(), $event);

            if ($draft) {
                return redirect()->route('online.registration.single.reg', [
                    'slug' => $slug,
                    'reg' => $this->drafts->encodeUrlKey($draft),
                ]);
            }

            return redirect()->route('online.registration.single', ['slug' => $slug]);
        }

        $draft = $this->draftFromRequest(request(), $event);

        if ($draft) {
            if ($event->email_verification_required && ! $draft->isEmailVerified()) {
                return $this->redirectToStep($slug, RegistrationWizardStep::Email, $draft);
            }

            return $this->redirectToStep($slug, $draft->current_step, $draft);
        }

        return $this->redirectToStep($slug, RegistrationWizardStep::Email);
    }

    public function startNew(Request $request, string $slug): RedirectResponse
    {
        [$event, $eventUrl] = $this->bootContext($slug);
        $this->drafts->clearResumeCookie();
        $request->session()->forget('registration_resume_token');

        if ($eventUrl->usesSinglePageRegistration()) {
            return redirect()
                ->route('online.registration.single', ['slug' => $slug])
                ->with('success', 'Start a new registration below.');
        }

        return redirect()
            ->route('online.registration.step.email', ['slug' => $slug])
            ->with('success', 'Start a new registration below.');
    }
}
