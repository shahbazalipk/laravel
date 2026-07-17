@php
    $editing = isset($type);
@endphp
<div class="space-y-8">
    <div class="grid gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2">
            <label for="name" class="block text-sm font-semibold text-gray-800">Name</label>
            <input id="name" name="name" type="text" required maxlength="255"
                   value="{{ old('name', $type->name ?? '') }}"
                   class="mt-2 w-full rounded-xl border border-gray-300 px-4 py-3 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200"
                   data-testid="pipeline-type-name">
            @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>
        <div>
            <label for="color" class="block text-sm font-semibold text-gray-800">Color</label>
            <input id="color" name="color" type="color"
                   value="{{ old('color', $type->color ?? '#4f46e5') }}"
                   class="mt-2 h-12 w-full rounded-xl border border-gray-300 px-2 py-1"
                   data-testid="pipeline-type-color">
        </div>
        <div class="lg:col-span-3">
            <label for="slug" class="block text-sm font-semibold text-gray-800">Slug</label>
            <input id="slug" name="slug" type="text" maxlength="255"
                   value="{{ old('slug', $type->slug ?? '') }}"
                   class="mt-2 w-full rounded-xl border border-gray-300 px-4 py-3 font-mono text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200"
                   data-testid="pipeline-type-slug">
        </div>
        <div class="lg:col-span-3">
            <label for="description" class="block text-sm font-semibold text-gray-800">Description</label>
            <textarea id="description" name="description" rows="3"
                      class="mt-2 w-full rounded-xl border border-gray-300 px-4 py-3 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200"
                      data-testid="pipeline-type-description">{{ old('description', $type->description ?? '') }}</textarea>
        </div>
        <div class="lg:col-span-3">
            <label class="inline-flex items-center gap-3 rounded-xl border border-gray-200 bg-gray-50 px-4 py-3">
                <input name="is_active" type="checkbox" value="1" class="h-4 w-4 rounded border-gray-300 text-indigo-600"
                       @checked(old('is_active', $type->is_active ?? true)) data-testid="pipeline-type-active">
                <span class="text-sm font-semibold text-gray-800">Active</span>
            </label>
        </div>
    </div>

    {{-- Stages repeater --}}
    <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm" data-testid="pipeline-type-stages">
        <div class="mb-4 flex items-center justify-between">
            <h2 class="text-lg font-bold text-gray-900">Stages</h2>
            <button type="button" id="add-stage" class="rounded-lg bg-indigo-50 px-3 py-1.5 text-sm font-semibold text-indigo-700 hover:bg-indigo-100">+ Add stage</button>
        </div>
        @error('stages') <p class="mb-3 text-sm text-red-600">{{ $message }}</p> @enderror
        <div id="stages-list" class="space-y-4">
            @php
                $stages = old('stages', isset($type) ? $type->stages->map(fn($s) => [
                    'public_id' => $s->public_id,
                    'name' => $s->name,
                    'slug' => $s->slug,
                    'category' => $s->category->value,
                    'probability' => $s->probability,
                    'is_default' => $s->is_default,
                ])->values()->all() : [
                    ['name' => 'New Lead', 'category' => 'open', 'probability' => 10, 'is_default' => true],
                    ['name' => 'Won', 'category' => 'won', 'probability' => 100, 'is_default' => false],
                    ['name' => 'Lost', 'category' => 'lost', 'probability' => 0, 'is_default' => false],
                ]);
            @endphp
            <p class="mb-3 text-xs text-gray-500">Drag the handle to reorder stages. Sort order is saved on submit.</p>
            @foreach($stages as $i => $stage)
                <div class="stage-row grid gap-3 rounded-xl border border-gray-200 bg-gray-50 p-4 md:grid-cols-12" draggable="true" data-testid="stage-row">
                    @if(!empty($stage['public_id']))
                        <input type="hidden" name="stages[{{ $i }}][public_id]" value="{{ $stage['public_id'] }}">
                    @endif
                    <div class="md:col-span-1 flex items-center justify-center">
                        <span class="stage-handle cursor-grab select-none text-lg text-gray-400" title="Drag to reorder">⋮⋮</span>
                    </div>
                    <div class="md:col-span-3">
                        <input name="stages[{{ $i }}][name]" type="text" required placeholder="Stage name"
                               value="{{ $stage['name'] ?? '' }}"
                               class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    </div>
                    <div class="md:col-span-2">
                        <select name="stages[{{ $i }}][category]" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                            @foreach($stageCategories as $cat)
                                <option value="{{ $cat->value }}" @selected(($stage['category'] ?? 'open') === $cat->value)>{{ $cat->label() }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="md:col-span-2">
                        <input name="stages[{{ $i }}][probability]" type="number" min="0" max="100" placeholder="%"
                               value="{{ $stage['probability'] ?? 0 }}"
                               class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    </div>
                    <div class="md:col-span-2 flex items-center">
                        <label class="inline-flex items-center gap-2 text-sm">
                            <input type="radio" name="default_stage" value="{{ $i }}"
                                   @checked(filter_var($stage['is_default'] ?? false, FILTER_VALIDATE_BOOLEAN))
                                   class="default-stage-radio text-indigo-600">
                            Default
                        </label>
                        <input type="hidden" name="stages[{{ $i }}][is_default]" value="{{ filter_var($stage['is_default'] ?? false, FILTER_VALIDATE_BOOLEAN) ? '1' : '0' }}" class="is-default-hidden">
                    </div>
                    <div class="md:col-span-2 flex items-center justify-end">
                        <button type="button" class="remove-stage text-sm font-semibold text-red-600 hover:text-red-800">Remove</button>
                    </div>
                </div>
            @endforeach
        </div>
    </section>

    {{-- Pipeline fields --}}
    <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm" data-testid="pipeline-type-pipeline-fields">
        <div class="mb-4 flex items-center justify-between">
            <h2 class="text-lg font-bold text-gray-900">Pipeline-level fields</h2>
            <button type="button" id="add-pipeline-field" class="rounded-lg bg-indigo-50 px-3 py-1.5 text-sm font-semibold text-indigo-700">+ Add field</button>
        </div>
        <div id="pipeline-fields-list" class="space-y-3">
            @php
                $pipelineFields = old('pipeline_fields', isset($type) ? $type->fields->filter(fn($f) => $f->scope->value === 'pipeline')->values()->map(fn($f) => [
                    'public_id' => $f->public_id, 'label' => $f->label, 'key' => $f->key, 'type' => $f->type->value,
                ])->all() : []);
            @endphp
            @foreach($pipelineFields as $i => $field)
                @include('admin.sales.pipeline-types._field-row', ['prefix' => 'pipeline_fields', 'index' => $i, 'field' => $field, 'fieldTypes' => $fieldTypes])
            @endforeach
        </div>
    </section>

    {{-- Deal fields --}}
    <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm" data-testid="pipeline-type-deal-fields">
        <div class="mb-4 flex items-center justify-between">
            <h2 class="text-lg font-bold text-gray-900">Deal-level fields</h2>
            <button type="button" id="add-deal-field" class="rounded-lg bg-indigo-50 px-3 py-1.5 text-sm font-semibold text-indigo-700">+ Add field</button>
        </div>
        <div id="deal-fields-list" class="space-y-3">
            @php
                $dealFields = old('deal_fields', isset($type) ? $type->fields->filter(fn($f) => $f->scope->value === 'deal')->values()->map(fn($f) => [
                    'public_id' => $f->public_id, 'label' => $f->label, 'key' => $f->key, 'type' => $f->type->value,
                ])->all() : []);
            @endphp
            @foreach($dealFields as $i => $field)
                @include('admin.sales.pipeline-types._field-row', ['prefix' => 'deal_fields', 'index' => $i, 'field' => $field, 'fieldTypes' => $fieldTypes])
            @endforeach
        </div>
    </section>
</div>

<template id="stage-template">
    <div class="stage-row grid gap-3 rounded-xl border border-gray-200 bg-gray-50 p-4 md:grid-cols-12" draggable="true" data-testid="stage-row">
        <div class="md:col-span-1 flex items-center justify-center"><span class="stage-handle cursor-grab select-none text-lg text-gray-400" title="Drag to reorder">⋮⋮</span></div>
        <div class="md:col-span-3"><input name="stages[__INDEX__][name]" type="text" required placeholder="Stage name" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"></div>
        <div class="md:col-span-2">
            <select name="stages[__INDEX__][category]" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                @foreach($stageCategories as $cat)<option value="{{ $cat->value }}">{{ $cat->label() }}</option>@endforeach
            </select>
        </div>
        <div class="md:col-span-2"><input name="stages[__INDEX__][probability]" type="number" min="0" max="100" value="0" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"></div>
        <div class="md:col-span-2 flex items-center">
            <label class="inline-flex items-center gap-2 text-sm">
                <input type="radio" name="default_stage" value="__INDEX__" class="default-stage-radio text-indigo-600"> Default
            </label>
            <input type="hidden" name="stages[__INDEX__][is_default]" value="0" class="is-default-hidden">
        </div>
        <div class="md:col-span-2 flex items-center justify-end"><button type="button" class="remove-stage text-sm font-semibold text-red-600">Remove</button></div>
    </div>
</template>

<template id="field-template">
    <div class="field-row grid gap-3 rounded-xl border border-gray-200 p-3 md:grid-cols-12">
        <div class="md:col-span-5"><input name="__PREFIX__[__INDEX__][label]" type="text" required placeholder="Label" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"></div>
        <div class="md:col-span-4">
            <select name="__PREFIX__[__INDEX__][type]" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                @foreach($fieldTypes as $ft)<option value="{{ $ft->value }}">{{ $ft->label() }}</option>@endforeach
            </select>
        </div>
        <div class="md:col-span-3 flex justify-end"><button type="button" class="remove-field text-sm font-semibold text-red-600">Remove</button></div>
    </div>
</template>

<script>
document.addEventListener('DOMContentLoaded', () => {
    let stageIndex = {{ count($stages) }};
    let pipelineFieldIndex = {{ count($pipelineFields) }};
    let dealFieldIndex = {{ count($dealFields) }};

    document.getElementById('add-stage')?.addEventListener('click', () => {
        const tpl = document.getElementById('stage-template').innerHTML.replace(/__INDEX__/g, stageIndex++);
        document.getElementById('stages-list').insertAdjacentHTML('beforeend', tpl);
    });

    document.getElementById('add-pipeline-field')?.addEventListener('click', () => addField('pipeline-fields-list', 'pipeline_fields', () => pipelineFieldIndex++));
    document.getElementById('add-deal-field')?.addEventListener('click', () => addField('deal-fields-list', 'deal_fields', () => dealFieldIndex++));

    function addField(listId, prefix, nextIndex) {
        const i = nextIndex();
        const tpl = document.getElementById('field-template').innerHTML.replace(/__PREFIX__/g, prefix).replace(/__INDEX__/g, i);
        document.getElementById(listId).insertAdjacentHTML('beforeend', tpl);
    }

    document.addEventListener('click', (e) => {
        if (e.target.classList.contains('remove-stage')) e.target.closest('.stage-row')?.remove();
        if (e.target.classList.contains('remove-field')) e.target.closest('.field-row')?.remove();
    });

    document.addEventListener('change', (e) => {
        if (e.target.classList.contains('default-stage-radio')) {
            document.querySelectorAll('.is-default-hidden').forEach(el => el.value = '0');
            const row = e.target.closest('.stage-row');
            row?.querySelector('.is-default-hidden')?.setAttribute('value', '1');
        }
    });

    const stagesList = document.getElementById('stages-list');
    let dragRow = null;
    stagesList?.addEventListener('dragstart', (e) => {
        dragRow = e.target.closest('.stage-row');
        if (dragRow) dragRow.classList.add('opacity-50');
    });
    stagesList?.addEventListener('dragend', () => {
        dragRow?.classList.remove('opacity-50');
        dragRow = null;
        reindexStages();
    });
    stagesList?.addEventListener('dragover', (e) => {
        e.preventDefault();
        const over = e.target.closest('.stage-row');
        if (!dragRow || !over || over === dragRow) return;
        const rect = over.getBoundingClientRect();
        const before = (e.clientY - rect.top) < rect.height / 2;
        stagesList.insertBefore(dragRow, before ? over : over.nextSibling);
    });

    function reindexStages() {
        stagesList?.querySelectorAll('.stage-row').forEach((row, index) => {
            row.querySelectorAll('[name^="stages["]').forEach((input) => {
                input.name = input.name.replace(/stages\[\d+]/, `stages[${index}]`);
            });
            const radio = row.querySelector('.default-stage-radio');
            if (radio) radio.value = String(index);
        });
    }
});
</script>
