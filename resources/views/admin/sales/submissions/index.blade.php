@extends('admin.layout')

@section('title', 'Form Submissions')

@section('content')
<div class="mb-7 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
    <div>
        <p class="text-sm font-semibold uppercase tracking-wider text-indigo-600">Sales</p>
        <h1 class="mt-1 text-3xl font-bold text-gray-900">Form Submissions</h1>
    </div>
    <a href="{{ route('admin.sales.submissions.export', request()->query()) }}"
       class="rounded-xl border border-indigo-200 bg-indigo-50 px-4 py-2.5 text-sm font-semibold text-indigo-700 hover:bg-indigo-100"
       data-testid="export-submissions">Export CSV</a>
</div>

<form method="GET" class="mb-6 grid gap-3 rounded-2xl border border-gray-200 bg-white p-4 sm:grid-cols-4">
    <input type="search" name="search" value="{{ request('search') }}" placeholder="Search…"
           class="rounded-xl border border-gray-300 px-4 py-2 text-sm sm:col-span-2">
    <select name="status" class="rounded-xl border border-gray-300 px-4 py-2 text-sm">
        <option value="">All statuses</option>
        @foreach($statuses as $status)
            <option value="{{ $status->value }}" @selected(request('status') === $status->value)>{{ $status->label() }}</option>
        @endforeach
    </select>
    <button class="rounded-xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white">Filter</button>
</form>

<div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm" data-testid="submissions-table">
    <table class="min-w-full divide-y divide-gray-200 text-sm">
        <thead class="bg-gray-50 text-left text-xs font-semibold uppercase text-gray-500">
            <tr>
                <th class="px-4 py-3">Reference</th>
                <th class="px-4 py-3">Form</th>
                <th class="px-4 py-3">Submitter</th>
                <th class="px-4 py-3">Status</th>
                <th class="px-4 py-3">Submitted</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse($submissions as $submission)
                <tr>
                    <td class="px-4 py-3">
                        <a href="{{ route('admin.sales.submissions.show', $submission) }}" class="font-semibold text-indigo-600 hover:text-indigo-800">
                            {{ $submission->reference }}
                        </a>
                    </td>
                    <td class="px-4 py-3">{{ $submission->form?->name }}</td>
                    <td class="px-4 py-3">
                        <div>{{ $submission->submitter_name ?: '—' }}</div>
                        <div class="text-xs text-gray-500">{{ $submission->submitter_email }}</div>
                    </td>
                    <td class="px-4 py-3">{{ $submission->status->label() }}</td>
                    <td class="px-4 py-3 text-gray-500">{{ $submission->created_at?->format('M d, Y H:i') }}</td>
                </tr>
            @empty
                <tr><td colspan="5" class="px-4 py-12 text-center text-gray-500">No submissions yet.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $submissions->links() }}</div>
@endsection
