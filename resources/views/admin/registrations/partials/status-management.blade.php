@php
    use App\Payments\Enums\RegistrationPaymentSummaryStatus;
    $summaryStatus = $paymentSummary['summary_status'] instanceof RegistrationPaymentSummaryStatus
        ? $paymentSummary['summary_status']
        : RegistrationPaymentSummaryStatus::from($registration->payment_status ?: 'pending');
@endphp

<div class="bg-white rounded-lg shadow-sm p-6" data-testid="registration-status-card">
    <div class="flex items-start justify-between gap-3 mb-4">
        <div>
            <h3 class="text-lg font-semibold text-gray-800">Registration Status</h3>
            <p class="text-sm text-gray-500 mt-1">Independent from payment and check-in state.</p>
        </div>
        @if($registration->registrationStatus)
            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold"
                  style="background-color: {{ $registration->registrationStatus->color }}20; color: {{ $registration->registrationStatus->color }};"
                  data-testid="current-registration-status">
                {{ $registration->registrationStatus->name }}
            </span>
        @else
            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-700"
                  data-testid="current-registration-status">
                Unassigned
            </span>
        @endif
    </div>

    <form action="{{ route('admin.registrations.status.update', $registration) }}"
          method="POST"
          class="space-y-4"
          data-testid="registration-status-form">
        @csrf
        @method('PATCH')

        <div>
            <label for="registration_status_id" class="block text-sm font-medium text-gray-700 mb-2">
                Status
            </label>
            <select name="registration_status_id"
                    id="registration_status_id"
                    required
                    class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200 @error('registration_status_id') border-red-500 @enderror"
                    data-testid="registration-status-select">
                <option value="">Select status</option>
                @foreach($statuses as $status)
                    <option value="{{ $status->id }}"
                        {{ (string) old('registration_status_id', $registration->registration_status_id) === (string) $status->id ? 'selected' : '' }}
                        {{ !$status->is_active && (int) $status->id !== (int) $registration->registration_status_id ? 'disabled' : '' }}>
                        {{ $status->name }}{{ !$status->is_active ? ' (inactive)' : '' }}
                    </option>
                @endforeach
            </select>
            @error('registration_status_id')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <button type="submit"
                class="w-full rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-indigo-700"
                data-testid="registration-status-save">
            Save Status
        </button>
    </form>
</div>
