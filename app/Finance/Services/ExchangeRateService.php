<?php

namespace App\Finance\Services;

use App\Finance\Models\FinanceExchangeRate;
use App\Shared\Audit\AuditLogger;
use Carbon\CarbonInterface;
use Illuminate\Validation\ValidationException;

class ExchangeRateService
{
    public function __construct(
        private readonly MoneyService $money,
        private readonly AuditLogger $audit,
    ) {}

    public function set(array $data): FinanceExchangeRate
    {
        $from = $this->money->currency($data['from_currency']);
        $to = $this->money->currency($data['to_currency']);
        if ($from === $to) {
            throw ValidationException::withMessages(['to_currency' => 'Currencies must be different.']);
        }

        $rate = FinanceExchangeRate::query()->updateOrCreate(
            [
                'from_currency' => $from,
                'to_currency' => $to,
                'effective_date' => $data['effective_date'],
                'source' => $data['source'] ?? 'manual',
            ],
            [
                'rate' => $this->money->positive($data['rate'], 8),
                'override_reason' => $data['override_reason'] ?? null,
                'created_by' => session('admin_id'),
            ],
        );

        $this->audit->record('finance', 'exchange-rate.saved', $rate, after: $rate->only([
            'from_currency', 'to_currency', 'rate', 'effective_date', 'source',
        ]));

        return $rate;
    }

    public function convert(
        string|int|float $amount,
        string $fromCurrency,
        string $toCurrency,
        CarbonInterface|string $date,
    ): string {
        $from = $this->money->currency($fromCurrency);
        $to = $this->money->currency($toCurrency);
        if ($from === $to) {
            return $this->money->normalize($amount);
        }

        $rate = FinanceExchangeRate::query()
            ->where('from_currency', $from)
            ->where('to_currency', $to)
            ->whereDate('effective_date', '<=', $date)
            ->latest('effective_date')
            ->first();

        if (! $rate) {
            throw ValidationException::withMessages([
                'rate' => "No {$from} to {$to} exchange rate is available for this date.",
            ]);
        }

        return bcmul($this->money->normalize($amount), $rate->rate, 4);
    }
}
