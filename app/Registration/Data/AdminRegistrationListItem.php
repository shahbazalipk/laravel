<?php

namespace App\Registration\Data;

use App\Models\Registration;
use App\Models\RegistrationCategory;
use App\Registration\Enums\RegistrationWizardStep;
use App\Registration\Models\RegistrationDraft;
use Carbon\CarbonInterface;

class AdminRegistrationListItem
{
    public function __construct(
        public readonly string $kind,
        public readonly string $stage,
        public readonly string $reference,
        public readonly string $name,
        public readonly ?string $company,
        public readonly string $email,
        public readonly ?string $phone,
        public readonly ?string $categoryName,
        public readonly string $typeLabel,
        public readonly ?string $paymentStatus,
        public readonly ?string $checkInLabel,
        public readonly ?string $stepLabel,
        public readonly bool $isExpired,
        public readonly CarbonInterface $createdAt,
        public readonly string $showUrl,
        public readonly ?string $editUrl,
    ) {}

    public static function fromRegistration(Registration $registration): self
    {
        return new self(
            kind: 'registration',
            stage: 'Registered',
            reference: (string) ($registration->registration_number ?: 'REG-'.$registration->id),
            name: trim((string) $registration->full_name) ?: '—',
            company: $registration->company_name,
            email: (string) $registration->email,
            phone: $registration->phone,
            categoryName: $registration->registrationCategory?->name,
            typeLabel: ucfirst((string) ($registration->registration_type ?: 'individual')),
            paymentStatus: $registration->payment_status,
            checkInLabel: $registration->checked_in ? 'Checked In' : 'Not Checked In',
            stepLabel: null,
            isExpired: false,
            createdAt: $registration->created_at,
            showUrl: route('admin.registrations.show', $registration),
            editUrl: route('admin.registrations.edit', $registration),
        );
    }

    public static function fromDraft(RegistrationDraft $draft, ?RegistrationCategory $category = null): self
    {
        $first = (string) $draft->payloadValue('first_name', '');
        $last = (string) $draft->payloadValue('last_name', '');
        $name = trim($first.' '.$last);
        $step = $draft->current_step instanceof RegistrationWizardStep
            ? $draft->current_step
            : RegistrationWizardStep::Email;

        return new self(
            kind: 'draft',
            stage: 'Draft',
            reference: 'DRAFT-'.strtoupper(substr((string) $draft->public_id, 0, 8)),
            name: $name !== '' ? $name : '—',
            company: $draft->payloadValue('company_name'),
            email: (string) $draft->email,
            phone: $draft->payloadValue('phone'),
            categoryName: $category?->name,
            typeLabel: 'Online draft',
            paymentStatus: null,
            checkInLabel: null,
            stepLabel: 'Step '.$step->number().': '.$step->label(),
            isExpired: $draft->isExpired(),
            createdAt: $draft->created_at,
            showUrl: route('admin.registration-drafts.show', $draft),
            editUrl: null,
        );
    }
}
