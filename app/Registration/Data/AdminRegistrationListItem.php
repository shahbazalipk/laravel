<?php

namespace App\Registration\Data;

use App\Forms\Models\CustomFormAnswer;
use App\Forms\Models\CustomFormResponse;
use App\Models\Registration;
use App\Models\RegistrationCategory;
use App\Registration\Enums\RegistrationWizardStep;
use App\Registration\Models\RegistrationDraft;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

class AdminRegistrationListItem
{
    /**
     * @param  array<string, string>  $values
     */
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
        public readonly ?string $deleteUrl,
        public readonly array $values = [],
    ) {}

    public static function fromRegistration(Registration $registration): self
    {
        $values = [
            'std:reference' => (string) ($registration->registration_number ?: 'REG-'.$registration->id),
            'std:name' => trim((string) $registration->full_name) ?: '—',
            'std:email' => (string) $registration->email,
            'std:phone' => (string) ($registration->phone ?: ''),
            'std:mobile' => (string) ($registration->mobile_phone ?: ''),
            'std:company' => (string) ($registration->company_name ?: ''),
            'std:job_title' => (string) ($registration->job_title ?: ''),
            'std:department' => (string) ($registration->department ?: ''),
            'std:industry' => (string) ($registration->industry?->name ?: ''),
            'std:category' => (string) ($registration->registrationCategory?->name ?: ''),
            'std:status' => (string) ($registration->registrationStatus?->name ?: ''),
            'std:stage' => 'Registered',
            'std:type' => ucfirst((string) ($registration->registration_type ?: 'individual')),
            'std:group' => (string) ($registration->group?->group_name ?: ''),
            'std:exhibitor' => (string) ($registration->exhibitor?->company_name ?: ''),
            'std:payment' => (string) ($registration->payment_status ?: ''),
            'std:total_amount' => $registration->total_amount !== null
                ? number_format((float) $registration->total_amount, 2).' '.($registration->currency ?: '')
                : '',
            'std:check_in' => $registration->checked_in ? 'Checked In' : 'Not Checked In',
            'std:city' => (string) ($registration->city ?: ''),
            'std:country' => (string) ($registration->country ?: ''),
            'std:notes' => (string) ($registration->notes ?: ''),
            'std:step' => '',
            'std:created_at' => $registration->created_at?->format('M d, Y H:i') ?: '',
        ];

        foreach (self::customAnswerValues(self::loadedCustomResponses($registration)) as $key => $value) {
            $values[$key] = $value;
        }

        return new self(
            kind: 'registration',
            stage: 'Registered',
            reference: $values['std:reference'],
            name: $values['std:name'],
            company: $registration->company_name,
            email: (string) $registration->email,
            phone: $registration->phone,
            categoryName: $registration->registrationCategory?->name,
            typeLabel: $values['std:type'],
            paymentStatus: $registration->payment_status,
            checkInLabel: $values['std:check_in'],
            stepLabel: null,
            isExpired: false,
            createdAt: $registration->created_at,
            showUrl: route('admin.registrations.show', $registration),
            editUrl: route('admin.registrations.edit', $registration),
            deleteUrl: route('admin.registrations.destroy', $registration),
            values: $values,
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
        $stepLabel = 'Step '.$step->number().': '.$step->label();

        $values = [
            'std:reference' => $draft->displayReference(),
            'std:name' => $name !== '' ? $name : '—',
            'std:email' => (string) $draft->email,
            'std:phone' => (string) ($draft->payloadValue('phone') ?: ''),
            'std:mobile' => (string) ($draft->payloadValue('mobile_phone') ?: $draft->payloadValue('mobile') ?: ''),
            'std:company' => (string) ($draft->payloadValue('company_name') ?: ''),
            'std:job_title' => (string) ($draft->payloadValue('job_title') ?: ''),
            'std:department' => (string) ($draft->payloadValue('department') ?: ''),
            'std:industry' => '',
            'std:category' => (string) ($category?->name ?: ''),
            'std:status' => '',
            'std:stage' => 'Draft',
            'std:type' => 'Online draft',
            'std:group' => '',
            'std:exhibitor' => '',
            'std:payment' => '',
            'std:total_amount' => '',
            'std:check_in' => '',
            'std:city' => (string) ($draft->payloadValue('city') ?: ''),
            'std:country' => (string) ($draft->payloadValue('country') ?: ''),
            'std:notes' => '',
            'std:step' => $stepLabel,
            'std:created_at' => $draft->created_at?->format('M d, Y H:i') ?: '',
        ];

        foreach (self::customAnswerValues(self::loadedCustomResponses($draft)) as $key => $value) {
            $values[$key] = $value;
        }

        return new self(
            kind: 'draft',
            stage: 'Draft',
            reference: $values['std:reference'],
            name: $values['std:name'],
            company: $draft->payloadValue('company_name'),
            email: (string) $draft->email,
            phone: $draft->payloadValue('phone'),
            categoryName: $category?->name,
            typeLabel: 'Online draft',
            paymentStatus: null,
            checkInLabel: null,
            stepLabel: $stepLabel,
            isExpired: $draft->isExpired(),
            createdAt: $draft->created_at,
            showUrl: route('admin.registration-drafts.show', $draft),
            editUrl: null,
            deleteUrl: route('admin.registration-drafts.destroy', $draft),
            values: $values,
        );
    }

    public function valueFor(string $key): string
    {
        $value = $this->exportValueFor($key);

        return $value !== '' ? $value : '—';
    }

    public function exportValueFor(string $key): string
    {
        return (string) ($this->values[$key] ?? '');
    }

    /**
     * @param  Registration|RegistrationDraft  $model
     * @return Collection<int, CustomFormResponse>
     */
    private static function loadedCustomResponses(Registration|RegistrationDraft $model): Collection
    {
        if (! $model->relationLoaded('customFormResponses')) {
            return collect();
        }

        return $model->customFormResponses ?? collect();
    }

    /**
     * @param  Collection<int, CustomFormResponse>  $responses
     * @return array<string, string>
     */
    private static function customAnswerValues(Collection $responses): array
    {
        $values = [];

        foreach ($responses as $response) {
            foreach ($response->answers ?? [] as $answer) {
                /** @var CustomFormAnswer $answer */
                $questionId = $answer->question?->public_id;
                if (! $questionId) {
                    continue;
                }

                $values['q:'.$questionId] = self::formatAnswer($answer);
            }
        }

        return $values;
    }

    private static function formatAnswer(CustomFormAnswer $answer): string
    {
        $type = $answer->question_type?->value ?? '';

        if ($type === 'upload') {
            $names = $answer->files?->pluck('original_name')->filter()->implode(', ');

            return $names !== '' ? $names : 'Uploaded file';
        }

        $options = $answer->question?->options?->keyBy(fn ($option) => (string) $option->value) ?? collect();
        $rawValues = $type === 'checkbox'
            ? ($answer->value['values'] ?? [])
            : [$answer->value['value'] ?? null];

        $display = collect($rawValues)
            ->reject(fn ($value) => $value === null || $value === '')
            ->map(function ($value) use ($options) {
                $option = $options->get((string) $value);

                return $option ? $option->label : (string) $value;
            })
            ->implode(', ');

        return $display;
    }
}
