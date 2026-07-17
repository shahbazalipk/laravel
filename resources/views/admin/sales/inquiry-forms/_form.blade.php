@php
    $editing = isset($form);
@endphp
<div class="space-y-6">
    <div class="grid gap-6 lg:grid-cols-2">
        <div>
            <label for="name" class="block text-sm font-semibold text-gray-800">Form name</label>
            <input id="name" name="name" type="text" required value="{{ old('name', $form->name ?? '') }}" class="mt-2 w-full rounded-xl border border-gray-300 px-4 py-3" data-testid="inquiry-form-name">
        </div>
        <div>
            <label for="slug" class="block text-sm font-semibold text-gray-800">Slug</label>
            <input id="slug" name="slug" type="text" value="{{ old('slug', $form->slug ?? '') }}" class="mt-2 w-full rounded-xl border border-gray-300 px-4 py-3 font-mono text-sm">
        </div>
        <div class="lg:col-span-2">
            <label for="heading" class="block text-sm font-semibold text-gray-800">Heading</label>
            <input id="heading" name="heading" type="text" value="{{ old('heading', $form->heading ?? '') }}" class="mt-2 w-full rounded-xl border border-gray-300 px-4 py-3">
        </div>
        <div class="lg:col-span-2">
            <label for="intro_text" class="block text-sm font-semibold text-gray-800">Intro text</label>
            <textarea id="intro_text" name="intro_text" rows="3" class="mt-2 w-full rounded-xl border border-gray-300 px-4 py-3">{{ old('intro_text', $form->intro_text ?? '') }}</textarea>
        </div>
        <div>
            <label for="sales_pipeline_id" class="block text-sm font-semibold text-gray-800">Target pipeline</label>
            <select id="sales_pipeline_id" name="sales_pipeline_id" class="mt-2 w-full rounded-xl border border-gray-300 px-4 py-3">
                <option value="">None</option>
                @foreach($pipelines as $pipeline)
                    <option value="{{ $pipeline->id }}"
                            data-stages="{{ $pipeline->stages->map(fn($s) => ['id' => $s->id, 'name' => $s->name])->values()->toJson() }}"
                            @selected(old('sales_pipeline_id', $form->sales_pipeline_id ?? '') == $pipeline->id)>{{ $pipeline->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="default_stage_id" class="block text-sm font-semibold text-gray-800">Default deal stage</label>
            <select id="default_stage_id" name="default_stage_id" class="mt-2 w-full rounded-xl border border-gray-300 px-4 py-3">
                <option value="">Pipeline default</option>
                @foreach($pipelines as $pipeline)
                    @foreach($pipeline->stages as $stage)
                        <option value="{{ $stage->id }}" data-pipeline="{{ $pipeline->id }}" @selected(old('default_stage_id', $form->default_stage_id ?? '') == $stage->id)>
                            {{ $pipeline->name }} · {{ $stage->name }}
                        </option>
                    @endforeach
                @endforeach
            </select>
        </div>
        <div class="lg:col-span-2">
            <label for="allowed_domains" class="block text-sm font-semibold text-gray-800">Allowed embed domains</label>
            <input id="allowed_domains" name="allowed_domains" type="text"
                   value="{{ old('allowed_domains', isset($form) ? implode(', ', $form->allowed_domains ?? []) : '') }}"
                   placeholder="example.com, partner.org"
                   class="mt-2 w-full rounded-xl border border-gray-300 px-4 py-3 text-sm"
                   data-testid="inquiry-allowed-domains">
            <p class="mt-1 text-xs text-gray-500">Comma-separated. Leave blank to allow any domain.</p>
        </div>
        <div class="flex items-end">
            <label class="inline-flex items-center gap-3 rounded-xl border border-gray-200 bg-gray-50 px-4 py-3">
                <input name="auto_create_deal" type="checkbox" value="1" class="h-4 w-4 rounded text-indigo-600" @checked(old('auto_create_deal', $form->auto_create_deal ?? false))>
                <span class="text-sm font-semibold text-gray-800">Auto-create deal on submit</span>
            </label>
        </div>
    </div>

    <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm" data-testid="inquiry-form-fields">
        <div class="mb-4 flex items-center justify-between">
            <div>
                <h2 class="text-lg font-bold text-gray-900">Fields</h2>
                <p class="text-sm text-gray-500">Map fields to deal/contact attributes for conversion.</p>
            </div>
            <button type="button" id="add-inquiry-field" class="rounded-lg bg-indigo-50 px-3 py-1.5 text-sm font-semibold text-indigo-700">+ Add field</button>
        </div>
        <div id="inquiry-fields-list" class="space-y-3">
            @php
                $fields = old('fields', isset($form) ? $form->fields->map(fn($f) => [
                    'public_id' => $f->public_id,
                    'label' => $f->label,
                    'type' => $f->type->value,
                    'is_required' => $f->is_required,
                    'map_to_deal_field' => $f->map_to_deal_field,
                    'map_to_contact_field' => $f->map_to_contact_field,
                ])->values()->all() : [
                    ['label' => 'Full name', 'type' => 'text', 'is_required' => true, 'map_to_contact_field' => 'name'],
                    ['label' => 'Email', 'type' => 'email', 'is_required' => true, 'map_to_contact_field' => 'email'],
                    ['label' => 'Company', 'type' => 'text', 'is_required' => false, 'map_to_deal_field' => 'title', 'map_to_contact_field' => 'company_name'],
                ]);
            @endphp
            @foreach($fields as $i => $field)
                <div class="field-row grid gap-3 rounded-xl border border-gray-200 p-3 md:grid-cols-12">
                    @if(!empty($field['public_id']))<input type="hidden" name="fields[{{ $i }}][public_id]" value="{{ $field['public_id'] }}">@endif
                    <div class="md:col-span-3"><input name="fields[{{ $i }}][label]" type="text" required value="{{ $field['label'] ?? '' }}" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" placeholder="Label"></div>
                    <div class="md:col-span-2">
                        <select name="fields[{{ $i }}][type]" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                            @foreach($fieldTypes as $ft)<option value="{{ $ft->value }}" @selected(($field['type'] ?? 'text') === $ft->value)>{{ $ft->label() }}</option>@endforeach
                        </select>
                    </div>
                    <div class="md:col-span-2">
                        <select name="fields[{{ $i }}][map_to_deal_field]" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                            <option value="">Deal map…</option>
                            @foreach(['title' => 'Title', 'value' => 'Value', 'description' => 'Description'] as $key => $label)
                                <option value="{{ $key }}" @selected(($field['map_to_deal_field'] ?? '') === $key)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="md:col-span-2">
                        <select name="fields[{{ $i }}][map_to_contact_field]" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">
                            <option value="">Contact map…</option>
                            @foreach(['name' => 'Name', 'email' => 'Email', 'phone' => 'Phone', 'company_name' => 'Company'] as $key => $label)
                                <option value="{{ $key }}" @selected(($field['map_to_contact_field'] ?? '') === $key)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="md:col-span-2 flex items-center"><label class="text-sm"><input type="checkbox" name="fields[{{ $i }}][is_required]" value="1" @checked($field['is_required'] ?? false)> Required</label></div>
                    <div class="md:col-span-1 flex justify-end"><button type="button" class="remove-field text-sm font-semibold text-red-600">Remove</button></div>
                </div>
            @endforeach
        </div>
    </section>
</div>

<template id="inquiry-field-template">
    <div class="field-row grid gap-3 rounded-xl border border-gray-200 p-3 md:grid-cols-12">
        <div class="md:col-span-3"><input name="fields[__INDEX__][label]" type="text" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" placeholder="Label"></div>
        <div class="md:col-span-2"><select name="fields[__INDEX__][type]" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm">@foreach($fieldTypes as $ft)<option value="{{ $ft->value }}">{{ $ft->label() }}</option>@endforeach</select></div>
        <div class="md:col-span-2"><select name="fields[__INDEX__][map_to_deal_field]" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"><option value="">Deal map…</option><option value="title">Title</option><option value="value">Value</option><option value="description">Description</option></select></div>
        <div class="md:col-span-2"><select name="fields[__INDEX__][map_to_contact_field]" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm"><option value="">Contact map…</option><option value="name">Name</option><option value="email">Email</option><option value="phone">Phone</option><option value="company_name">Company</option></select></div>
        <div class="md:col-span-2 flex items-center"><label class="text-sm"><input type="checkbox" name="fields[__INDEX__][is_required]" value="1"> Required</label></div>
        <div class="md:col-span-1 flex justify-end"><button type="button" class="remove-field text-sm font-semibold text-red-600">Remove</button></div>
    </div>
</template>
<script>
document.addEventListener('DOMContentLoaded', () => {
    let idx = {{ count($fields) }};
    document.getElementById('add-inquiry-field')?.addEventListener('click', () => {
        document.getElementById('inquiry-fields-list').insertAdjacentHTML('beforeend', document.getElementById('inquiry-field-template').innerHTML.replace(/__INDEX__/g, idx++));
    });
    document.addEventListener('click', e => { if (e.target.classList.contains('remove-field')) e.target.closest('.field-row')?.remove(); });
});
</script>
