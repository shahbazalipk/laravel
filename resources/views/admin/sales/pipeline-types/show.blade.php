@extends('admin.layout')

@section('title', $type->name)

@section('content')
<div class="mb-7 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
    <div>
        <p class="text-sm font-semibold uppercase tracking-wider text-indigo-600">Pipeline type</p>
        <h1 class="mt-1 text-3xl font-bold text-gray-900">{{ $type->name }}</h1>
        <p class="mt-1 font-mono text-sm text-gray-500">{{ $type->slug }}</p>
    </div>
    <div class="flex flex-wrap gap-2">
        <a href="{{ route('admin.sales.pipeline-types.edit', $type) }}" class="rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700" data-testid="edit-pipeline-type">Edit</a>
        <form action="{{ route('admin.sales.pipeline-types.duplicate', $type) }}" method="POST">@csrf<button type="submit" class="rounded-xl border border-gray-300 px-4 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50">Duplicate</button></form>
        <form action="{{ route('admin.sales.pipeline-types.toggle', $type) }}" method="POST">@csrf<button type="submit" class="rounded-xl border border-gray-300 px-4 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50">{{ $type->is_active ? 'Deactivate' : 'Activate' }}</button></form>
        <form action="{{ route('admin.sales.pipeline-types.destroy', $type) }}" method="POST" onsubmit="return confirm('Delete this pipeline type?');">@csrf @method('DELETE')<button type="submit" class="rounded-xl border border-red-200 px-4 py-2.5 text-sm font-semibold text-red-600 hover:bg-red-50">Delete</button></form>
        <a href="{{ route('admin.sales.pipelines.create', ['type' => $type->public_id]) }}" class="rounded-xl border border-indigo-200 bg-indigo-50 px-4 py-2.5 text-sm font-semibold text-indigo-700 hover:bg-indigo-100">Create pipeline</a>
    </div>
</div>

<div class="grid gap-6 lg:grid-cols-3">
    <div class="lg:col-span-2 space-y-6">
        <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm" data-testid="pipeline-type-stages-list">
            <h2 class="text-lg font-bold text-gray-900">Stages ({{ $type->stages->count() }})</h2>
            <div class="mt-4 space-y-2">
                @foreach($type->stages as $stage)
                    <div class="flex items-center justify-between rounded-xl border border-gray-100 bg-gray-50 px-4 py-3">
                        <div class="flex items-center gap-3">
                            <span class="h-3 w-3 rounded-full" style="background: {{ $stage->color ?? $type->color ?? '#4f46e5' }}"></span>
                            <span class="font-semibold text-gray-900">{{ $stage->name }}</span>
                            @if($stage->is_default)<span class="rounded-full bg-indigo-100 px-2 py-0.5 text-xs font-semibold text-indigo-700">Default</span>@endif
                        </div>
                        <span class="text-sm text-gray-500">{{ $stage->category->label() }} · {{ $stage->probability }}%</span>
                    </div>
                @endforeach
            </div>
        </section>

        <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-bold text-gray-900">Custom fields</h2>
            @foreach(['pipeline' => 'Pipeline-level', 'deal' => 'Deal-level'] as $scope => $label)
                @php $fields = $type->fields->filter(fn($f) => $f->scope->value === $scope) @endphp
                <div class="mt-4">
                    <h3 class="text-sm font-semibold uppercase tracking-wider text-gray-500">{{ $label }}</h3>
                    @forelse($fields as $field)
                        <div class="mt-2 flex items-center justify-between rounded-lg border border-gray-100 px-3 py-2 text-sm">
                            <span class="font-medium text-gray-900">{{ $field->label }}</span>
                            <span class="text-gray-500">{{ $field->type->label() }}</span>
                        </div>
                    @empty
                        <p class="mt-2 text-sm text-gray-500">No {{ strtolower($label) }} fields.</p>
                    @endforelse
                </div>
            @endforeach
        </section>
    </div>

    <aside class="space-y-4">
        <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
            <dl class="space-y-3 text-sm">
                <div><dt class="font-semibold text-gray-500">Status</dt><dd class="mt-1 font-semibold text-gray-900">{{ $type->is_active ? 'Active' : 'Inactive' }}</dd></div>
                <div><dt class="font-semibold text-gray-500">Pipelines</dt><dd class="mt-1 font-semibold text-gray-900">{{ $type->pipelines->count() }}</dd></div>
                <div><dt class="font-semibold text-gray-500">Updated</dt><dd class="mt-1 text-gray-900">{{ $type->updated_at->diffForHumans() }}</dd></div>
            </dl>
        </div>
        @if($type->pipelines->isNotEmpty())
            <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
                <h3 class="font-bold text-gray-900">Pipelines using this type</h3>
                <ul class="mt-3 space-y-2">
                    @foreach($type->pipelines as $pipeline)
                        <li><a href="{{ route('admin.sales.pipelines.show', $pipeline) }}" class="text-sm font-semibold text-indigo-600 hover:text-indigo-800">{{ $pipeline->name }}</a></li>
                    @endforeach
                </ul>
            </div>
        @endif
    </aside>
</div>
@endsection
