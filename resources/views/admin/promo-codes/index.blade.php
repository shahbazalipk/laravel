@extends('admin.layout')

@section('title', 'Promo Codes')

@section('content')
<div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between" data-testid="promo-codes-page">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Promo Codes</h1>
        <p class="mt-1 text-gray-600">Create discount codes with percentage or fixed amounts, expiry, and usage limits.</p>
    </div>
    <a href="{{ route('admin.promo-codes.create') }}"
       class="inline-flex items-center justify-center rounded-lg bg-indigo-600 px-4 py-2 text-white transition hover:bg-indigo-700"
       data-testid="promo-codes-create">
        <svg class="mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
        </svg>
        Add Promo Code
    </a>
</div>

@if($promoCodes->isEmpty())
    <div class="rounded-lg bg-white p-12 text-center shadow-sm" data-testid="promo-codes-empty">
        <svg class="mx-auto mb-4 h-16 w-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
        </svg>
        <h3 class="mb-2 text-lg font-semibold text-gray-800">No promo codes yet</h3>
        <p class="mb-4 text-gray-600">Create your first discount code for this event.</p>
        <a href="{{ route('admin.promo-codes.create') }}"
           class="inline-flex items-center rounded-lg bg-indigo-600 px-4 py-2 text-white transition hover:bg-indigo-700">
            Add First Promo Code
        </a>
    </div>
@else
    <div class="overflow-hidden rounded-lg bg-white shadow-sm" data-testid="promo-codes-table">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Code</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Discount</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Validity</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Usage</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Status</th>
                        <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                    @foreach($promoCodes as $promoCode)
                        <tr class="hover:bg-gray-50 transition" data-testid="promo-code-row-{{ $promoCode->id }}">
                            <td class="px-6 py-4">
                                <a href="{{ route('admin.promo-codes.show', $promoCode) }}" class="group">
                                    <div class="font-mono text-sm font-semibold text-gray-900 group-hover:text-indigo-700">{{ $promoCode->code }}</div>
                                    @if($promoCode->name)
                                        <div class="text-sm text-gray-500">{{ $promoCode->name }}</div>
                                    @endif
                                </a>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ $promoCode->discountLabel() }}</div>
                                <div class="text-xs text-gray-500">{{ $promoCode->discount_type->label() }}</div>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600">
                                @if($promoCode->starts_at || $promoCode->expires_at)
                                    <div>{{ $promoCode->starts_at?->format('M d, Y') ?? 'Now' }}</div>
                                    <div class="text-xs text-gray-500">to {{ $promoCode->expires_at?->format('M d, Y H:i') ?? 'No expiry' }}</div>
                                    @if($promoCode->isExpired())
                                        <span class="mt-1 inline-flex rounded-full bg-amber-100 px-2 py-0.5 text-[11px] font-semibold text-amber-800">Expired</span>
                                    @endif
                                @else
                                    <span class="text-gray-400">No date limits</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                <div>{{ $promoCode->usageLabel() }}</div>
                                @if($promoCode->max_uses_per_email)
                                    <div class="text-xs text-gray-500">{{ $promoCode->max_uses_per_email }} per email</div>
                                @endif
                                @if($promoCode->restrict_to_email_list)
                                    <div class="mt-1 text-xs font-medium text-indigo-700" data-testid="promo-allowlist-badge-{{ $promoCode->id }}">
                                        Email list · {{ $promoCode->emails_count ?? 0 }}
                                    </div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <form action="{{ route('admin.promo-codes.toggle', $promoCode) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="focus:outline-none" data-testid="promo-code-toggle-{{ $promoCode->id }}">
                                        @if($promoCode->is_active)
                                            <span class="inline-flex rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-800 hover:bg-green-200">Active</span>
                                        @else
                                            <span class="inline-flex rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-800 hover:bg-gray-200">Inactive</span>
                                        @endif
                                    </button>
                                </form>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex justify-end space-x-2">
                                    <a href="{{ route('admin.promo-codes.show', $promoCode) }}"
                                       class="text-slate-600 transition hover:text-slate-900"
                                       title="View details"
                                       data-testid="promo-code-view-{{ $promoCode->id }}">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>
                                    </a>
                                    <a href="{{ route('admin.promo-codes.edit', $promoCode) }}"
                                       class="text-indigo-600 transition hover:text-indigo-900"
                                       title="Edit"
                                       data-testid="promo-code-edit-{{ $promoCode->id }}">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                        </svg>
                                    </a>
                                    <form action="{{ route('admin.promo-codes.destroy', $promoCode) }}"
                                          method="POST"
                                          onsubmit="return confirm('Delete promo code {{ $promoCode->code }}?');"
                                          class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="text-red-600 transition hover:text-red-900"
                                                title="Delete"
                                                data-testid="promo-code-delete-{{ $promoCode->id }}">
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M9 7V4a1 1 0 011-1h4a1 1 0 011 1v3m-7 0h8"></path>
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4 text-sm text-gray-600">
        Total: {{ $promoCodes->count() }} promo code{{ $promoCodes->count() !== 1 ? 's' : '' }}
    </div>
@endif
@endsection
