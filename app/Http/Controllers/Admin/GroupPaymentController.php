<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\RefundRegistrationPaymentRequest;
use App\Http\Requests\Admin\ReverseRegistrationPaymentRequest;
use App\Http\Requests\Admin\StoreRegistrationPaymentRequest;
use App\Models\Group;
use App\Payments\Models\GroupPaymentEntry;
use App\Payments\Services\RecordGroupPayment;
use Illuminate\Http\RedirectResponse;
use InvalidArgumentException;

class GroupPaymentController extends Controller
{
    public function __construct(
        private RecordGroupPayment $payments
    ) {}

    public function store(
        StoreRegistrationPaymentRequest $request,
        Group $group
    ): RedirectResponse {
        try {
            $this->payments->recordPayment($group, $request->validated());
        } catch (InvalidArgumentException $exception) {
            return back()
                ->withInput()
                ->with('error', $exception->getMessage())
                ->with('open_group_payment_modal', true);
        }

        return redirect()
            ->route('admin.groups.show', $group)
            ->with('success', 'Group payment recorded successfully.');
    }

    public function refund(
        RefundRegistrationPaymentRequest $request,
        Group $group,
        GroupPaymentEntry $payment
    ): RedirectResponse {
        $this->ensurePaymentBelongsToGroup($group, $payment);

        try {
            $this->payments->recordRefund($group, $payment, array_merge(
                $request->validated(),
                ['status' => 'succeeded']
            ));
        } catch (InvalidArgumentException $exception) {
            return back()->withInput()->with('error', $exception->getMessage());
        }

        return redirect()
            ->route('admin.groups.show', $group)
            ->with('success', 'Group refund recorded successfully.');
    }

    public function reverse(
        ReverseRegistrationPaymentRequest $request,
        Group $group,
        GroupPaymentEntry $payment
    ): RedirectResponse {
        $this->ensurePaymentBelongsToGroup($group, $payment);

        try {
            $this->payments->recordReversal($group, $payment, $request->validated());
        } catch (InvalidArgumentException $exception) {
            return back()->withInput()->with('error', $exception->getMessage());
        }

        return redirect()
            ->route('admin.groups.show', $group)
            ->with('success', 'Group payment entry reversed successfully.');
    }

    private function ensurePaymentBelongsToGroup(Group $group, GroupPaymentEntry $payment): void
    {
        abort_unless(
            (int) $payment->event_group_id === (int) $group->id
            && (int) $payment->event_id === (int) $group->event_id
            && (int) $payment->org_id === (int) $group->org_id,
            404
        );
    }
}
