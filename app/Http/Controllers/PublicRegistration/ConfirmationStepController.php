<?php

namespace App\Http\Controllers\PublicRegistration;

use App\Http\Requests\PublicRegistration\CompleteRegistrationRequest;
use App\Models\Industry;
use App\Models\RegistrationCategory;
use App\Registration\Enums\RegistrationWizardStep;
use App\Registration\Services\CompleteRegistrationFromDraft;
use App\Registration\Services\OnlineRegistrationContext;
use App\Registration\Services\RegistrationDraftService;
use App\Services\PromoCodeService;
use App\Services\RegistrationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use InvalidArgumentException;
use Throwable;

class ConfirmationStepController extends WizardController
{
    public function __construct(
        OnlineRegistrationContext $context,
        RegistrationDraftService $drafts,
        private CompleteRegistrationFromDraft $completion,
        private RegistrationService $registrationService,
        private PromoCodeService $promoCodes,
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
        $category = ! empty($payload['registration_category_id'])
            ? RegistrationCategory::query()->find($payload['registration_category_id'])
            : null;

        $industry = ! empty($payload['industry_id'])
            ? Industry::query()->find($payload['industry_id'])
            : null;

        $pricing = null;
        if ($category) {
            try {
                $pricing = $this->promoCodes->priceWithOptionalPromo(
                    $category,
                    $event,
                    old('promo_code', $payload['promo_code'] ?? null),
                    (string) $draft->email,
                );
            } catch (ValidationException) {
                $pricing = $this->registrationService->calculatePrice($category, $event);
                $pricing['discount_amount'] = 0.0;
                // Stale / invalid draft promo should not keep looking applied.
                if (! empty($payload['promo_code']) && ! session()->has('errors')) {
                    $this->drafts->savePayload($draft, ['promo_code' => null]);
                    $payload = $draft->fresh()->payload ?? [];
                }
            }
        }

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
                'payload' => $payload,
            ]
        );
    }

    public function applyPromo(Request $request, string $slug): RedirectResponse
    {
        [$event] = $this->bootContext($slug);

        $validated = $request->validate([
            'promo_code' => ['required', 'string', 'max:50'],
        ]);

        try {
            $draft = $this->requireDraft($request, $event);
            $this->drafts->assertCanAccessStep($draft, RegistrationWizardStep::Confirmation);

            $payload = $draft->payload ?? [];
            $category = ! empty($payload['registration_category_id'])
                ? RegistrationCategory::query()->find($payload['registration_category_id'])
                : null;

            if (! $category) {
                throw ValidationException::withMessages([
                    'promo_code' => 'Select a registration category before applying a promo code.',
                ]);
            }

            $pricing = $this->promoCodes->priceWithOptionalPromo(
                $category,
                $event,
                $validated['promo_code'],
                (string) $draft->email,
            );

            if (empty($pricing['promo_code'])) {
                throw ValidationException::withMessages([
                    'promo_code' => 'Enter a valid promo code.',
                ]);
            }

            $this->drafts->savePayload($draft, [
                'promo_code' => $pricing['promo_code'],
            ]);
        } catch (ValidationException $exception) {
            return back()->withInput()->withErrors($exception->errors());
        } catch (InvalidArgumentException $exception) {
            return redirect()
                ->route('online.registration.step.email', ['slug' => $slug])
                ->with('error', $exception->getMessage());
        }

        return back()->with('success', 'Promo code applied. Your total has been updated.');
    }

    public function removePromo(Request $request, string $slug): RedirectResponse
    {
        [$event] = $this->bootContext($slug);

        try {
            $draft = $this->requireDraft($request, $event);
            $this->drafts->assertCanAccessStep($draft, RegistrationWizardStep::Confirmation);
            $this->drafts->savePayload($draft, ['promo_code' => null]);
        } catch (InvalidArgumentException $exception) {
            return redirect()
                ->route('online.registration.step.email', ['slug' => $slug])
                ->with('error', $exception->getMessage());
        } catch (Throwable $exception) {
            return back()->with('error', 'Unable to remove the promo code. Please try again.');
        }

        return back()->with('success', 'Promo code removed. Original pricing restored.');
    }

    public function store(CompleteRegistrationRequest $request, string $slug): RedirectResponse
    {
        [$event] = $this->bootContext($slug);

        try {
            $draft = $this->requireDraft($request, $event);
            $promoCode = $request->validated('promo_code')
                ?? ($draft->payload['promo_code'] ?? null);
            $this->drafts->savePayload($draft, [
                'terms_accepted' => true,
                'promo_code' => $promoCode,
            ]);
            $registration = $this->completion->execute($draft->fresh(), $event, $request);
        } catch (ValidationException $exception) {
            return back()->withInput()->withErrors($exception->errors());
        } catch (InvalidArgumentException $exception) {
            return redirect()
                ->route('online.registration.step.email', ['slug' => $slug])
                ->withInput()
                ->with('error', $exception->getMessage());
        }

        $message = ((float) $registration->total_amount) <= 0
            ? 'Registration confirmed successfully! Your registration is complete.'
            : 'Registration and payment screenshot submitted successfully. We’ll update you after payment verification.';

        return redirect()
            ->route('registration.confirmation', $registration->hash)
            ->with('success', $message);
    }
}
