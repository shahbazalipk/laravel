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
        && ! $entry->isReversal()
        && $group->paymentEntries
            ->where('reverses_entry_id', $entry->id)
            ->where('type', PaymentEntryType::Reversal)
            ->where('status', PaymentEntryStatus::Succeeded)
            ->isNotEmpty();
    $canReverse = $entry->isSucceeded() && ! $entry->isReversal() && ! $alreadyReversed;
@endphp

@if(($layout ?? 'table') === 'table')
<tr>
    <td class="px-4 py-3 text-sm text-gray-700">{{ $entry->occurred_at?->format('M d, Y H:i') }}</td>
    <td class="px-4 py-3">
        <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-semibold {{ $typeBadge }}">{{ $entry->type->label() }}</span>
        <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-semibold {{ $statusBadge }}">{{ $entry->status->label() }}</span>
    </td>
    <td class="px-4 py-3 text-sm font-semibold">{{ number_format($entry->amount, 2) }} {{ $entry->currency }}</td>
    <td class="px-4 py-3 text-sm text-gray-600">
        @if($entry->method)<div>{{ $entry->method }}</div>@endif
        @if($entry->reference)<div>{{ $entry->reference }}</div>@endif
        @if($entry->notes)<div class="text-gray-500">{{ $entry->notes }}</div>@endif
    </td>
    <td class="px-4 py-3 text-right text-sm">
        @if($canRefund)
            <button type="button" class="text-rose-700 text-xs font-medium"
                    onclick="openGroupRefundModal(@js($entry->public_id), @js($refundable), @js($entry->currency))">Refund</button>
        @endif
        @if($canReverse)
            <form action="{{ route('admin.groups.payments.reverse', [$group, $entry]) }}" method="POST" class="inline" onsubmit="return confirm('Reverse this entry?')">
                @csrf
                <button type="submit" class="text-slate-700 text-xs font-medium">Reverse</button>
            </form>
        @endif
    </td>
</tr>
@else
<div class="rounded-xl border border-gray-200 p-4">
    <p class="text-sm font-semibold">{{ number_format($entry->amount, 2) }} {{ $entry->currency }}</p>
    <p class="text-xs text-gray-500">{{ $entry->occurred_at?->format('M d, Y H:i') }}</p>
</div>
@endif
