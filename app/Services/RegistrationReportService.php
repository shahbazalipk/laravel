<?php

namespace App\Services;

use App\Models\Registration;
use App\Models\RegistrationCategory;
use App\Payments\Enums\RegistrationPaymentSummaryStatus;
use App\Payments\Services\RegistrationPaymentTotals;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RegistrationReportService
{
    public function __construct(
        private RegistrationPaymentTotals $paymentTotals,
    ) {}
    /**
     * @return array{
     *   from: string,
     *   to: string,
     *   totals: array{
     *     registrations: int,
     *     revenue: float,
     *     discounts: float,
     *     tax: float,
     *     paid_count: int,
     *     pending_count: int,
     *     currency: string
     *   },
     *   rows: list<array{
     *     category_id: int|null,
     *     category_name: string,
     *     registrations: int,
     *     paid: int,
     *     pending: int,
     *     other_payment: int,
     *     checked_in: int,
     *     base_price_sum: float,
     *     discount_sum: float,
     *     tax_sum: float,
     *     revenue: float,
     *     currency: string
     *   }>
     * }
     */
    public function categoryReport(?CarbonInterface $from = null, ?CarbonInterface $to = null): array
    {
        [$from, $to] = $this->normalizeRange($from, $to);
        $query = $this->registrationsQuery($from, $to);

        $aggregates = (clone $query)
            ->select([
                'registration_category_id',
                DB::raw('COUNT(*) as registrations'),
                DB::raw("SUM(CASE WHEN payment_status IN ('paid', 'overpaid') THEN 1 ELSE 0 END) as paid"),
                DB::raw("SUM(CASE WHEN payment_status = 'pending' OR payment_status IS NULL THEN 1 ELSE 0 END) as pending"),
                DB::raw("SUM(CASE WHEN payment_status NOT IN ('paid', 'overpaid', 'pending') AND payment_status IS NOT NULL THEN 1 ELSE 0 END) as other_payment"),
                DB::raw('SUM(CASE WHEN checked_in = 1 THEN 1 ELSE 0 END) as checked_in'),
                DB::raw('COALESCE(SUM(base_price), 0) as base_price_sum'),
                DB::raw('COALESCE(SUM(discount_amount), 0) as discount_sum'),
                DB::raw('COALESCE(SUM(tax_amount), 0) as tax_sum'),
                DB::raw('COALESCE(SUM(total_amount), 0) as revenue'),
                DB::raw('MAX(currency) as currency'),
            ])
            ->groupBy('registration_category_id')
            ->get()
            ->keyBy('registration_category_id');

        $categoryNames = RegistrationCategory::query()
            ->whereIn('id', $aggregates->keys()->filter()->all())
            ->pluck('name', 'id');

        $rows = [];
        foreach ($aggregates as $categoryId => $row) {
            $rows[] = [
                'category_id' => $categoryId ? (int) $categoryId : null,
                'category_name' => $categoryId
                    ? (string) ($categoryNames[$categoryId] ?? 'Unknown category')
                    : 'Unassigned',
                'registrations' => (int) $row->registrations,
                'paid' => (int) $row->paid,
                'pending' => (int) $row->pending,
                'other_payment' => (int) $row->other_payment,
                'checked_in' => (int) $row->checked_in,
                'base_price_sum' => round((float) $row->base_price_sum, 2),
                'discount_sum' => round((float) $row->discount_sum, 2),
                'tax_sum' => round((float) $row->tax_sum, 2),
                'revenue' => round((float) $row->revenue, 2),
                'currency' => $row->currency ?: $this->defaultCurrency(),
            ];
        }

        usort($rows, fn (array $a, array $b) => $b['registrations'] <=> $a['registrations']);

        $totals = [
            'registrations' => array_sum(array_column($rows, 'registrations')),
            'revenue' => round(array_sum(array_column($rows, 'revenue')), 2),
            'discounts' => round(array_sum(array_column($rows, 'discount_sum')), 2),
            'tax' => round(array_sum(array_column($rows, 'tax_sum')), 2),
            'paid_count' => array_sum(array_column($rows, 'paid')),
            'pending_count' => array_sum(array_column($rows, 'pending')),
            'currency' => $rows[0]['currency'] ?? $this->defaultCurrency(),
        ];

        return [
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
            'totals' => $totals,
            'rows' => $rows,
        ];
    }

    /**
     * @return array{
     *   from: string,
     *   to: string,
     *   totals: array{
     *     registrations: int,
     *     revenue: float,
     *     discounts: float,
     *     tax: float,
     *     paid_revenue: float,
     *     pending_revenue: float,
     *     paid_count: int,
     *     pending_count: int,
     *     currency: string
     *   },
     *   by_status: list<array{status: string, label: string, count: int, revenue: float}>,
     *   by_method: list<array{method: string, count: int, revenue: float}>,
     *   daily: list<array{date: string, registrations: int, revenue: float, paid_revenue: float}>,
     *   top_categories: list<array{category_name: string, registrations: int, revenue: float}>
     * }
     */
    public function paymentsReport(?CarbonInterface $from = null, ?CarbonInterface $to = null): array
    {
        [$from, $to] = $this->normalizeRange($from, $to);
        $query = $this->registrationsQuery($from, $to);

        $registrations = (clone $query)->get([
            'id',
            'registration_category_id',
            'payment_status',
            'payment_method',
            'base_price',
            'discount_amount',
            'tax_amount',
            'total_amount',
            'currency',
            'created_at',
        ]);

        $currency = $registrations->pluck('currency')->filter()->first() ?: $this->defaultCurrency();

        $byStatus = $registrations
            ->groupBy(fn (Registration $r) => $r->payment_status ?: 'pending')
            ->map(function (Collection $group, string $status) {
                return [
                    'status' => $status,
                    'label' => $this->paymentStatusLabel($status),
                    'count' => $group->count(),
                    'revenue' => round((float) $group->sum('total_amount'), 2),
                ];
            })
            ->sortByDesc('count')
            ->values()
            ->all();

        $byMethod = $registrations
            ->groupBy(fn (Registration $r) => filled($r->payment_method) ? (string) $r->payment_method : 'Unspecified')
            ->map(fn (Collection $group, string $method) => [
                'method' => $method,
                'count' => $group->count(),
                'revenue' => round((float) $group->sum('total_amount'), 2),
            ])
            ->sortByDesc('revenue')
            ->values()
            ->all();

        $categoryNames = RegistrationCategory::query()
            ->whereIn('id', $registrations->pluck('registration_category_id')->filter()->unique()->all())
            ->pluck('name', 'id');

        $topCategories = $registrations
            ->groupBy('registration_category_id')
            ->map(function (Collection $group, $categoryId) use ($categoryNames) {
                return [
                    'category_name' => $categoryId
                        ? (string) ($categoryNames[$categoryId] ?? 'Unknown category')
                        : 'Unassigned',
                    'registrations' => $group->count(),
                    'revenue' => round((float) $group->sum('total_amount'), 2),
                ];
            })
            ->sortByDesc('revenue')
            ->take(8)
            ->values()
            ->all();

        $dailyMap = $registrations->groupBy(fn (Registration $r) => optional($r->created_at)->toDateString() ?: 'unknown');
        $daily = [];
        for ($day = $from->copy()->startOfDay(); $day->lte($to->copy()->startOfDay()); $day->addDay()) {
            $key = $day->toDateString();
            $bucket = $dailyMap->get($key, collect());
            $daily[] = [
                'date' => $key,
                'registrations' => $bucket->count(),
                'revenue' => round((float) $bucket->sum('total_amount'), 2),
                'paid_revenue' => round((float) $bucket->whereIn('payment_status', ['paid', 'overpaid'])->sum('total_amount'), 2),
            ];
        }

        $paid = $registrations->whereIn('payment_status', ['paid', 'overpaid']);
        $pending = $registrations->filter(fn (Registration $r) => blank($r->payment_status) || $r->payment_status === 'pending');

        return [
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
            'totals' => [
                'registrations' => $registrations->count(),
                'revenue' => round((float) $registrations->sum('total_amount'), 2),
                'discounts' => round((float) $registrations->sum('discount_amount'), 2),
                'tax' => round((float) $registrations->sum('tax_amount'), 2),
                'paid_revenue' => round((float) $paid->sum('total_amount'), 2),
                'pending_revenue' => round((float) $pending->sum('total_amount'), 2),
                'paid_count' => $paid->count(),
                'pending_count' => $pending->count(),
                'currency' => $currency,
            ],
            'by_status' => $byStatus,
            'by_method' => $byMethod,
            'daily' => $daily,
            'top_categories' => $topCategories,
        ];
    }

    /**
     * @return array{
     *   from: string,
     *   to: string,
     *   currency: string,
     *   totals: array{
     *     registrations: int,
     *     total_price: float,
     *     paid: float,
     *     pending: float
     *   },
     *   groups: list<array{
     *     status: string,
     *     label: string,
     *     badge_classes: string,
     *     count: int,
     *     total_price: float,
     *     paid: float,
     *     pending: float,
     *     rows: list<array{
     *       registration_id: int,
     *       registration_number: string|null,
     *       name: string,
     *       phone: string,
     *       category_name: string,
     *       total_price: float,
     *       paid: float,
     *       pending: float,
     *       currency: string,
     *       payment_status: string,
     *       payment_status_label: string,
     *       show_url: string
     *     }>
     *   }>
     * }
     */
    public function paymentStatusDetailReport(?CarbonInterface $from = null, ?CarbonInterface $to = null): array
    {
        [$from, $to] = $this->normalizeRange($from, $to);

        $registrations = $this->registrationsQuery($from, $to)
            ->with(['registrationCategory', 'paymentEntries'])
            ->orderBy('created_at')
            ->get();

        $rows = $registrations->map(function (Registration $registration): array {
            $payment = $this->paymentTotals->calculate($registration);

            return [
                'registration_id' => (int) $registration->id,
                'registration_number' => $registration->registration_number,
                'name' => trim((string) $registration->full_name) ?: '—',
                'phone' => filled($registration->phone)
                    ? (string) $registration->phone
                    : (filled($registration->mobile_phone) ? (string) $registration->mobile_phone : '—'),
                'category_name' => $registration->registrationCategory?->name ?? 'Unassigned',
                'total_price' => $payment['registration_total'],
                'paid' => $payment['net_paid'],
                'pending' => $payment['balance_due'],
                'currency' => $payment['currency'],
                'payment_status' => $payment['summary_status']->value,
                'payment_status_label' => $payment['summary_status']->label(),
                'show_url' => route('admin.registrations.show', $registration),
            ];
        });

        $statusOrder = collect(RegistrationPaymentSummaryStatus::cases())
            ->mapWithKeys(fn (RegistrationPaymentSummaryStatus $status, int $index) => [$status->value => $index]);

        $groups = $rows
            ->groupBy('payment_status')
            ->map(function (Collection $groupRows, string $status) {
                $summaryStatus = RegistrationPaymentSummaryStatus::from($status);

                return [
                    'status' => $status,
                    'label' => $summaryStatus->label(),
                    'badge_classes' => $summaryStatus->badgeClasses(),
                    'count' => $groupRows->count(),
                    'total_price' => round((float) $groupRows->sum('total_price'), 2),
                    'paid' => round((float) $groupRows->sum('paid'), 2),
                    'pending' => round((float) $groupRows->sum('pending'), 2),
                    'rows' => $groupRows->values()->all(),
                ];
            })
            ->sortBy(fn (array $group) => $statusOrder[$group['status']] ?? 999)
            ->values()
            ->all();

        $currency = $rows->pluck('currency')->filter()->first() ?: $this->defaultCurrency();

        return [
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
            'currency' => $currency,
            'totals' => [
                'registrations' => $rows->count(),
                'total_price' => round((float) $rows->sum('total_price'), 2),
                'paid' => round((float) $rows->sum('paid'), 2),
                'pending' => round((float) $rows->sum('pending'), 2),
            ],
            'groups' => $groups,
        ];
    }

    /**
     * @param  array{from: string, to: string, currency: string, totals: array<string, mixed>, groups: list<array<string, mixed>>}  $report
     */
    public function paymentStatusDetailReportCsv(array $report): string
    {
        $lines = [];
        $lines[] = $this->csvLine([
            'Payment status',
            'Registration #',
            'Name',
            'Phone',
            'Category',
            'Total price',
            'Paid',
            'Pending',
            'Currency',
        ]);

        foreach ($report['groups'] as $group) {
            foreach ($group['rows'] as $row) {
                $lines[] = $this->csvLine([
                    $group['label'],
                    $row['registration_number'] ?? '',
                    $row['name'],
                    $row['phone'],
                    $row['category_name'],
                    number_format($row['total_price'], 2, '.', ''),
                    number_format($row['paid'], 2, '.', ''),
                    number_format($row['pending'], 2, '.', ''),
                    $row['currency'],
                ]);
            }
        }

        $lines[] = '';
        $lines[] = $this->csvLine(['Summary', 'Value']);
        $lines[] = $this->csvLine(['From', $report['from']]);
        $lines[] = $this->csvLine(['To', $report['to']]);
        $lines[] = $this->csvLine(['Registrations', $report['totals']['registrations']]);
        $lines[] = $this->csvLine(['Total price', number_format($report['totals']['total_price'], 2, '.', '')]);
        $lines[] = $this->csvLine(['Paid', number_format($report['totals']['paid'], 2, '.', '')]);
        $lines[] = $this->csvLine(['Pending', number_format($report['totals']['pending'], 2, '.', '')]);
        $lines[] = $this->csvLine(['Currency', $report['currency']]);

        return implode("\n", $lines)."\n";
    }

    /**
     * @param  array{from: string, to: string, totals: array<string, mixed>, rows: list<array<string, mixed>>}  $report
     */
    public function categoryReportCsv(array $report): string
    {
        $lines = [];
        $lines[] = $this->csvLine([
            'Category',
            'Registrations',
            'Paid',
            'Pending',
            'Other payment',
            'Checked in',
            'Base total',
            'Discounts',
            'Tax',
            'Revenue',
            'Currency',
        ]);

        foreach ($report['rows'] as $row) {
            $lines[] = $this->csvLine([
                $row['category_name'],
                $row['registrations'],
                $row['paid'],
                $row['pending'],
                $row['other_payment'],
                $row['checked_in'],
                number_format($row['base_price_sum'], 2, '.', ''),
                number_format($row['discount_sum'], 2, '.', ''),
                number_format($row['tax_sum'], 2, '.', ''),
                number_format($row['revenue'], 2, '.', ''),
                $row['currency'],
            ]);
        }

        $lines[] = $this->csvLine([
            'TOTAL',
            $report['totals']['registrations'],
            $report['totals']['paid_count'],
            $report['totals']['pending_count'],
            '',
            '',
            '',
            number_format($report['totals']['discounts'], 2, '.', ''),
            number_format($report['totals']['tax'], 2, '.', ''),
            number_format($report['totals']['revenue'], 2, '.', ''),
            $report['totals']['currency'],
        ]);

        return implode("\n", $lines)."\n";
    }

    /**
     * @param  array{from: string, to: string, totals: array<string, mixed>, by_status: list<array<string, mixed>>}  $report
     */
    public function paymentsReportCsv(array $report): string
    {
        $lines = [];
        $lines[] = $this->csvLine(['Payment status', 'Count', 'Revenue']);
        foreach ($report['by_status'] as $row) {
            $lines[] = $this->csvLine([
                $row['label'],
                $row['count'],
                number_format($row['revenue'], 2, '.', ''),
            ]);
        }

        $lines[] = '';
        $lines[] = $this->csvLine(['Summary', 'Value']);
        $lines[] = $this->csvLine(['From', $report['from']]);
        $lines[] = $this->csvLine(['To', $report['to']]);
        $lines[] = $this->csvLine(['Registrations', $report['totals']['registrations']]);
        $lines[] = $this->csvLine(['Total revenue', number_format($report['totals']['revenue'], 2, '.', '')]);
        $lines[] = $this->csvLine(['Paid revenue', number_format($report['totals']['paid_revenue'], 2, '.', '')]);
        $lines[] = $this->csvLine(['Pending revenue', number_format($report['totals']['pending_revenue'], 2, '.', '')]);
        $lines[] = $this->csvLine(['Discounts', number_format($report['totals']['discounts'], 2, '.', '')]);
        $lines[] = $this->csvLine(['Tax', number_format($report['totals']['tax'], 2, '.', '')]);
        $lines[] = $this->csvLine(['Currency', $report['totals']['currency']]);

        return implode("\n", $lines)."\n";
    }

    /**
     * @return array{0: Carbon, 1: Carbon}
     */
    public function normalizeRange(?CarbonInterface $from = null, ?CarbonInterface $to = null): array
    {
        $from = $from ? Carbon::parse($from)->startOfDay() : now()->subDays(29)->startOfDay();
        $to = $to ? Carbon::parse($to)->endOfDay() : now()->endOfDay();

        if ($from->gt($to)) {
            return [$to->copy()->startOfDay(), $from->copy()->endOfDay()];
        }

        return [$from, $to];
    }

    private function registrationsQuery(CarbonInterface $from, CarbonInterface $to)
    {
        $query = Registration::query()
            ->whereBetween('created_at', [$from, $to]);

        if (Schema::hasColumn('registrations', 'deleted_at')) {
            $query->whereNull('deleted_at');
        }

        return $query;
    }

    private function defaultCurrency(): string
    {
        return (string) (config('event.currency') ?: 'AED');
    }

    private function paymentStatusLabel(string $status): string
    {
        return match ($status) {
            'paid' => 'Paid',
            'pending' => 'Pending',
            'partially_paid' => 'Partially paid',
            'overpaid' => 'Overpaid',
            'refunded' => 'Refunded',
            'partially_refunded' => 'Partially refunded',
            'failed' => 'Failed',
            default => ucwords(str_replace('_', ' ', $status)),
        };
    }

    /**
     * @param  list<mixed>  $values
     */
    private function csvLine(array $values): string
    {
        return collect($values)
            ->map(function ($value) {
                $string = (string) $value;
                if (str_contains($string, ',') || str_contains($string, '"') || str_contains($string, "\n")) {
                    return '"'.str_replace('"', '""', $string).'"';
                }

                return $string;
            })
            ->implode(',');
    }
}
