<?php

namespace App\Http\Controllers\PublicRegistration;

use App\Forms\Enums\FormAudience;
use App\Forms\Models\CustomFormResponse;
use App\Forms\Services\DynamicFormValidator;
use App\Forms\Services\FormResolver;
use App\Forms\Services\FormResponseService;
use App\Http\Requests\PublicRegistration\SinglePageRegistrationRequest;
use App\Http\Requests\PublicRegistration\VerifyOtpRequest;
use App\Models\Event;
use App\Models\EventUrl;
use App\Models\RegistrationCategory;
use App\Registration\Enums\RegistrationWizardStep;
use App\Registration\Models\RegistrationDraft;
use App\Registration\Services\CompleteRegistrationFromDraft;
use App\Registration\Services\OnlineRegistrationContext;
use App\Registration\Services\RegistrationDraftService;
use App\Registration\Services\RegistrationOtpService;
use App\Services\PromoCodeService;
use App\Services\RegistrationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use InvalidArgumentException;

class SinglePageRegistrationController extends WizardController
{
    public function __construct(
        OnlineRegistrationContext $context,
        RegistrationDraftService $drafts,
        private CompleteRegistrationFromDraft $completion,
        private RegistrationService $registrationService,
        private PromoCodeService $promoCodes,
        private FormResolver $forms,
        private FormResponseService $formResponses,
        private DynamicFormValidator $formValidator,
        private RegistrationOtpService $otp,
    ) {
        parent::__construct($context, $drafts);
    }

    public function show(Request $request, string $slug): View|RedirectResponse
    {
        [$event, $eventUrl] = $this->bootSinglePage($slug);
        $draft = $this->draftFromRequest($request, $event);
        $awaitingVerification = $draft
            && $event->email_verification_required
            && ! $draft->isEmailVerified();

        if ($draft && ! $this->draftKeyFromRequest($request)) {
            return redirect()->route('online.registration.single.reg', [
                'slug' => $slug,
                'reg' => $this->drafts->encodeUrlKey($draft),
            ]);
        }

        return $this->singlePageView($event, $eventUrl, $slug, $draft, $awaitingVerification);
    }

    public function store(SinglePageRegistrationRequest $request, string $slug): RedirectResponse
    {
        [$event, $eventUrl] = $this->bootSinglePage($slug);

        try {
            $draft = $this->persistSinglePageDraft($request, $event, $eventUrl);

            if ($event->email_verification_required && ! $draft->isEmailVerified()) {
                $plainToken = $request->cookie(RegistrationDraftService::COOKIE_NAME)
                    ?: $this->drafts->rotateToken($draft);
                $this->drafts->queueResumeCookie($plainToken);

                $resumeUrl = route('online.registration.resume', [
                    'slug' => $slug,
                    'token' => $plainToken,
                ]);
                $this->otp->issueAndSend($draft, $event, $resumeUrl);

                return redirect()
                    ->route('online.registration.single.reg', [
                        'slug' => $slug,
                        'reg' => $this->drafts->encodeUrlKey($draft),
                    ])
                    ->with('success', 'We sent a verification code to your email. Enter it below to complete registration.');
            }

            $registration = $this->completion->execute($draft->fresh(), $event, $request);
        } catch (ValidationException $exception) {
            return back()->withInput()->withErrors($exception->errors());
        } catch (InvalidArgumentException $exception) {
            return back()->withInput()->with('error', $exception->getMessage());
        }

        $message = ((float) $registration->total_amount) <= 0
            ? 'Registration confirmed successfully! Your registration is complete.'
            : 'Registration and payment screenshot submitted successfully. We’ll update you after payment verification.';

        return redirect()
            ->route('registration.confirmation', $registration->hash)
            ->with('success', $message);
    }

    public function verify(VerifyOtpRequest $request, string $slug): RedirectResponse
    {
        [$event] = $this->bootSinglePage($slug);

        try {
            $draft = $this->requireDraft($request, $event);
            $this->otp->verify($draft, $request->validated('otp'));
            $draft = $this->drafts->advanceTo($draft->fresh(), RegistrationWizardStep::Confirmation);
            $registration = $this->completion->execute($draft->fresh(), $event, $request);
        } catch (InvalidArgumentException $exception) {
            return back()->withInput()->with('error', $exception->getMessage());
        }

        $message = ((float) $registration->total_amount) <= 0
            ? 'Registration confirmed successfully! Your registration is complete.'
            : 'Registration and payment screenshot submitted successfully. We’ll update you after payment verification.';

        return redirect()
            ->route('registration.confirmation', $registration->hash)
            ->with('success', $message);
    }

    public function resend(Request $request, string $slug): RedirectResponse
    {
        [$event] = $this->bootSinglePage($slug);

        try {
            $draft = $this->requireDraft($request, $event);
            $plainToken = $request->cookie(RegistrationDraftService::COOKIE_NAME)
                ?: $this->drafts->rotateToken($draft);
            $this->drafts->queueResumeCookie($plainToken);

            $resumeUrl = route('online.registration.resume', [
                'slug' => $slug,
                'token' => $plainToken,
            ]);
            $this->otp->issueAndSend($draft, $event, $resumeUrl);
        } catch (InvalidArgumentException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return back()->with('success', 'A new verification code was sent to your email.');
    }

    public function previewPromo(Request $request, string $slug): JsonResponse
    {
        [$event, $eventUrl] = $this->bootSinglePage($slug);

        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'registration_category_id' => ['required', 'integer'],
            'promo_code' => ['required', 'string', 'max:50'],
        ]);

        $category = RegistrationCategory::query()->find($validated['registration_category_id']);
        if (! $category) {
            throw ValidationException::withMessages([
                'registration_category_id' => 'Select a valid registration category.',
            ]);
        }

        try {
            $this->context->assertCategoryAllowed($category, $event, $eventUrl);

            $pricing = $this->promoCodes->priceWithOptionalPromo(
                $category,
                $event,
                $validated['promo_code'],
                $this->drafts->normalizeEmail($validated['email']),
            );
        } catch (ValidationException $exception) {
            return response()->json([
                'message' => collect($exception->errors())->flatten()->first() ?: 'Invalid promo code.',
                'errors' => $exception->errors(),
            ], 422);
        } catch (InvalidArgumentException $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
                'errors' => ['registration_category_id' => [$exception->getMessage()]],
            ], 422);
        }

        if (empty($pricing['promo_code'])) {
            return response()->json([
                'message' => 'Enter a valid promo code.',
                'errors' => ['promo_code' => ['Enter a valid promo code.']],
            ], 422);
        }

        return response()->json([
            'pricing' => [
                'base_price' => (float) $pricing['base_price'],
                'tax_amount' => (float) $pricing['tax_amount'],
                'total_amount' => (float) $pricing['total_amount'],
                'currency' => $pricing['currency'],
                'discount_amount' => (float) ($pricing['discount_amount'] ?? 0),
                'promo_code' => $pricing['promo_code'],
            ],
        ]);
    }

    /**
     * @return array{0: Event, 1: EventUrl}
     */
    private function bootSinglePage(string $slug): array
    {
        [$event, $eventUrl] = $this->bootContext($slug);

        abort_unless($eventUrl->usesSinglePageRegistration(), 404);

        return [$event, $eventUrl];
    }

    private function persistSinglePageDraft(
        SinglePageRegistrationRequest $request,
        Event $event,
        EventUrl $eventUrl,
    ): RegistrationDraft {
        $email = $this->drafts->normalizeEmail($request->validated('email'));
        $existing = $this->draftFromRequest($request, $event);

        if ($existing && $existing->email === $email && ! $existing->isExpired()) {
            $draft = $existing;
            $plainToken = $request->cookie(RegistrationDraftService::COOKIE_NAME);
            if (! $plainToken) {
                $plainToken = $this->drafts->rotateToken($draft);
            }
        } else {
            [$draft, $plainToken] = $this->drafts->start($event, $eventUrl, $email);
        }

        $this->drafts->queueResumeCookie($plainToken);

        $category = RegistrationCategory::query()->findOrFail($request->validated('registration_category_id'));
        $this->context->assertCategoryAllowed($category, $event, $eventUrl);

        $categoryPayload = [
            'registration_category_id' => $category->id,
            'category_password' => $request->validated('category_password'),
            'membership_id' => $request->validated('membership_id'),
            'professional_student_id' => $request->validated('professional_student_id'),
        ];
        $categoryErrors = $this->registrationService->validateCategory($category, $categoryPayload);
        if ($categoryErrors !== []) {
            throw ValidationException::withMessages($categoryErrors);
        }

        $pricing = $this->promoCodes->priceWithOptionalPromo(
            $category,
            $event,
            $request->validated('promo_code'),
            $email,
        );
        $customForms = $this->forms->activeForAudience(FormAudience::Registration);
        $submittedForms = $request->all('custom_forms')['custom_forms'] ?? [];

        return DB::transaction(function () use (
            $request,
            $draft,
            $categoryPayload,
            $pricing,
            $customForms,
            $submittedForms,
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

            $data = collect($request->validated())
                ->except(['profile_picture', 'profile_picture_data', 'email'])
                ->all();
            $data['terms_accepted'] = true;
            $data['pricing'] = $pricing;
            $data = array_merge($data, $categoryPayload);

            $profilePath = $this->completion->storeProfileImage($request, $lockedDraft);
            if ($profilePath) {
                $data['profile_picture'] = $profilePath;
            }

            foreach ($customForms as $form) {
                $answers = $submittedForms[$form->public_id] ?? [];
                $existing = $existingResponses->get($form->id);

                $this->formResponses->submit(
                    $form,
                    $lockedDraft,
                    is_array($answers) ? $answers : [],
                    ['source' => 'online_registration_single_page'],
                    $existing instanceof CustomFormResponse ? $existing : null
                );
            }

            return $this->drafts->savePayload($lockedDraft, $data, RegistrationWizardStep::Confirmation);
        });
    }

    private function singlePageView(
        Event $event,
        EventUrl $eventUrl,
        string $slug,
        ?RegistrationDraft $draft,
        bool $awaitingVerification,
    ): View {
        $customForms = $this->forms->activeForAudience(FormAudience::Registration);
        $existingResponses = $draft
            ? $draft->customFormResponses()
                ->whereIn('custom_form_id', $customForms->modelKeys())
                ->with('answers.files')
                ->get()
                ->keyBy('custom_form_id')
            : collect();

        $reg = $draft ? $this->drafts->encodeUrlKey($draft) : null;
        $eventUrlSponsors = $eventUrl->sponsors()->active()->visibleOnline()->ordered()->get();
        $eventUrlPartners = $eventUrl->partners()->active()->visibleOnline()->ordered()->get();

        $storeRoute = $reg
            ? route('online.registration.single.reg.store', ['slug' => $slug, 'reg' => $reg])
            : route('online.registration.single.store', ['slug' => $slug]);

        return view('online.single-page', [
            'event' => $event,
            'eventUrl' => $eventUrl,
            'slug' => $slug,
            'draft' => $draft,
            'reg' => $reg,
            'payload' => $draft?->payload ?? [],
            'awaitingVerification' => $awaitingVerification,
            'categories' => $this->context->categoriesWithPricing($event, $eventUrl),
            'industries' => $this->context->industries($event),
            'customForms' => $customForms,
            'customFormResponses' => $existingResponses,
            'eventUrlSponsors' => $eventUrlSponsors,
            'eventUrlPartners' => $eventUrlPartners,
            'storeRoute' => $storeRoute,
            'promoPreviewUrl' => route('online.registration.single.promo.preview', ['slug' => $slug]),
            'verifyRoute' => $reg
                ? route('online.registration.single.reg.verify', ['slug' => $slug, 'reg' => $reg])
                : null,
            'resendRoute' => $reg
                ? route('online.registration.single.reg.resend', ['slug' => $slug, 'reg' => $reg])
                : null,
        ]);
    }
}
