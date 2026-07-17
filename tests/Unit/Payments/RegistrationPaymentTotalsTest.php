<?php

namespace Tests\Unit\Payments;

use App\Models\Registration;
use App\Payments\Enums\PaymentEntryStatus;
use App\Payments\Enums\PaymentEntryType;
use App\Payments\Enums\RegistrationPaymentSummaryStatus;
use App\Payments\Models\RegistrationPaymentEntry;
use App\Payments\Services\RegistrationPaymentTotals;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class RegistrationPaymentTotalsTest extends TestCase
{
    private RegistrationPaymentTotals $totals;

    protected function setUp(): void
    {
        parent::setUp();
        $this->totals = new RegistrationPaymentTotals;
    }

    #[Test]
    public function it_calculates_partial_payment_totals_and_status(): void
    {
        $registration = $this->registration(100);
        $entries = collect([
            $this->entry(PaymentEntryType::Payment, 40, PaymentEntryStatus::Succeeded),
            $this->entry(PaymentEntryType::Payment, 20, PaymentEntryStatus::Succeeded),
        ]);

        $result = $this->totals->calculate($registration, $entries);

        $this->assertSame(100.0, $result['registration_total']);
        $this->assertSame(60.0, $result['gross_paid']);
        $this->assertSame(0.0, $result['refunded']);
        $this->assertSame(60.0, $result['net_paid']);
        $this->assertSame(40.0, $result['balance_due']);
        $this->assertSame(RegistrationPaymentSummaryStatus::PartiallyPaid, $result['summary_status']);
    }

    #[Test]
    public function it_allows_overpayment_and_marks_overpaid(): void
    {
        $registration = $this->registration(100);
        $entries = collect([
            $this->entry(PaymentEntryType::Payment, 120, PaymentEntryStatus::Succeeded),
        ]);

        $result = $this->totals->calculate($registration, $entries);

        $this->assertSame(120.0, $result['net_paid']);
        $this->assertSame(0.0, $result['balance_due']);
        $this->assertSame(20.0, $result['overpayment']);
        $this->assertSame(RegistrationPaymentSummaryStatus::Overpaid, $result['summary_status']);
    }

    #[Test]
    public function it_tracks_refunds_and_partially_refunded_status(): void
    {
        $registration = $this->registration(100);
        $entries = collect([
            $this->entry(PaymentEntryType::Payment, 100, PaymentEntryStatus::Succeeded),
            $this->entry(PaymentEntryType::Refund, 30, PaymentEntryStatus::Succeeded),
        ]);

        $result = $this->totals->calculate($registration, $entries);

        $this->assertSame(100.0, $result['gross_paid']);
        $this->assertSame(30.0, $result['refunded']);
        $this->assertSame(70.0, $result['net_paid']);
        $this->assertSame(30.0, $result['balance_due']);
        $this->assertSame(RegistrationPaymentSummaryStatus::PartiallyRefunded, $result['summary_status']);
    }

    #[Test]
    public function it_marks_fully_refunded_when_net_is_zero(): void
    {
        $registration = $this->registration(100);
        $entries = collect([
            $this->entry(PaymentEntryType::Payment, 100, PaymentEntryStatus::Succeeded),
            $this->entry(PaymentEntryType::Refund, 100, PaymentEntryStatus::Succeeded),
        ]);

        $result = $this->totals->calculate($registration, $entries);

        $this->assertSame(0.0, $result['net_paid']);
        $this->assertSame(RegistrationPaymentSummaryStatus::Refunded, $result['summary_status']);
    }

    #[Test]
    public function it_ignores_failed_payments_in_totals_but_can_mark_failed(): void
    {
        $registration = $this->registration(100);
        $entries = collect([
            $this->entry(PaymentEntryType::Payment, 100, PaymentEntryStatus::Failed),
        ]);

        $result = $this->totals->calculate($registration, $entries);

        $this->assertSame(0.0, $result['gross_paid']);
        $this->assertTrue($result['has_failed_attempt']);
        $this->assertSame(RegistrationPaymentSummaryStatus::Failed, $result['summary_status']);
    }

    #[Test]
    public function it_marks_zero_price_registrations_as_paid_when_nothing_is_owed(): void
    {
        $registration = $this->registration(0);
        $entries = collect([]);

        $result = $this->totals->calculate($registration, $entries);

        $this->assertSame(0.0, $result['registration_total']);
        $this->assertSame(0.0, $result['balance_due']);
        $this->assertSame(RegistrationPaymentSummaryStatus::Paid, $result['summary_status']);
    }

    #[Test]
    public function it_subtracts_reversals_from_net_paid(): void
    {
        $registration = $this->registration(100);
        $entries = collect([
            $this->entry(PaymentEntryType::Payment, 50, PaymentEntryStatus::Succeeded),
            $this->entry(PaymentEntryType::Reversal, 50, PaymentEntryStatus::Succeeded),
        ]);

        $result = $this->totals->calculate($registration, $entries);

        $this->assertSame(50.0, $result['gross_paid']);
        $this->assertSame(50.0, $result['reversed']);
        $this->assertSame(0.0, $result['net_paid']);
        $this->assertSame(RegistrationPaymentSummaryStatus::Refunded, $result['summary_status']);
    }

    private function registration(float $total): Registration
    {
        $registration = new Registration;
        $registration->forceFill([
            'total_amount' => $total,
            'currency' => 'AED',
            'payment_status' => 'pending',
        ]);

        return $registration;
    }

    private function entry(
        PaymentEntryType $type,
        float $amount,
        PaymentEntryStatus $status
    ): RegistrationPaymentEntry {
        $entry = new RegistrationPaymentEntry;
        $entry->forceFill([
            'type' => $type,
            'status' => $status,
            'amount' => $amount,
            'currency' => 'AED',
        ]);

        return $entry;
    }
}
