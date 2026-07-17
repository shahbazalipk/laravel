<?php

namespace Tests\Unit\Payments;

use App\Models\Registration;
use App\Payments\Enums\PaymentEntryStatus;
use App\Payments\Enums\PaymentEntryType;
use App\Payments\Models\RegistrationPaymentEntry;
use App\Payments\Services\RecordRegistrationPayment;
use App\Payments\Services\RegistrationPaymentTotals;
use App\Services\AuditService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use InvalidArgumentException;
use LogicException;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class RecordRegistrationPaymentTest extends TestCase
{
    private RecordRegistrationPayment $recorder;

    protected function setUp(): void
    {
        parent::setUp();

        config(['event.event_id' => 1, 'event.org_id' => 1]);

        Schema::dropIfExists('registration_payment_entries');
        Schema::dropIfExists('registrations');
        Schema::dropIfExists('hash_mappings');

        Schema::create('hash_mappings', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('event_id');
            $table->unsignedBigInteger('org_id');
            $table->string('hash', 64)->unique();
            $table->string('model_type');
            $table->unsignedBigInteger('model_id');
            $table->timestamps();
        });

        Schema::create('registrations', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('event_id');
            $table->unsignedBigInteger('org_id');
            $table->string('registration_number')->nullable();
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->string('currency', 10)->default('AED');
            $table->string('payment_status', 32)->default('pending');
            $table->string('payment_method')->nullable();
            $table->string('payment_reference')->nullable();
            $table->timestamp('payment_date')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('registration_payment_entries', function (Blueprint $table): void {
            $table->id();
            $table->uuid('public_id')->unique();
            $table->unsignedBigInteger('event_id')->index();
            $table->unsignedBigInteger('org_id')->index();
            $table->unsignedBigInteger('registration_id')->index();
            $table->string('type', 32);
            $table->string('status', 32);
            $table->decimal('amount', 12, 2);
            $table->string('currency', 10);
            $table->string('method', 100)->nullable();
            $table->string('reference')->nullable();
            $table->timestamp('occurred_at');
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('reverses_entry_id')->nullable();
            $table->string('recorded_by_type')->nullable();
            $table->unsignedBigInteger('recorded_by_id')->nullable();
            $table->string('recorded_by_name')->nullable();
            $table->string('recorded_by_email')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });

        $audit = Mockery::mock(AuditService::class);
        $audit->shouldReceive('log')->andReturnNull();
        $this->app->instance(AuditService::class, $audit);

        $this->recorder = new RecordRegistrationPayment(
            new RegistrationPaymentTotals,
            $audit
        );
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('registration_payment_entries');
        Schema::dropIfExists('registrations');
        Schema::dropIfExists('hash_mappings');
        Mockery::close();
        parent::tearDown();
    }

    #[Test]
    public function it_records_partial_payments_and_syncs_summary(): void
    {
        $registration = $this->makeRegistration(100);

        $this->recorder->recordPayment($registration, [
            'amount' => 40,
            'status' => 'succeeded',
            'method' => 'Card',
            'reference' => 'PAY-1',
            'occurred_at' => now()->subMinute(),
        ]);

        $this->recorder->recordPayment($registration->fresh(), [
            'amount' => 60,
            'status' => 'succeeded',
            'method' => 'Card',
            'reference' => 'PAY-2',
            'occurred_at' => now(),
        ]);

        $registration->refresh();

        $this->assertSame(2, $registration->paymentEntries()->count());
        $this->assertSame('paid', $registration->payment_status);
        $this->assertSame('Card', $registration->payment_method);
        $this->assertSame('PAY-2', $registration->payment_reference);
    }

    #[Test]
    public function it_rejects_over_refunds(): void
    {
        $registration = $this->makeRegistration(100);
        $payment = $this->recorder->recordPayment($registration, [
            'amount' => 50,
            'status' => 'succeeded',
        ]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Refund amount exceeds');

        $this->recorder->recordRefund($registration->fresh(), $payment, [
            'amount' => 60,
            'status' => 'succeeded',
        ]);
    }

    #[Test]
    public function it_records_refunds_within_refundable_balance(): void
    {
        $registration = $this->makeRegistration(100);
        $payment = $this->recorder->recordPayment($registration, [
            'amount' => 100,
            'status' => 'succeeded',
        ]);

        $refund = $this->recorder->recordRefund($registration->fresh(), $payment, [
            'amount' => 25,
            'status' => 'succeeded',
            'reference' => 'RF-1',
        ]);

        $registration->refresh();

        $this->assertSame(PaymentEntryType::Refund, $refund->type);
        $this->assertSame('partially_refunded', $registration->payment_status);
        $this->assertSame(75.0, (float) (new RegistrationPaymentTotals)->calculate($registration)['net_paid']);
    }

    #[Test]
    public function it_prevents_duplicate_reversals(): void
    {
        $registration = $this->makeRegistration(100);
        $payment = $this->recorder->recordPayment($registration, [
            'amount' => 100,
            'status' => 'succeeded',
        ]);

        $this->recorder->recordReversal($registration->fresh(), $payment, [
            'notes' => 'Correction',
        ]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('already been reversed');

        $this->recorder->recordReversal($registration->fresh(), $payment->fresh(), []);
    }

    #[Test]
    public function it_rolls_back_when_recording_fails_after_lock(): void
    {
        $registration = $this->makeRegistration(100);

        try {
            $this->recorder->recordPayment($registration, [
                'amount' => 0,
                'status' => 'succeeded',
            ]);
            $this->fail('Expected InvalidArgumentException');
        } catch (InvalidArgumentException) {
            // expected
        }

        $this->assertSame(0, RegistrationPaymentEntry::query()->count());
        $this->assertSame('pending', $registration->fresh()->payment_status);
    }

    #[Test]
    public function payment_entries_are_immutable(): void
    {
        $registration = $this->makeRegistration(100);
        $payment = $this->recorder->recordPayment($registration, [
            'amount' => 10,
            'status' => 'succeeded',
        ]);

        $this->expectException(LogicException::class);
        $payment->update(['notes' => 'changed']);
    }

    #[Test]
    public function payment_entries_cannot_be_deleted(): void
    {
        $registration = $this->makeRegistration(100);
        $payment = $this->recorder->recordPayment($registration, [
            'amount' => 10,
            'status' => 'succeeded',
        ]);

        $this->expectException(LogicException::class);
        $payment->delete();
    }

    private function makeRegistration(float $total): Registration
    {
        return Registration::create([
            'event_id' => 1,
            'org_id' => 1,
            'registration_number' => 'REG-TEST-1',
            'total_amount' => $total,
            'currency' => 'AED',
            'payment_status' => 'pending',
        ]);
    }
}
