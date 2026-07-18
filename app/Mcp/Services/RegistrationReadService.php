<?php

namespace App\Mcp\Services;

use App\Models\Registration;
use App\Payments\Enums\RegistrationPaymentSummaryStatus;
use App\Payments\Services\RegistrationPaymentTotals;
use Illuminate\Database\Eloquent\Builder;

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
            ->with(['registrationStatus:id,name,slug', 'registrationCategory:id,name', 'paymentEntries'])
            ->where('registration_number', $registrationNumber)
            ->first();

        if (! $registration) {
            return null;
        }

        $payment = $this->paymentTotals->calculate($registration, $registration->paymentEntries);

        return [
            'registration_number' => $registration->registration_number,
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
            ->with(['registrationStatus:id,name,slug', 'registrationCategory:id,name'])
            ->latest('created_at');

        if ($status) {
            $query->whereHas('registrationStatus', fn (Builder $query) => $query->where('slug', $status));
        }

        if ($paymentStatus) {
            $query->where('payment_status', $paymentStatus);
        }

        return $query->limit($limit)->get()->map(fn (Registration $registration) => [
            'registration_number' => $registration->registration_number,
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

    private function dateRange(Builder $query, ?string $from, ?string $to): Builder
    {
        return $query
            ->when($from, fn (Builder $query) => $query->whereDate('registrations.created_at', '>=', $from))
            ->when($to, fn (Builder $query) => $query->whereDate('registrations.created_at', '<=', $to));
    }
}
