<?php

namespace App\Payments\Services;

use App\Payments\Enums\PaymentEntryStatus;
use App\Payments\Enums\PaymentEntryType;
use App\Payments\Enums\RegistrationPaymentSummaryStatus;
use App\Payments\Models\RegistrationPaymentEntry;
use App\Models\Registration;
use Illuminate\Support\Collection;

class RegistrationPaymentTotals
{
    /**
     * @return array{
     *   registration_total: float,
     *   gross_paid: float,
     *   refunded: float,
     *   reversed: float,
     *   net_paid: float,
     *   balance_due: float,
     *   overpayment: float,
     *   currency: string,
     *   summary_status: RegistrationPaymentSummaryStatus,
     *   has_failed_attempt: bool
     * }
     */
    public function calculate(Registration $registration, ?Collection $entries = null): array
    {
        $entries ??= $registration->paymentEntries()
            ->orderBy('occurred_at')
            ->orderBy('id')
            ->get();

        $grossPaid = 0.0;
        $refunded = 0.0;
        $reversed = 0.0;
        $hasFailedAttempt = false;

        foreach ($entries as $entry) {
            /** @var RegistrationPaymentEntry $entry */
            if ($entry->status === PaymentEntryStatus::Failed) {
                $hasFailedAttempt = true;
                continue;
            }

            if ($entry->type === PaymentEntryType::Payment) {
                $grossPaid += (float) $entry->amount;
            } elseif ($entry->type === PaymentEntryType::Refund) {
                $refunded += (float) $entry->amount;
            } elseif ($entry->type === PaymentEntryType::Reversal) {
                $reversed += (float) $entry->amount;
            }
        }

        $registrationTotal = round((float) ($registration->total_amount ?? 0), 2);
        $netPaid = round($grossPaid - $refunded - $reversed, 2);
        $balanceDue = round(max(0, $registrationTotal - $netPaid), 2);
        $overpayment = round(max(0, $netPaid - $registrationTotal), 2);

        return [
            'registration_total' => $registrationTotal,
            'gross_paid' => round($grossPaid, 2),
            'refunded' => round($refunded, 2),
            'reversed' => round($reversed, 2),
            'net_paid' => $netPaid,
            'balance_due' => $balanceDue,
            'overpayment' => $overpayment,
            'currency' => $registration->currency ?: 'AED',
            'summary_status' => $this->deriveSummaryStatus(
                $registrationTotal,
                $grossPaid,
                $refunded,
                $reversed,
                $netPaid,
                $hasFailedAttempt
            ),
            'has_failed_attempt' => $hasFailedAttempt,
        ];
    }

    public function deriveSummaryStatus(
        float $registrationTotal,
        float $grossPaid,
        float $refunded,
        float $reversed,
        float $netPaid,
        bool $hasFailedAttempt
    ): RegistrationPaymentSummaryStatus {
        // Free / zero-price registrations with nothing paid are fully settled.
        if ($registrationTotal <= 0.0 && $netPaid <= 0.0 && $grossPaid <= 0.0) {
            return $hasFailedAttempt
                ? RegistrationPaymentSummaryStatus::Failed
                : RegistrationPaymentSummaryStatus::Paid;
        }

        if ($netPaid <= 0.0) {
            if ($grossPaid > 0.0 && ($refunded + $reversed) >= $grossPaid) {
                return RegistrationPaymentSummaryStatus::Refunded;
            }

            if ($hasFailedAttempt && $grossPaid <= 0.0) {
                return RegistrationPaymentSummaryStatus::Failed;
            }

            return RegistrationPaymentSummaryStatus::Pending;
        }

        if ($refunded > 0.0 && $netPaid > 0.0 && $netPaid < $registrationTotal) {
            return RegistrationPaymentSummaryStatus::PartiallyRefunded;
        }

        if ($netPaid > $registrationTotal) {
            return RegistrationPaymentSummaryStatus::Overpaid;
        }

        if (abs($netPaid - $registrationTotal) < 0.009) {
            return RegistrationPaymentSummaryStatus::Paid;
        }

        if ($netPaid > 0.0 && $netPaid < $registrationTotal) {
            return RegistrationPaymentSummaryStatus::PartiallyPaid;
        }

        return RegistrationPaymentSummaryStatus::Pending;
    }
}
