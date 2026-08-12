<?php

namespace App\Payments\Services;

use App\Models\Group;
use App\Payments\Enums\PaymentEntryStatus;
use App\Payments\Enums\PaymentEntryType;
use App\Payments\Enums\RegistrationPaymentSummaryStatus;
use App\Payments\Models\GroupPaymentEntry;
use Illuminate\Support\Collection;

class GroupPaymentTotals
{
    /**
     * @return array{
     *   group_total: float,
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
    public function calculate(Group $group, ?Collection $entries = null): array
    {
        $entries ??= $group->paymentEntries()
            ->orderBy('occurred_at')
            ->orderBy('id')
            ->get();

        $grossPaid = 0.0;
        $refunded = 0.0;
        $reversed = 0.0;
        $hasFailedAttempt = false;

        foreach ($entries as $entry) {
            /** @var GroupPaymentEntry $entry */
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

        $groupTotal = $group->billingTotal();
        $netPaid = round($grossPaid - $refunded - $reversed, 2);
        $balanceDue = round(max(0, $groupTotal - $netPaid), 2);
        $overpayment = round(max(0, $netPaid - $groupTotal), 2);

        $registrationTotals = app(RegistrationPaymentTotals::class);

        return [
            'group_total' => $groupTotal,
            'gross_paid' => round($grossPaid, 2),
            'refunded' => round($refunded, 2),
            'reversed' => round($reversed, 2),
            'net_paid' => $netPaid,
            'balance_due' => $balanceDue,
            'overpayment' => $overpayment,
            'currency' => $group->billingCurrency(),
            'summary_status' => $registrationTotals->deriveSummaryStatus(
                $groupTotal,
                $grossPaid,
                $refunded,
                $reversed,
                $netPaid,
                $hasFailedAttempt
            ),
            'has_failed_attempt' => $hasFailedAttempt,
        ];
    }
}
