@php
    $memberCount = $group->registrations->count();
    $capacity = $group->allowed_attendees;
@endphp

<div class="bg-white rounded-lg shadow-sm p-6" data-testid="group-members-card">
    <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h3 class="text-lg font-semibold text-gray-800">Linked Registrations</h3>
            <p class="mt-0.5 text-sm text-gray-500">
                {{ $memberCount }} / {{ $capacity }} attendee slots used
            </p>
        </div>
        <a href="{{ route('admin.registrations.create', ['group_id' => $group->id, 'registration_type' => 'group']) }}"
           class="inline-flex items-center rounded-lg bg-indigo-600 px-3 py-2 text-sm font-semibold text-white hover:bg-indigo-700">
            + New registration
        </a>
    </div>

    @if($memberCount < $capacity && $availableRegistrations->isNotEmpty())
        <form action="{{ route('admin.groups.registrations.store', $group) }}"
              method="POST"
              class="mb-5 flex flex-col gap-3 rounded-xl border border-dashed border-indigo-200 bg-indigo-50/40 p-4 sm:flex-row sm:items-end"
              data-testid="link-registration-form">
            @csrf
            <div class="flex-1">
                <label for="registration_id" class="mb-1 block text-sm font-medium text-slate-700">
                    Link existing registration
                </label>
                <select id="registration_id"
                        name="registration_id"
                        required
                        class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200"
                        data-testid="link-registration-select">
                    <option value="">Select a registration…</option>
                    @foreach($availableRegistrations as $registration)
                        <option value="{{ $registration->id }}" {{ old('registration_id') == $registration->id ? 'selected' : '' }}>
                            {{ $registration->registration_number }} — {{ $registration->full_name }} ({{ $registration->email }})
                        </option>
                    @endforeach
                </select>
                @error('registration_id')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <button type="submit"
                    class="inline-flex items-center justify-center rounded-lg border border-indigo-200 bg-white px-4 py-2 text-sm font-semibold text-indigo-700 hover:bg-indigo-50"
                    data-testid="link-registration-submit">
                Link to group
            </button>
        </form>
    @elseif($memberCount >= $capacity)
        <div class="mb-5 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
            This group has reached its attendee capacity.
        </div>
    @endif

    @if($group->registrations->isEmpty())
        <div class="rounded-xl border border-dashed border-gray-300 bg-gray-50 px-4 py-8 text-center" data-testid="group-members-empty">
            <p class="text-sm font-medium text-gray-700">No registrations linked yet</p>
            <p class="mt-0.5 text-sm text-gray-500">Link an existing registration or create a new one for this group.</p>
        </div>
    @else
        <div class="hidden md:block overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200" data-testid="group-members-table">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Reg #</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Name</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Email</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Category</th>
                        <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                    @foreach($group->registrations as $registration)
                        <tr data-testid="group-member-{{ $registration->id }}">
                            <td class="px-4 py-3 text-sm font-medium text-gray-900">
                                <a href="{{ route('admin.registrations.show', $registration) }}" class="text-indigo-600 hover:text-indigo-800">
                                    {{ $registration->registration_number }}
                                </a>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-700">{{ $registration->full_name }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600">{{ $registration->email }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600">{{ $registration->registrationCategory->name ?? '—' }}</td>
                            <td class="px-4 py-3 text-right text-sm">
                                <form action="{{ route('admin.groups.registrations.destroy', [$group, $registration]) }}"
                                      method="POST"
                                      onsubmit="return confirm('Remove this registration from the group?')"
                                      class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-800 font-medium">Remove</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="space-y-3 md:hidden" data-testid="group-members-cards">
            @foreach($group->registrations as $registration)
                <div class="rounded-xl border border-gray-200 p-4">
                    <a href="{{ route('admin.registrations.show', $registration) }}"
                       class="text-sm font-semibold text-indigo-600">{{ $registration->registration_number }}</a>
                    <p class="mt-1 text-sm font-medium text-gray-900">{{ $registration->full_name }}</p>
                    <p class="text-xs text-gray-500">{{ $registration->email }}</p>
                    <form action="{{ route('admin.groups.registrations.destroy', [$group, $registration]) }}"
                          method="POST"
                          onsubmit="return confirm('Remove this registration from the group?')"
                          class="mt-3">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-sm font-medium text-red-600">Remove from group</button>
                    </form>
                </div>
            @endforeach
        </div>
    @endif
</div>
