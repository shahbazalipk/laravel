<?php

namespace App\Mcp\Services;

use App\Forms\Enums\FormResponseStatus;
use App\Forms\Models\CustomFormAnswer;
use App\Models\Registration;
use App\Payments\Enums\RegistrationPaymentSummaryStatus;
use App\Payments\Services\RegistrationPaymentTotals;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema;

class RegistrationReadService
{
    public function __construct(private readonly RegistrationPaymentTotals $paymentTotals) {}

    /**
     * @return array<string, mixed>
     */
    public function summary(?string $from = null, ?string $to = null): array
    {
        $query = $this->dateRange(Registration::query(), $from, $to);
        $total = (clone $query)->count();

        return [
            'total' => $total,
            'active' => (clone $query)->where('is_active', true)->count(),
            'checked_in' => (clone $query)->where('checked_in', true)->count(),
            'not_checked_in' => (clone $query)->where('checked_in', false)->count(),
            'by_registration_status' => (clone $query)
                ->leftJoin('registration_statuses', 'registration_statuses.id', '=', 'registrations.registration_status_id')
                ->selectRaw("COALESCE(registration_statuses.slug, 'unassigned') as status, COUNT(*) as aggregate")
                ->groupBy('registration_statuses.slug')
                ->pluck('aggregate', 'status')
                ->map(fn ($count) => (int) $count)
                ->all(),
            'by_category' => (clone $query)
                ->join('registration_categories', 'registration_categories.id', '=', 'registrations.registration_category_id')
                ->selectRaw('registration_categories.name as category, COUNT(*) as aggregate')
                ->groupBy('registration_categories.name')
                ->pluck('aggregate', 'category')
                ->map(fn ($count) => (int) $count)
                ->all(),
            'by_payment_status' => (clone $query)
                ->selectRaw('payment_status, COUNT(*) as aggregate')
                ->groupBy('payment_status')
                ->pluck('aggregate', 'payment_status')
                ->map(fn ($count) => (int) $count)
                ->all(),
            'period' => ['from' => $from, 'to' => $to],
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    public function findStatus(string $registrationNumber): ?array
    {
        $registration = Registration::query()
            ->with([
                'registrationStatus:id,name,slug',
                'registrationCategory:id,name',
                'paymentEntries',
                'customFormResponses.answers',
            ])
            ->where('registration_number', $registrationNumber)
            ->first();

        if (! $registration) {
            return null;
        }

        $payment = $this->paymentTotals->calculate($registration, $registration->paymentEntries);

        return [
            'registration_number' => $registration->registration_number,
            'profile' => $this->profilePayload($registration),
            'registration_status' => [
                'name' => $registration->registrationStatus?->name,
                'slug' => $registration->registrationStatus?->slug,
            ],
            'category' => $registration->registrationCategory?->name,
            'registration_type' => $registration->registration_type,
            'is_active' => (bool) $registration->is_active,
            'payment' => [
                ...$payment,
                'summary_status' => $payment['summary_status']->value,
            ],
            'check_in' => [
                'checked_in' => (bool) $registration->checked_in,
                'checked_in_at' => $registration->checked_in_at?->toIso8601String(),
            ],
            'registered_at' => $registration->created_at?->toIso8601String(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function paymentSummary(?string $from = null, ?string $to = null): array
    {
        $counts = array_fill_keys(
            array_map(fn (RegistrationPaymentSummaryStatus $status) => $status->value, RegistrationPaymentSummaryStatus::cases()),
            0,
        );
        $currencies = [];

        $this->dateRange(Registration::query(), $from, $to)
            ->with('paymentEntries')
            ->orderBy('id')
            ->chunkById(500, function ($registrations) use (&$counts, &$currencies): void {
                foreach ($registrations as $registration) {
                    $totals = $this->paymentTotals->calculate($registration, $registration->paymentEntries);
                    $counts[$totals['summary_status']->value]++;
                    $currency = $totals['currency'];
                    $currencies[$currency] ??= [
                        'registration_total' => 0.0,
                        'net_paid' => 0.0,
                        'balance_due' => 0.0,
                        'refunded' => 0.0,
                    ];

                    foreach (array_keys($currencies[$currency]) as $field) {
                        $currencies[$currency][$field] += $totals[$field];
                    }
                }
            });

        foreach ($currencies as &$totals) {
            foreach ($totals as &$amount) {
                $amount = round($amount, 2);
            }
        }

        return [
            'registrations_by_payment_status' => $counts,
            'amounts_by_currency' => $currencies,
            'period' => ['from' => $from, 'to' => $to],
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function recent(int $limit, ?string $status, ?string $paymentStatus): array
    {
        $query = Registration::query()
            ->with([
                'registrationStatus:id,name,slug',
                'registrationCategory:id,name',
                'customFormResponses.answers',
            ])
            ->latest('created_at');

        if ($status) {
            $query->whereHas('registrationStatus', fn (Builder $query) => $query->where('slug', $status));
        }

        if ($paymentStatus) {
            $query->where('payment_status', $paymentStatus);
        }

        return $query->limit($limit)->get()->map(fn (Registration $registration) => [
            'registration_number' => $registration->registration_number,
            'profile' => $this->profilePayload($registration),
            'status' => $registration->registrationStatus?->slug,
            'category' => $registration->registrationCategory?->name,
            'payment_status' => $registration->payment_status,
            'checked_in' => (bool) $registration->checked_in,
            'registered_at' => $registration->created_at?->toIso8601String(),
        ])->all();
    }

    /**
     * @return array<string, mixed>
     */
    public function checkInSummary(): array
    {
        $total = Registration::query()->count();
        $checkedIn = Registration::query()->where('checked_in', true)->count();

        return [
            'total_registrations' => $total,
            'checked_in' => $checkedIn,
            'not_checked_in' => $total - $checkedIn,
            'check_in_rate_percent' => $total > 0 ? round(($checkedIn / $total) * 100, 2) : 0.0,
            'checked_in_today' => Registration::query()
                ->whereDate('checked_in_at', today())
                ->count(),
            'by_category' => Registration::query()
                ->join('registration_categories', 'registration_categories.id', '=', 'registrations.registration_category_id')
                ->selectRaw('registration_categories.name as category, COUNT(*) as total')
                ->selectRaw('SUM(CASE WHEN registrations.checked_in = 1 THEN 1 ELSE 0 END) as checked_in')
                ->groupBy('registration_categories.name')
                ->get()
                ->mapWithKeys(fn ($row) => [$row->category => [
                    'total' => (int) $row->total,
                    'checked_in' => (int) $row->checked_in,
                ]])
                ->all(),
        ];
    }

    /**
     * Website-safe profile fields already stored on the registration / custom answers.
     * Does not create new fields and does not expose email/phone.
     *
     * @return array{
     *   first_name: string|null,
     *   last_name: string|null,
     *   photo: string|null,
     *   job_title: string|null,
     *   company: string|null,
     *   linkedin: string|null
     * }
     */
    private function profilePayload(Registration $registration): array
    {
        return [
            'first_name' => $registration->first_name ?: null,
            'last_name' => $registration->last_name ?: null,
            'photo' => $this->photoUrl($registration),
            'job_title' => $registration->job_title ?: null,
            'company' => $registration->company_name ?: null,
            'linkedin' => $this->linkedInFromCustomAnswers($registration),
        ];
    }

    private function photoUrl(Registration $registration): ?string
    {
        if (! Schema::hasColumn('registrations', 'profile_picture')) {
            return null;
        }

        $path = $registration->profile_picture;

        return filled($path) ? storage_public_url((string) $path) : null;
    }

    private function linkedInFromCustomAnswers(Registration $registration): ?string
    {
        if (! Schema::hasTable('custom_form_answers')) {
            return null;
        }

        $responses = $registration->relationLoaded('customFormResponses')
            ? $registration->customFormResponses
            : $registration->customFormResponses()->with('answers')->get();

        foreach ($responses as $response) {
            if (
                $response->status instanceof FormResponseStatus
                && $response->status !== FormResponseStatus::Submitted
            ) {
                continue;
            }

            $answers = $response->relationLoaded('answers')
                ? $response->answers
                : $response->answers()->get();

            foreach ($answers as $answer) {
                if (! $this->looksLikeLinkedInQuestion($answer)) {
                    continue;
                }

                $value = $this->scalarAnswerValue($answer->value);
                if (filled($value)) {
                    return $value;
                }
            }
        }

        return null;
    }

    private function looksLikeLinkedInQuestion(CustomFormAnswer $answer): bool
    {
        $haystack = strtolower(trim(
            (string) $answer->question_key.' '.(string) $answer->question_label
        ));

        return str_contains($haystack, 'linkedin')
            || str_contains($haystack, 'linked_in')
            || str_contains($haystack, 'linked-in');
    }

    private function scalarAnswerValue(mixed $value): ?string
    {
        if (! is_array($value)) {
            return filled($value) ? trim((string) $value) : null;
        }

        if (array_key_exists('value', $value) && ! is_array($value['value'])) {
            return filled($value['value']) ? trim((string) $value['value']) : null;
        }

        if (isset($value['values']) && is_array($value['values'])) {
            foreach ($value['values'] as $item) {
                if (filled($item) && ! is_array($item)) {
                    return trim((string) $item);
                }
            }
        }

        return null;
    }

    private function dateRange(Builder $query, ?string $from, ?string $to): Builder
    {
        return $query
            ->when($from, fn (Builder $query) => $query->whereDate('registrations.created_at', '>=', $from))
            ->when($to, fn (Builder $query) => $query->whereDate('registrations.created_at', '<=', $to));
    }
}
