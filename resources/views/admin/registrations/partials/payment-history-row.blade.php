@php
    use App\Payments\Enums\PaymentEntryStatus;
    use App\Payments\Enums\PaymentEntryType;

    $typeBadge = match ($entry->type) {
        PaymentEntryType::Payment => 'bg-emerald-100 text-emerald-800',
        PaymentEntryType::Refund => 'bg-rose-100 text-rose-800',
        PaymentEntryType::Reversal => 'bg-slate-100 text-slate-800',
    };
    $statusBadge = $entry->status === PaymentEntryStatus::Succeeded
        ? 'bg-green-100 text-green-800'
        : 'bg-red-100 text-red-800';
    $refundable = (float) ($refundableByPayment[$entry->id] ?? 0);
    $canRefund = $entry->isPayment() && $entry->isSucceeded() && $refundable > 0;
    $alreadyReversed = $entry->isSucceeded()
        && !$entry->isReversal()
        && $registration->paymentEntries
            ->where('reverses_entry_id', $entry->id)
            ->where('type', PaymentEntryType::Reversal)
            ->where('status', PaymentEntryStatus::Succeeded)
            ->isNotEmpty();
    $canReverse = $entry->isSucceeded() && !$entry->isReversal() && !$alreadyReversed;
@endphp

@if(($layout ?? 'table') === 'table')
<tr class="hover:bg-gray-50" data-testid="payment-entry-{{ $entry->public_id }}">
    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700">
        {{ $entry->occurred_at?->format('M d, Y H:i') }}
    </td>
    <td class="px-4 py-3 whitespace-nowrap">
        <div class="flex flex-wrap gap-1">
            <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-semibold {{ $typeBadge }}">{{ $entry->type->label() }}</span>
            <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-semibold {{ $statusBadge }}">{{ $entry->status->label() }}</span>
        </div>
    </td>
    <td class="px-4 py-3 whitespace-nowrap text-sm font-semibold text-gray-900">
        {{ number_format($entry->amount, 2) }} {{ $entry->currency }}
    </td>
    <td class="px-4 py-3 text-sm text-gray-600">
        <div class="space-y-1">
            @if($entry->method)<div>Method: {{ $entry->method }}</div>@endif
            @if($entry->reference)<div>Ref: {{ $entry->reference }}</div>@endif
            @if($entry->notes)<div class="text-gray-500">{{ $entry->notes }}</div>@endif
            @if($entry->reverses_entry_id)
                <div class="text-xs text-indigo-600">Linked to entry #{{ $entry->reverses_entry_id }}</div>
            @endif
        </div>
    </td>
    <td class="px-4 py-3 text-sm text-gray-600">
        <div>{{ $entry->recorded_by_name ?: 'System' }}</div>
        @if($entry->recorded_by_email)
            <div class="text-xs text-gray-400">{{ $entry->recorded_by_email }}</div>
        @endif
    </td>
    <td class="px-4 py-3 text-right text-sm">
        <div class="inline-flex flex-wrap justify-end gap-2">
            @if($canRefund)
                <button type="button"
                        class="rounded-md border border-rose-200 px-2.5 py-1.5 text-xs font-medium text-rose-700 hover:bg-rose-50"
                        onclick="openRefundModal(@js($entry->public_id), @js($refundable), @js($entry->currency))"
                        data-testid="refund-payment-{{ $entry->public_id }}">
                    Refund
                </button>
            @endif
            @if($canReverse)
                <form action="{{ route('admin.registrations.payments.reverse', [$registration, $entry]) }}"
                      method="POST"
                      onsubmit="return confirm('Reverse this entry? This cannot be undone.');">
                    @csrf
                    <button type="submit"
                            class="rounded-md border border-slate-200 px-2.5 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-50"
                            data-testid="reverse-payment-{{ $entry->public_id }}">
                        Reverse
                    </button>
                </form>
            @endif
        </div>
    </td>
</tr>
@else
<div class="rounded-xl border border-gray-200 p-4" data-testid="payment-entry-{{ $entry->public_id }}">
    <div class="flex items-start justify-between gap-3">
        <div>
            <p class="text-sm font-semibold text-gray-900">{{ number_format($entry->amount, 2) }} {{ $entry->currency }}</p>
            <p class="mt-1 text-xs text-gray-500">{{ $entry->occurred_at?->format('M d, Y H:i') }}</p>
        </div>
        <div class="flex flex-wrap justify-end gap-1">
            <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-semibold {{ $typeBadge }}">{{ $entry->type->label() }}</span>
            <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-semibold {{ $statusBadge }}">{{ $entry->status->label() }}</span>
        </div>
    </div>
    <div class="mt-3 space-y-1 text-sm text-gray-600">
        @if($entry->method)<div>Method: {{ $entry->method }}</div>@endif
        @if($entry->reference)<div>Ref: {{ $entry->reference }}</div>@endif
        @if($entry->notes)<div>{{ $entry->notes }}</div>@endif
        <div>By: {{ $entry->recorded_by_name ?: 'System' }}</div>
    </div>
    <div class="mt-3 flex flex-wrap gap-2">
        @if($canRefund)
            <button type="button"
                    class="rounded-md border border-rose-200 px-2.5 py-1.5 text-xs font-medium text-rose-700 hover:bg-rose-50"
                    onclick="openRefundModal(@js($entry->public_id), @js($refundable), @js($entry->currency))"
                    data-testid="refund-payment-{{ $entry->public_id }}">
                Refund
            </button>
        @endif
        @if($canReverse)
            <form action="{{ route('admin.registrations.payments.reverse', [$registration, $entry]) }}"
                  method="POST"
                  onsubmit="return confirm('Reverse this entry? This cannot be undone.');">
                @csrf
                <button type="submit"
                        class="rounded-md border border-slate-200 px-2.5 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-50"
                        data-testid="reverse-payment-{{ $entry->public_id }}">
                    Reverse
                </button>
            </form>
        @endif
    </div>
</div>
@endif
