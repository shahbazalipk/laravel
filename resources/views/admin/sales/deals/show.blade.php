@extends('admin.layout')

@section('title', $deal->title)

@section('content')
<div class="mb-7 flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
    <div>
        <p class="text-sm font-semibold uppercase tracking-wider text-indigo-600">Deal</p>
        <h1 class="mt-1 text-3xl font-bold text-gray-900">{{ $deal->title }}</h1>
        <p class="mt-1 font-mono text-sm text-gray-500">{{ $deal->reference }}</p>
    </div>
    <div class="flex flex-wrap gap-2">
        <a href="{{ route('admin.sales.deals.edit', $deal) }}" class="rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700" data-testid="edit-deal">Edit</a>
        <form action="{{ route('admin.sales.deals.destroy', $deal) }}" method="POST" onsubmit="return confirm('Delete this deal?');">@csrf @method('DELETE')<button type="submit" class="rounded-xl border border-red-200 px-4 py-2.5 text-sm font-semibold text-red-600">Delete</button></form>
    </div>
</div>

<div class="grid gap-6 lg:grid-cols-3">
    <div class="lg:col-span-2 space-y-6">
        <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm" data-testid="deal-summary">
            <dl class="grid gap-4 sm:grid-cols-2">
                <div><dt class="text-sm text-gray-500">Value</dt><dd class="text-2xl font-bold text-indigo-600">{{ format_money($deal->value) }}</dd></div>
                <div><dt class="text-sm text-gray-500">Probability</dt><dd class="text-2xl font-bold text-gray-900">{{ $deal->probability ?? 0 }}%</dd></div>
                <div><dt class="text-sm text-gray-500">Pipeline</dt><dd class="font-semibold"><a href="{{ route('admin.sales.pipelines.show', $deal->pipeline) }}" class="text-indigo-600">{{ $deal->pipeline?->name }}</a></dd></div>
                <div><dt class="text-sm text-gray-500">Status</dt><dd class="font-semibold">{{ $deal->status->label() }}</dd></div>
            </dl>
            @if($deal->description)
                <p class="mt-4 text-sm leading-6 text-gray-600">{{ $deal->description }}</p>
            @endif
        </section>

        @if($deal->contacts->isNotEmpty())
            <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
                <h2 class="text-lg font-bold text-gray-900">Contacts</h2>
                @foreach($deal->contacts as $contact)
                    <div class="mt-3 rounded-xl border border-gray-100 bg-gray-50 px-4 py-3">
                        <p class="font-semibold text-gray-900">{{ $contact->name }}</p>
                        <p class="text-sm text-gray-600">{{ $contact->email }} · {{ $contact->phone }}</p>
                    </div>
                @endforeach
            </section>
        @endif

        <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm" data-testid="deal-activities">
            <h2 class="text-lg font-bold text-gray-900">Activity</h2>
            <ul class="mt-3 space-y-2 text-sm">
                @forelse($deal->activities as $activity)
                    <li class="rounded-lg bg-slate-50 px-3 py-2 text-gray-700">
                        <span class="font-medium">{{ $activity->summary }}</span>
                        <span class="ml-2 text-xs text-gray-500">{{ $activity->created_at?->format('M j, g:i A') }}</span>
                    </li>
                @empty
                    <li class="text-gray-500">No activity yet.</li>
                @endforelse
            </ul>
        </section>
    </div>

    <aside class="space-y-4">
        <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm" data-testid="deal-move-stage">
            <h2 class="font-bold text-gray-900">Move stage</h2>
            <form action="{{ route('admin.sales.deals.move-stage', $deal) }}" method="POST" class="mt-4 space-y-3">
                @csrf
                <select name="sales_pipeline_stage_id" required class="w-full rounded-xl border border-gray-300 px-3 py-2 text-sm">
                    @foreach($deal->pipeline->stages as $stage)
                        <option value="{{ $stage->id }}" @selected($deal->sales_pipeline_stage_id === $stage->id)>{{ $stage->name }}</option>
                    @endforeach
                </select>
                <textarea name="note" rows="2" placeholder="Note (optional)" class="w-full rounded-xl border border-gray-300 px-3 py-2 text-sm"></textarea>
                <button type="submit" class="w-full rounded-xl bg-indigo-600 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700">Move</button>
            </form>
        </section>

        <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
            <h2 class="font-bold text-gray-900">Stage history</h2>
            <ul class="mt-3 space-y-2 text-sm">
                @forelse($deal->stageHistories as $history)
                    <li class="text-gray-600">{{ $history->created_at->format('M j, g:i A') }} → {{ $history->toStage?->name }}</li>
                @empty
                    <li class="text-gray-500">No history yet.</li>
                @endforelse
            </ul>
        </section>
    </aside>
</div>
@endsection
