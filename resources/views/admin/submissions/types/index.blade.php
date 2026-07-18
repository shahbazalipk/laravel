@extends('admin.layout')

@section('title', 'Submission Types')

@section('content')
<div class="space-y-6" data-testid="submission-types">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div><p class="text-sm font-semibold text-indigo-600">Configuration</p><h2 class="text-3xl font-bold text-slate-900">Submission types</h2></div>
        <a href="{{ route('admin.submissions.types.create') }}" class="rounded-xl bg-indigo-600 px-4 py-2.5 text-center text-sm font-semibold text-white">Create type</a>
    </div>
    <div class="grid md:grid-cols-2 xl:grid-cols-3 gap-5">
        @forelse($types as $type)
            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0"><p class="text-xs font-semibold uppercase text-slate-500">{{ $type->code }}</p><h3 class="mt-1 truncate text-lg font-bold text-slate-900">{{ $type->name }}</h3></div>
                    <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $type->status === 'active' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">{{ ucfirst($type->status) }}</span>
                </div>
                <p class="mt-3 line-clamp-2 text-sm text-slate-600">{{ $type->description ?: 'No description added.' }}</p>
                <div class="mt-5 grid grid-cols-2 gap-3 text-center">
                    <div class="rounded-xl bg-slate-50 p-3"><p class="text-xl font-bold">{{ $type->submissions_count }}</p><p class="text-xs text-slate-500">Submissions</p></div>
                    <div class="rounded-xl bg-slate-50 p-3"><p class="text-xl font-bold">{{ $type->questions_count }}</p><p class="text-xs text-slate-500">Questions</p></div>
                </div>
                <div class="mt-5 flex flex-wrap gap-2">
                    <a href="{{ route('admin.submissions.types.edit', $type) }}" class="rounded-lg bg-indigo-50 px-3 py-2 text-sm font-semibold text-indigo-700">Configure</a>
                    <form method="POST" action="{{ route('admin.submissions.types.duplicate', $type) }}">@csrf<button class="rounded-lg border px-3 py-2 text-sm font-semibold">Duplicate</button></form>
                </div>
            </article>
        @empty
            <div class="md:col-span-2 xl:col-span-3 rounded-2xl border border-dashed border-slate-300 bg-white p-12 text-center text-slate-500">No submission types yet.</div>
        @endforelse
    </div>
    {{ $types->links() }}
</div>
@endsection
