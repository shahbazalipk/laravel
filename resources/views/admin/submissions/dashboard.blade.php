@extends('admin.layout')

@section('title', 'Abstracts Dashboard')

@section('content')
<div data-testid="submission-dashboard" class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <p class="text-sm font-semibold text-indigo-600">Speaker & Abstract Management</p>
            <h2 class="text-3xl font-bold text-slate-900">Submission overview</h2>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('admin.submissions.types.create') }}" class="rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700">New submission type</a>
            <a href="{{ route('admin.submissions.index') }}" class="rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">View submissions</a>
        </div>
    </div>

    <div class="grid grid-cols-2 lg:grid-cols-6 gap-4">
        @foreach($metrics as $label => $value)
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ str_replace('_', ' ', $label) }}</p>
                <p class="mt-2 text-3xl font-bold text-slate-900">{{ number_format($value) }}</p>
            </div>
        @endforeach
    </div>

    <div class="grid lg:grid-cols-3 gap-6">
        <section class="lg:col-span-2 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex items-center justify-between mb-5">
                <h3 class="font-bold text-slate-900">Submission types</h3>
                <a class="text-sm font-semibold text-indigo-600" href="{{ route('admin.submissions.types.index') }}">Configure</a>
            </div>
            <div class="space-y-4">
                @forelse($byType as $type)
                    <div class="flex items-center gap-4">
                        <div class="min-w-0 flex-1">
                            <div class="flex justify-between text-sm"><span class="truncate font-medium text-slate-800">{{ $type->name }}</span><span class="font-semibold">{{ $type->submissions_count }}</span></div>
                            <div class="mt-2 h-2 rounded-full bg-slate-100"><div class="h-2 rounded-full bg-indigo-500" style="width: {{ $metrics['total'] ? max(4, ($type->submissions_count / $metrics['total']) * 100) : 0 }}%"></div></div>
                        </div>
                    </div>
                @empty
                    <p class="py-10 text-center text-slate-500">Create a submission type to begin accepting applications.</p>
                @endforelse
            </div>
        </section>
        <aside class="rounded-2xl bg-slate-900 p-6 text-white shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-widest text-indigo-300">Review health</p>
            <h3 class="mt-2 text-xl font-bold">Keep decisions moving</h3>
            <p class="mt-3 text-sm leading-6 text-slate-300">Use the reviewer workspace to balance assignments, resolve conflicts, and follow overdue reviews.</p>
            <a href="{{ route('admin.submissions.reviewers.index') }}" class="mt-6 inline-flex rounded-xl bg-white px-4 py-2.5 text-sm font-semibold text-slate-900">Manage reviewers</a>
        </aside>
    </div>
</div>
@endsection
