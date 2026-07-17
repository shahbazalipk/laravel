<?php

namespace App\Registration\Services;

use App\Forms\Services\FormResponseService;
use App\Models\Event;
use App\Models\Registration;
use App\Models\RegistrationCategory;
use App\Models\RegistrationStatus;
use App\Payments\Services\RecordRegistrationPayment;
use App\Registration\Enums\RegistrationWizardStep;
use App\Registration\Models\RegistrationDraft;
use App\Services\RegistrationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;

class CompleteRegistrationFromDraft
{
    public function __construct(
        private RegistrationService $registrationService,
        private OnlineRegistrationContext $context,
        private RegistrationDraftService $drafts,
        private RecordRegistrationPayment $payments,
        private FormResponseService $formResponses
    ) {}

    public function execute(RegistrationDraft $draft, Event $event, Request $request): Registration
    {
        if ($draft->isCompleted() && $draft->registration_id) {
            return Registration::query()->findOrFail($draft->registration_id);
        }

        $this->drafts->assertAccessible($draft, $event);
        $this->drafts->assertCanAccessStep($draft, RegistrationWizardStep::Confirmation);

        if ($event->email_verification_required && ! $draft->isEmailVerified()) {
            throw new InvalidArgumentException('Please verify your email before completing registration.');
        }

        $payload = $draft->payload ?? [];

        if (empty($payload['terms_accepted'])) {
            throw new InvalidArgumentException('You must accept the terms and conditions.');
        }

        $categoryId = (int) ($payload['registration_category_id'] ?? 0);
        $category = RegistrationCategory::query()->findOrFail($categoryId);
        $eventUrl = $draft->eventUrl;

        $this->context->assertCategoryAllowed($category, $event, $eventUrl);

        $categoryErrors = $this->registrationService->validateCategory($category, $payload);
        if (! empty($categoryErrors)) {
            throw new InvalidArgumentException(reset($categoryErrors));
        }

        $pricing = $this->registrationService->calculatePrice($category, $event);

        return DB::transaction(function () use ($draft, $event, $request, $payload, $category, $pricing) {
            $locked = RegistrationDraft::query()
                ->whereKey($draft->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($locked->isCompleted() && $locked->registration_id) {
                return Registration::query()->findOrFail($locked->registration_id);
            }

            $paymentStatus = 'pending';
            $paymentDate = null;
            $registrationStatusId = null;

            if ((float) $pricing['total_amount'] <= 0) {
                $paymentStatus = 'paid';
                $paymentDate = now();
                $registrationStatusId = RegistrationStatus::query()
                    ->where('event_id', $event->id)
                    ->whereIn('name', ['Confirmed', 'Approved', 'Active'])
                    ->value('id');
            }

            $registrationData = [
                'event_id' => $event->id,
                'org_id' => $event->organization_id ?? $event->org_id ?? $locked->org_id,
                'email' => $locked->email,
                'first_name' => $payload['first_name'] ?? null,
                'last_name' => $payload['last_name'] ?? null,
                'phone' => $payload['phone'] ?? null,
                'job_title' => $payload['job_title'] ?? null,
                'company_name' => $payload['company_name'] ?? null,
                'industry_id' => $payload['industry_id'] ?? null,
                'registration_category_id' => $category->id,
                'registration_type' => 'individual',
                'registration_status_id' => $registrationStatusId,
                'profile_picture' => $payload['profile_picture'] ?? null,
                'category_password' => $payload['category_password'] ?? null,
                'membership_id' => $payload['membership_id'] ?? null,
                'professional_student_id' => $payload['professional_student_id'] ?? null,
                'base_price' => $pricing['base_price'],
                'tax_amount' => $pricing['tax_amount'],
                'total_amount' => $pricing['total_amount'],
                'currency' => $pricing['currency'],
                'payment_status' => $paymentStatus,
                'payment_date' => $paymentDate,
                'terms_accepted' => true,
                'terms_accepted_at' => now(),
                'email_verified' => $locked->isEmailVerified(),
                'email_verified_at' => $locked->email_verified_at,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'registration_source' => 'online',
            ];

            unset($registrationData['category_password']);

            $registration = $this->registrationService->createRegistration($registrationData);
            $this->formResponses->promoteDraftRespondent($locked, $registration);

            if ($paymentStatus === 'paid' && (float) $pricing['total_amount'] <= 0) {
                // Free registrations stay ledger-compatible with an opening paid balance of zero.
                // No ledger row is required for a zero-amount settlement.
            }

            $locked->forceFill([
                'registration_id' => $registration->id,
                'completed_at' => now(),
                'current_step' => RegistrationWizardStep::Confirmation,
            ])->save();

            $this->drafts->clearResumeCookie();

            return $registration;
        });
    }

    public function storeProfileImage(Request $request, RegistrationDraft $draft): ?string
    {
        $existing = $draft->payloadValue('profile_picture');

        if ($request->hasFile('profile_picture')) {
            if ($existing) {
                Storage::disk('public')->delete($existing);
            }

            $file = $request->file('profile_picture');
            $filename = time().'_'.uniqid('', true).'.'.$file->getClientOriginalExtension();

            return $file->storeAs('registrations/drafts/'.$draft->public_id, $filename, 'public');
        }

        if ($request->filled('profile_picture_data')) {
            if ($existing) {
                Storage::disk('public')->delete($existing);
            }

            $imageData = (string) $request->input('profile_picture_data');
            if (! preg_match('/^data:image\/(png|jpe?g);base64,/', $imageData, $matches)) {
                throw new InvalidArgumentException('Invalid profile picture data.');
            }

            $extension = str_contains(strtolower($matches[1]), 'jp') ? 'jpg' : 'png';
            $binary = base64_decode(preg_replace('/^data:image\/\w+;base64,/', '', $imageData), true);
            if ($binary === false || strlen($binary) > 2 * 1024 * 1024) {
                throw new InvalidArgumentException('Profile picture must be a valid image under 2MB.');
            }

            $filename = time().'_'.uniqid('', true).'.'.$extension;
            $path = 'registrations/drafts/'.$draft->public_id.'/'.$filename;
            Storage::disk('public')->put($path, $binary);

            return $path;
        }

        return $existing;
    }
}
