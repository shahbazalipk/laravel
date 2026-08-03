@extends('admin.layout')

@section('title', 'Promo Code Details')

@section('content')
<div class="mb-6" data-testid="promo-code-show-page">
    <div class="mb-4 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div class="flex items-start gap-3">
            <a href="{{ route('admin.promo-codes.index') }}"
               class="mt-1 text-gray-600 hover:text-gray-900"
               aria-label="Back to promo codes">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
            </a>
            <div>
                <div class="flex flex-wrap items-center gap-3">
                    <h1 class="font-mono text-2xl font-bold text-gray-800" data-testid="promo-code-detail-code">{{ $promoCode->code }}</h1>
                    @if($promoCode->is_active)
                        <span class="inline-flex rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-800">Active</span>
                    @else
                        <span class="inline-flex rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-800">Inactive</span>
                    @endif
                    @if($promoCode->isExpired())
                        <span class="inline-flex rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-800">Expired</span>
                    @endif
                </div>
                <p class="mt-1 text-gray-600">
                    {{ $promoCode->name ?: 'Promo code details and email allowlist' }}
                </p>
            </div>
        </div>

        <div class="flex flex-wrap gap-2">
            <form action="{{ route('admin.promo-codes.toggle', $promoCode) }}" method="POST">
                @csrf
                <button type="submit"
                        class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50"
                        data-testid="promo-code-detail-toggle">
                    {{ $promoCode->is_active ? 'Deactivate' : 'Activate' }}
                </button>
            </form>
            <a href="{{ route('admin.promo-codes.edit', $promoCode) }}"
               class="inline-flex items-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-indigo-700"
               data-testid="promo-code-detail-edit">
                Edit promo
            </a>
        </div>
    </div>

    <div class="mb-6 rounded-lg bg-white p-6 shadow-sm" data-testid="promo-code-detail-summary">
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
            <div>
                <h3 class="mb-1 text-sm font-medium text-gray-500">Discount</h3>
                <p class="text-lg font-semibold text-gray-900">{{ $promoCode->discountLabel() }}</p>
                <p class="text-xs text-gray-500">{{ $promoCode->discount_type->label() }}</p>
            </div>
            <div>
                <h3 class="mb-1 text-sm font-medium text-gray-500">Usage</h3>
                <p class="text-lg font-semibold text-gray-900">{{ $promoCode->usageLabel() }}</p>
                @if($promoCode->max_uses_per_email)
                    <p class="text-xs text-gray-500">{{ $promoCode->max_uses_per_email }} use(s) per email</p>
                @else
                    <p class="text-xs text-gray-500">No per-email limit</p>
                @endif
                @if(($usageSummary['total_emails'] ?? 0) > 0)
                    <p class="mt-1 text-xs font-medium text-slate-600" data-testid="promo-code-usage-email-summary">
                        {{ $usageSummary['used_emails'] }} of {{ $usageSummary['total_emails'] }} allowlisted email{{ $usageSummary['total_emails'] === 1 ? '' : 's' }} used
                    </p>
                @endif
            </div>
            <div>
                <h3 class="mb-1 text-sm font-medium text-gray-500">Validity</h3>
                @if($promoCode->starts_at || $promoCode->expires_at)
                    <p class="text-sm font-semibold text-gray-900">
                        {{ $promoCode->starts_at?->format('M d, Y H:i') ?? 'Now' }}
                    </p>
                    <p class="text-xs text-gray-500">
                        to {{ $promoCode->expires_at?->format('M d, Y H:i') ?? 'No expiry' }}
                    </p>
                @else
                    <p class="text-sm font-semibold text-gray-900">No date limits</p>
                @endif
            </div>
            <div>
                <h3 class="mb-1 text-sm font-medium text-gray-500">Email allowlist</h3>
                @if($promoCode->restrict_to_email_list)
                    <p class="text-lg font-semibold text-indigo-700" data-testid="promo-code-email-count">
                        {{ $promoCode->emails_count }} email{{ $promoCode->emails_count === 1 ? '' : 's' }}
                    </p>
                    <p class="text-xs text-gray-500">Restricted to listed emails</p>
                @else
                    <p class="text-lg font-semibold text-gray-900">Open</p>
                    <p class="text-xs text-gray-500">Anyone with the code can use it</p>
                @endif
            </div>
        </div>

        @if($promoCode->description)
            <div class="mt-6 border-t border-gray-100 pt-4">
                <h3 class="mb-1 text-sm font-medium text-gray-500">Description</h3>
                <p class="text-sm text-gray-700 whitespace-pre-line">{{ $promoCode->description }}</p>
            </div>
        @endif
    </div>

    <div class="rounded-lg bg-white shadow-sm" data-testid="promo-code-emails-section">
        <div class="flex flex-col gap-4 border-b border-gray-100 px-6 py-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-lg font-semibold text-gray-800">Allowlist emails</h2>
                <p class="mt-0.5 text-sm text-gray-500">
                    @if($promoCode->restrict_to_email_list)
                        Only these emails can redeem {{ $promoCode->code }}.
                    @else
                        This promo is unrestricted. Listed emails are kept for when you enable restriction.
                    @endif
                </p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <button type="button"
                        onclick="document.getElementById('addEmailModal').classList.remove('hidden')"
                        class="inline-flex items-center rounded-lg bg-indigo-600 px-3 py-2 text-sm font-medium text-white transition hover:bg-indigo-700"
                        data-testid="promo-code-add-email">
                    <svg class="mr-1.5 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Add email
                </button>
                <a href="{{ route('admin.promo-codes.edit', $promoCode) }}"
                   class="rounded-lg border border-indigo-200 bg-indigo-50 px-3 py-2 text-sm font-medium text-indigo-700 transition hover:bg-indigo-100"
                   data-testid="promo-code-upload-emails">
                    Upload / replace CSV
                </a>
                @if(($promoCode->emails_count ?? 0) > 0)
                    <form action="{{ route('admin.promo-codes.clear-emails', $promoCode) }}"
                          method="POST"
                          onsubmit="return confirm('Clear the entire email allowlist?');">
                        @csrf
                        <button type="submit"
                                class="rounded-lg border border-red-200 px-3 py-2 text-sm font-medium text-red-600 transition hover:bg-red-50"
                                data-testid="promo-code-clear-emails">
                            Clear list
                        </button>
                    </form>
                @endif
            </div>
        </div>

        <div class="border-b border-gray-100 px-6 py-4">
            <form method="GET" action="{{ route('admin.promo-codes.show', $promoCode) }}" class="flex flex-col gap-3 sm:flex-row sm:items-center">
                <input type="search"
                       name="q"
                       value="{{ $search }}"
                       placeholder="Search emails…"
                       class="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm focus:border-transparent focus:ring-2 focus:ring-indigo-500"
                       data-testid="promo-code-email-search">
                @if($usageStatus !== '')
                    <input type="hidden" name="status" value="{{ $usageStatus }}">
                @endif
                <button type="submit"
                        class="rounded-lg bg-gray-800 px-4 py-2 text-sm font-medium text-white transition hover:bg-gray-900"
                        data-testid="promo-code-email-search-submit">
                    Search
                </button>
                @if($search !== '' || $usageStatus !== '')
                    <a href="{{ route('admin.promo-codes.show', $promoCode) }}"
                       class="inline-flex items-center justify-center rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                        Clear
                    </a>
                @endif
            </form>

            <div class="mt-3 flex flex-wrap gap-2" data-testid="promo-code-usage-filters">
                <a href="{{ route('admin.promo-codes.show', array_filter(['promo_code' => $promoCode, 'q' => $search !== '' ? $search : null])) }}"
                   class="rounded-full px-3 py-1 text-xs font-semibold transition {{ $usageStatus === '' ? 'bg-slate-800 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}"
                   data-testid="promo-code-filter-all">
                    All ({{ $usageSummary['total_emails'] }})
                </a>
                <a href="{{ route('admin.promo-codes.show', array_filter(['promo_code' => $promoCode, 'q' => $search !== '' ? $search : null, 'status' => 'used'])) }}"
                   class="rounded-full px-3 py-1 text-xs font-semibold transition {{ $usageStatus === 'used' ? 'bg-emerald-700 text-white' : 'bg-emerald-50 text-emerald-800 hover:bg-emerald-100' }}"
                   data-testid="promo-code-filter-used">
                    Used ({{ $usageSummary['used_emails'] }})
                </a>
                <a href="{{ route('admin.promo-codes.show', array_filter(['promo_code' => $promoCode, 'q' => $search !== '' ? $search : null, 'status' => 'unused'])) }}"
                   class="rounded-full px-3 py-1 text-xs font-semibold transition {{ $usageStatus === 'unused' ? 'bg-amber-700 text-white' : 'bg-amber-50 text-amber-800 hover:bg-amber-100' }}"
                   data-testid="promo-code-filter-unused">
                    Unused ({{ $usageSummary['unused_emails'] }})
                </a>
            </div>
        </div>

        @if($emails->isEmpty())
            <div class="px-6 py-12 text-center" data-testid="promo-code-emails-empty">
                <svg class="mx-auto mb-3 h-12 w-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                </svg>
                <p class="text-sm font-medium text-gray-800">
                    {{ $search !== '' ? 'No emails match your search.' : 'No emails in the allowlist yet.' }}
                </p>
                <p class="mt-1 text-sm text-gray-500">
                    Add emails one by one or upload a CSV.
                </p>
                @if($search === '')
                    <button type="button"
                            onclick="document.getElementById('addEmailModal').classList.remove('hidden')"
                            class="mt-4 inline-flex items-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">
                        Add email
                    </button>
                @endif
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200" data-testid="promo-code-emails-table">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">#</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Email</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Added</th>
                            <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        @foreach($emails as $index => $emailRow)
                            @php $timesUsed = (int) ($emailRow->times_used ?? 0); @endphp
                            <tr class="hover:bg-gray-50" data-testid="promo-code-email-row-{{ $emailRow->id }}">
                                <td class="px-6 py-3 text-sm text-gray-500">
                                    {{ $emails->firstItem() + $index }}
                                </td>
                                <td class="px-6 py-3 text-sm font-medium text-gray-900" data-testid="promo-code-email-value-{{ $emailRow->id }}">
                                    {{ $emailRow->email }}
                                </td>
                                <td class="px-6 py-3 text-sm" data-testid="promo-code-email-status-{{ $emailRow->id }}">
                                    @if($timesUsed > 0)
                                        <span class="inline-flex items-center rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-semibold text-emerald-800"
                                              data-testid="promo-code-email-used-{{ $emailRow->id }}">
                                            Used{{ $timesUsed > 1 ? " ({$timesUsed}×)" : '' }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-600"
                                              data-testid="promo-code-email-unused-{{ $emailRow->id }}">
                                            Unused
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-3 text-sm text-gray-500">
                                    {{ $emailRow->created_at?->format('M d, Y H:i') ?? '—' }}
                                </td>
                                <td class="px-6 py-3 text-right text-sm font-medium">
                                    <div class="flex items-center justify-end gap-2">
                                        <button type="button"
                                                class="text-indigo-600 transition hover:text-indigo-900"
                                                title="Edit email"
                                                data-testid="promo-code-email-edit-{{ $emailRow->id }}"
                                                data-edit-email-id="{{ $emailRow->id }}"
                                                data-edit-email-value="{{ $emailRow->email }}"
                                                data-edit-email-url="{{ route('admin.promo-codes.emails.update', [$promoCode, $emailRow]) }}">
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                            </svg>
                                        </button>
                                        <form action="{{ route('admin.promo-codes.emails.destroy', [$promoCode, $emailRow]) }}"
                                              method="POST"
                                              onsubmit="return confirm('Remove {{ $emailRow->email }} from the allowlist?');"
                                              class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="text-red-600 transition hover:text-red-900"
                                                    title="Delete email"
                                                    data-testid="promo-code-email-delete-{{ $emailRow->id }}">
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

            @if($emails->hasPages())
                <div class="border-t border-gray-100 px-6 py-4">
                    {{ $emails->links() }}
                </div>
            @endif

            <div class="border-t border-gray-100 px-6 py-3 text-sm text-gray-500">
                Showing {{ $emails->firstItem() }}–{{ $emails->lastItem() }} of {{ $emails->total() }} email{{ $emails->total() === 1 ? '' : 's' }}
            </div>
        @endif
    </div>

    <div class="mt-6 rounded-lg bg-white shadow-sm" data-testid="promo-code-redemptions-section">
        <div class="border-b border-gray-100 px-6 py-4">
            <h2 class="text-lg font-semibold text-gray-800">Redemptions</h2>
            <p class="mt-0.5 text-sm text-gray-500">
                Registrations that redeemed {{ $promoCode->code }}
                ({{ $usageSummary['total_redemptions'] }} total).
            </p>
        </div>

        @if($redemptions->isEmpty())
            <div class="px-6 py-10 text-center" data-testid="promo-code-redemptions-empty">
                <p class="text-sm font-medium text-gray-800">No redemptions yet.</p>
                <p class="mt-1 text-sm text-gray-500">When someone uses this promo, they will appear here.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200" data-testid="promo-code-redemptions-table">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Registration</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Email</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Discount</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Redeemed</th>
                            <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        @foreach($redemptions as $registration)
                            <tr class="hover:bg-gray-50" data-testid="promo-code-redemption-{{ $registration->id }}">
                                <td class="px-6 py-3 text-sm font-medium text-gray-900">
                                    {{ trim(($registration->first_name ?? '').' '.($registration->last_name ?? '')) ?: '—' }}
                                    @if($registration->registration_number)
                                        <span class="mt-0.5 block font-mono text-xs text-gray-500">{{ $registration->registration_number }}</span>
                                    @endif
                                </td>
                                <td class="px-6 py-3 text-sm text-gray-700">{{ $registration->email }}</td>
                                <td class="px-6 py-3 text-sm text-emerald-700">
                                    {{ number_format((float) ($registration->discount_amount ?? 0), 2) }}
                                    {{ $registration->currency ?? $promoCode->currency ?? '' }}
                                </td>
                                <td class="px-6 py-3 text-sm text-gray-500">
                                    {{ $registration->created_at?->format('M d, Y H:i') ?? '—' }}
                                </td>
                                <td class="px-6 py-3 text-right text-sm">
                                    <a href="{{ route('admin.registrations.show', $registration) }}"
                                       class="font-medium text-indigo-600 hover:text-indigo-800"
                                       data-testid="promo-code-redemption-link-{{ $registration->id }}">
                                        View
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($redemptions->hasPages())
                <div class="border-t border-gray-100 px-6 py-4">
                    {{ $redemptions->links() }}
                </div>
            @endif
        @endif
    </div>
</div>

{{-- Add email modal --}}
<div id="addEmailModal"
     class="{{ $errors->has('email') && ! old('_editing_email_id') ? '' : 'hidden' }} fixed inset-0 z-50 overflow-y-auto bg-gray-600/50"
     data-testid="promo-code-add-email-modal">
    <div class="relative top-20 mx-auto w-full max-w-md rounded-lg border border-gray-200 bg-white p-6 shadow-lg">
        <div class="mb-4 flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-800">Add email</h3>
            <button type="button"
                    onclick="document.getElementById('addEmailModal').classList.add('hidden')"
                    class="text-gray-400 hover:text-gray-600"
                    aria-label="Close">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        <form method="POST" action="{{ route('admin.promo-codes.emails.store', $promoCode) }}" data-testid="promo-code-add-email-form">
            @csrf
            <label for="add_email" class="mb-2 block text-sm font-medium text-gray-700">Email address</label>
            <input type="email"
                   name="email"
                   id="add_email"
                   value="{{ old('_editing_email_id') ? '' : old('email') }}"
                   required
                   class="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm focus:border-transparent focus:ring-2 focus:ring-indigo-500 @error('email') border-red-500 @enderror"
                   placeholder="attendee@example.com"
                   data-testid="promo-code-add-email-input">
            @error('email')
                @if(! old('_editing_email_id'))
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @endif
            @enderror
            <div class="mt-5 flex justify-end gap-2">
                <button type="button"
                        onclick="document.getElementById('addEmailModal').classList.add('hidden')"
                        class="rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                    Cancel
                </button>
                <button type="submit"
                        class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700"
                        data-testid="promo-code-add-email-submit">
                    Add email
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Edit email modal --}}
<div id="editEmailModal"
     class="{{ $errors->has('email') && old('_editing_email_id') ? '' : 'hidden' }} fixed inset-0 z-50 overflow-y-auto bg-gray-600/50"
     data-testid="promo-code-edit-email-modal">
    <div class="relative top-20 mx-auto w-full max-w-md rounded-lg border border-gray-200 bg-white p-6 shadow-lg">
        <div class="mb-4 flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-800">Edit email</h3>
            <button type="button"
                    onclick="document.getElementById('editEmailModal').classList.add('hidden')"
                    class="text-gray-400 hover:text-gray-600"
                    aria-label="Close">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        <form method="POST"
              id="editEmailForm"
              action="{{ old('_editing_email_url', '#') }}"
              data-testid="promo-code-edit-email-form">
            @csrf
            @method('PUT')
            <input type="hidden" name="_editing_email_id" id="edit_email_id" value="{{ old('_editing_email_id') }}">
            <input type="hidden" name="_editing_email_url" id="edit_email_url" value="{{ old('_editing_email_url') }}">
            <label for="edit_email" class="mb-2 block text-sm font-medium text-gray-700">Email address</label>
            <input type="email"
                   name="email"
                   id="edit_email"
                   value="{{ old('_editing_email_id') ? old('email') : '' }}"
                   required
                   class="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm focus:border-transparent focus:ring-2 focus:ring-indigo-500 @error('email') border-red-500 @enderror"
                   data-testid="promo-code-edit-email-input">
            @error('email')
                @if(old('_editing_email_id'))
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @endif
            @enderror
            <div class="mt-5 flex justify-end gap-2">
                <button type="button"
                        onclick="document.getElementById('editEmailModal').classList.add('hidden')"
                        class="rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                    Cancel
                </button>
                <button type="submit"
                        class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700"
                        data-testid="promo-code-edit-email-submit">
                    Save changes
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('[data-edit-email-id]').forEach((button) => {
            button.addEventListener('click', () => {
                const form = document.getElementById('editEmailForm');
                const modal = document.getElementById('editEmailModal');
                const emailInput = document.getElementById('edit_email');
                const idInput = document.getElementById('edit_email_id');
                const urlInput = document.getElementById('edit_email_url');

                form.action = button.dataset.editEmailUrl;
                emailInput.value = button.dataset.editEmailValue || '';
                idInput.value = button.dataset.editEmailId || '';
                urlInput.value = button.dataset.editEmailUrl || '';
                modal.classList.remove('hidden');
                emailInput.focus();
            });
        });
    });
</script>
@endpush
