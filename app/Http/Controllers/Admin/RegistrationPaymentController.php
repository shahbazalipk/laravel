<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\RefundRegistrationPaymentRequest;
use App\Http\Requests\Admin\ReverseRegistrationPaymentRequest;
use App\Http\Requests\Admin\StoreRegistrationPaymentRequest;
use App\Models\Registration;
use App\Payments\Models\RegistrationPaymentEntry;
use App\Payments\Services\RecordRegistrationPayment;
use Illuminate\Http\RedirectResponse;
use InvalidArgumentException;

class RegistrationPaymentController extends Controller
{
    public function __construct(
        private RecordRegistrationPayment $payments
    ) {}

    public function store(
        StoreRegistrationPaymentRequest $request,
        Registration $registration
    ): RedirectResponse {
        try {
            $this->payments->recordPayment($registration, $request->validated());
        } catch (InvalidArgumentException $exception) {
            return back()
                ->withInput()
                ->with('error', $exception->getMessage())
                ->with('open_payment_modal', true);
        }

        return redirect()
            ->route('admin.registrations.show', $registration)
            ->with('success', 'Payment entry recorded successfully.');
    }

    public function refund(
        RefundRegistrationPaymentRequest $request,
        Registration $registration,
        RegistrationPaymentEntry $payment
    ): RedirectResponse {
        $this->ensurePaymentBelongsToRegistration($registration, $payment);

        try {
            $this->payments->recordRefund($registration, $payment, array_merge(
                $request->validated(),
                ['status' => 'succeeded']
            ));
        } catch (InvalidArgumentException $exception) {
            return back()->withInput()->with('error', $exception->getMessage());
        }

        return redirect()
            ->route('admin.registrations.show', $registration)
            ->with('success', 'Refund recorded successfully.');
    }

    public function reverse(
        ReverseRegistrationPaymentRequest $request,
        Registration $registration,
        RegistrationPaymentEntry $payment
    ): RedirectResponse {
        $this->ensurePaymentBelongsToRegistration($registration, $payment);

        try {
            $this->payments->recordReversal($registration, $payment, $request->validated());
        } catch (InvalidArgumentException $exception) {
            return back()->withInput()->with('error', $exception->getMessage());
        }

        return redirect()
            ->route('admin.registrations.show', $registration)
            ->with('success', 'Payment entry reversed successfully.');
    }

    private function ensurePaymentBelongsToRegistration(
        Registration $registration,
        RegistrationPaymentEntry $payment
    ): void {
        abort_unless(
            (int) $payment->registration_id === (int) $registration->id
            && (int) $payment->event_id === (int) $registration->event_id
            && (int) $payment->org_id === (int) $registration->org_id,
            404
        );
    }
}
