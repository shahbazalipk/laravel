@extends('admin.layout')

@section('title', $pipeline->name.' Kanban')

@section('content')
<div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
    <div>
        <p class="text-sm font-semibold uppercase tracking-wider text-indigo-600">Kanban</p>
        <h1 class="mt-1 text-2xl font-bold text-gray-900">{{ $pipeline->name }}</h1>
        <p class="mt-1 text-sm text-gray-500">Drag deals between stages to update them.</p>
    </div>
    <a href="{{ route('admin.sales.pipelines.show', $pipeline) }}" class="text-sm font-semibold text-indigo-600 hover:text-indigo-800">← Back to pipeline</a>
</div>

<div id="kanban-status" class="mb-4 hidden rounded-xl border px-4 py-2 text-sm" role="status"></div>

<div class="flex gap-4 overflow-x-auto pb-4" data-testid="kanban-board">
    @foreach($pipeline->stages as $stage)
        @php $stageDeals = $dealsByStage->get($stage->id, collect()) @endphp
        <div class="min-w-[280px] flex-shrink-0 rounded-2xl border border-gray-200 bg-gray-50"
             data-testid="kanban-column-{{ $stage->public_id }}"
             data-stage-id="{{ $stage->id }}"
             data-stage-public-id="{{ $stage->public_id }}">
            <div class="border-b border-gray-200 px-4 py-3">
                <div class="flex items-center justify-between">
                    <h2 class="font-bold text-gray-900">{{ $stage->name }}</h2>
                    <span class="kanban-count rounded-full bg-white px-2 py-0.5 text-xs font-semibold text-gray-600">{{ $stageDeals->count() }}</span>
                </div>
                <p class="text-xs text-gray-500">{{ $stage->probability }}% · {{ $stage->category->label() }}</p>
            </div>
            <div class="kanban-dropzone min-h-[120px] space-y-3 p-3" data-stage-id="{{ $stage->id }}">
                @foreach($stageDeals as $deal)
                    <div draggable="true"
                         class="kanban-card cursor-grab rounded-xl border border-gray-200 bg-white p-4 shadow-sm transition hover:shadow-md active:cursor-grabbing"
                         data-testid="kanban-card-{{ $deal->public_id }}"
                         data-deal-id="{{ $deal->public_id }}"
                         data-move-url="{{ route('admin.sales.deals.move-stage', $deal) }}">
                        <a href="{{ route('admin.sales.deals.show', $deal) }}" class="font-semibold text-gray-900 hover:text-indigo-700" onclick="event.stopPropagation()">{{ $deal->title }}</a>
                        <p class="mt-1 text-xs text-gray-500">{{ $deal->reference }}</p>
                        <p class="mt-3 text-sm font-bold text-indigo-600">{{ format_money($deal->value) }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    @endforeach
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
    const statusEl = document.getElementById('kanban-status');
    let dragged = null;

    function showStatus(message, ok = true) {
        statusEl.textContent = message;
        statusEl.classList.remove('hidden', 'border-red-200', 'bg-red-50', 'text-red-700', 'border-emerald-200', 'bg-emerald-50', 'text-emerald-700');
        statusEl.classList.add(ok ? 'border-emerald-200' : 'border-red-200', ok ? 'bg-emerald-50' : 'bg-red-50', ok ? 'text-emerald-700' : 'text-red-700');
    }

    function refreshCounts() {
        document.querySelectorAll('[data-stage-id]').forEach((col) => {
            const zone = col.querySelector('.kanban-dropzone');
            const count = col.querySelector('.kanban-count');
            if (zone && count) count.textContent = String(zone.querySelectorAll('.kanban-card').length);
        });
    }

    document.querySelectorAll('.kanban-card').forEach((card) => {
        card.addEventListener('dragstart', (e) => {
            dragged = card;
            card.classList.add('opacity-50');
            e.dataTransfer.effectAllowed = 'move';
            e.dataTransfer.setData('text/plain', card.dataset.dealId);
        });
        card.addEventListener('dragend', () => {
            card.classList.remove('opacity-50');
            dragged = null;
            document.querySelectorAll('.kanban-dropzone').forEach((z) => z.classList.remove('ring-2', 'ring-indigo-300'));
        });
    });

    document.querySelectorAll('.kanban-dropzone').forEach((zone) => {
        zone.addEventListener('dragover', (e) => {
            e.preventDefault();
            zone.classList.add('ring-2', 'ring-indigo-300');
        });
        zone.addEventListener('dragleave', () => zone.classList.remove('ring-2', 'ring-indigo-300'));
        zone.addEventListener('drop', async (e) => {
            e.preventDefault();
            zone.classList.remove('ring-2', 'ring-indigo-300');
            if (!dragged) return;

            const previousParent = dragged.parentElement;
            zone.appendChild(dragged);
            refreshCounts();

            try {
                const response = await fetch(dragged.dataset.moveUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrf,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify({ sales_pipeline_stage_id: Number(zone.dataset.stageId) }),
                });
                if (!response.ok) {
                    previousParent?.appendChild(dragged);
                    refreshCounts();
                    const payload = await response.json().catch(() => ({}));
                    showStatus(payload.message || 'Could not move deal.', false);
                    return;
                }
                showStatus('Deal moved.', true);
            } catch (err) {
                previousParent?.appendChild(dragged);
                refreshCounts();
                showStatus('Network error while moving deal.', false);
            }
        });
    });
});
</script>
@endsection
