@extends('admin.layout')

@section('title', $submission->reference)

@section('content')
<div class="mb-6">
    <a href="{{ route('admin.sales.submissions.index') }}" class="text-sm font-semibold text-indigo-600">← Submissions</a>
    <h1 class="mt-3 text-3xl font-bold text-gray-900">{{ $submission->reference }}</h1>
    <p class="mt-1 text-gray-600">{{ $submission->form?->name }} · {{ $submission->status->label() }}</p>
</div>

<div class="grid gap-6 lg:grid-cols-3">
    <div class="space-y-6 lg:col-span-2">
        <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-gray-900">Answers</h2>
            <dl class="mt-4 grid gap-4 sm:grid-cols-2">
                @foreach(($submission->answers ?? []) as $key => $value)
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wide text-gray-500">{{ $key }}</dt>
                        <dd class="mt-1 font-medium text-gray-900">
                            @if(is_array($value))
                                {{ json_encode($value) }}
                            @else
                                {{ $value }}
                            @endif
                        </dd>
                    </div>
                @endforeach
            </dl>
            @if($submission->files->isNotEmpty())
                <h3 class="mt-6 text-sm font-semibold text-gray-800">Files</h3>
                <ul class="mt-2 space-y-2 text-sm">
                    @foreach($submission->files as $file)
                        <li class="rounded-lg bg-slate-50 px-3 py-2">{{ $file->original_name }} ({{ $file->field_key }})</li>
                    @endforeach
                </ul>
            @endif
        </section>
    </div>
    <div class="space-y-6">
        <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-gray-900">Actions</h2>
            <form method="POST" action="{{ route('admin.sales.submissions.status', $submission) }}" class="mt-4 space-y-3">
                @csrf @method('PATCH')
                <select name="status" class="w-full rounded-xl border border-gray-300 px-3 py-2 text-sm">
                    @foreach($statuses as $status)
                        <option value="{{ $status->value }}" @selected($submission->status === $status)>{{ $status->label() }}</option>
                    @endforeach
                </select>
                <div>
                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Assign admin ID</label>
                    <input type="number" name="assigned_admin_id" value="{{ old('assigned_admin_id', $submission->assigned_admin_id) }}"
                           class="w-full rounded-xl border border-gray-300 px-3 py-2 text-sm" placeholder="Admin ID">
                </div>
                <div>
                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-500">Internal notes</label>
                    <textarea name="internal_notes" rows="3" class="w-full rounded-xl border border-gray-300 px-3 py-2 text-sm"
                              placeholder="Notes for the sales team…">{{ old('internal_notes', $submission->internal_notes) }}</textarea>
                </div>
                <button class="w-full rounded-xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white">Save updates</button>
            </form>
            @if(! $submission->sales_deal_id)
                <form method="POST" action="{{ route('admin.sales.submissions.convert', $submission) }}" class="mt-3">
                    @csrf
                    <button class="w-full rounded-xl border border-indigo-200 bg-indigo-50 px-4 py-2 text-sm font-semibold text-indigo-700"
                            data-testid="convert-submission">Convert to Deal</button>
                </form>
            @else
                <a href="{{ route('admin.sales.deals.show', $submission->deal) }}" class="mt-3 block text-center text-sm font-semibold text-indigo-600">View linked deal</a>
            @endif
        </section>
        <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm text-sm text-gray-600">
            <p><span class="font-semibold text-gray-800">Email:</span> {{ $submission->submitter_email ?: '—' }}</p>
            <p class="mt-2"><span class="font-semibold text-gray-800">Company:</span> {{ $submission->company_name ?: '—' }}</p>
            <p class="mt-2"><span class="font-semibold text-gray-800">Source:</span> {{ $submission->source_url ?: '—' }}</p>
            <p class="mt-2"><span class="font-semibold text-gray-800">Domain:</span> {{ $submission->embed_domain ?: '—' }}</p>
        </section>
    </div>
</div>
@endsection
