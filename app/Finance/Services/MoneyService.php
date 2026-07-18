<?php

namespace App\Finance\Services;

use InvalidArgumentException;

class MoneyService
{
    public function normalize(string|int|float $amount, int $scale = 4): string
    {
        $value = is_float($amount)
            ? number_format($amount, $scale, '.', '')
            : trim((string) $amount);

        if (! preg_match('/^-?\d+(?:\.\d+)?$/', $value)) {
            throw new InvalidArgumentException('A valid monetary amount is required.');
        }

        return bcadd($value, '0', $scale);
    }

    public function positive(string|int|float $amount, int $scale = 4): string
    {
        $normalized = $this->normalize($amount, $scale);

        if (bccomp($normalized, '0', $scale) !== 1) {
            throw new InvalidArgumentException('The amount must be greater than zero.');
        }

        return $normalized;
    }

    public function nonNegative(string|int|float $amount, int $scale = 4): string
    {
        $normalized = $this->normalize($amount, $scale);

        if (bccomp($normalized, '0', $scale) === -1) {
            throw new InvalidArgumentException('The amount cannot be negative.');
        }

        return $normalized;
    }

    public function currency(string $currency): string
    {
        $normalized = strtoupper(trim($currency));

        if (! preg_match('/^[A-Z]{3}$/', $normalized)) {
            throw new InvalidArgumentException('A valid ISO 4217 currency code is required.');
        }

        return $normalized;
    }

    public function multiply(string|int|float $amount, string|int|float $rate, int $scale = 4): string
    {
        return bcmul($this->normalize($amount, $scale), $this->positive($rate, 8), $scale);
    }
}
