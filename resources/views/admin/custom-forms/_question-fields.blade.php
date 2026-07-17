@php
    $question = $question ?? null;
    $fieldId = $question?->public_id ?? 'new';
    $useOld = old('_question_public_id') === $fieldId;
    $fieldValue = fn (string $key, mixed $default = null) => $useOld ? old($key, $default) : $default;
    $condition = $question?->targetConditions?->first();
    $selectedType = $fieldValue('type', $question?->type?->value ?? 'text');
    $options = collect($fieldValue('options', $question?->options ?? []));
@endphp

<div class="space-y-6" data-question-fields data-testid="question-fields-{{ $fieldId }}">
    <input type="hidden" name="_question_public_id" value="{{ $fieldId }}">
    <div class="grid gap-5 md:grid-cols-2">
        <div>
            <label for="label-{{ $fieldId }}" class="block text-sm font-semibold text-gray-800">Label</label>
            <input id="label-{{ $fieldId }}" name="label" required maxlength="255"
                   value="{{ $fieldValue('label', $question?->label) }}" placeholder="e.g. Dietary requirements"
                   class="mt-2 w-full rounded-lg border border-gray-300 px-3 py-2.5 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200">
        </div>
        <div>
            <label for="key-{{ $fieldId }}" class="block text-sm font-semibold text-gray-800">Stable key</label>
            <input id="key-{{ $fieldId }}" name="key" required maxlength="100"
                   value="{{ $fieldValue('key', $question?->key) }}" placeholder="dietary_requirements"
                   class="mt-2 w-full rounded-lg border border-gray-300 px-3 py-2.5 font-mono text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200">
            <p class="mt-1 text-xs text-gray-500">Lowercase letters, numbers, and underscores. Avoid changing after launch.</p>
        </div>
        <div>
            <label for="type-{{ $fieldId }}" class="block text-sm font-semibold text-gray-800">Question type</label>
            <select id="type-{{ $fieldId }}" name="type" data-question-type
                    class="mt-2 w-full rounded-lg border border-gray-300 px-3 py-2.5 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200">
                @foreach($questionTypes as $type)
                    <option value="{{ $type->value }}" @selected($selectedType === $type->value)>{{ $type->label() }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex items-end">
            <label class="flex w-full items-center gap-3 rounded-lg border border-gray-200 bg-gray-50 px-4 py-2.5">
                <input name="is_required" type="checkbox" value="1" @checked($fieldValue('is_required', $question?->is_required ?? false))
                       class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                <span class="text-sm font-semibold text-gray-800">Required response</span>
            </label>
        </div>
        <div>
            <label for="placeholder-{{ $fieldId }}" class="block text-sm font-semibold text-gray-800">Placeholder</label>
            <input id="placeholder-{{ $fieldId }}" name="placeholder" maxlength="255"
                   value="{{ $fieldValue('placeholder', $question?->placeholder) }}"
                   class="mt-2 w-full rounded-lg border border-gray-300 px-3 py-2.5 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200">
        </div>
        <div>
            <label for="help-{{ $fieldId }}" class="block text-sm font-semibold text-gray-800">Help text</label>
            <input id="help-{{ $fieldId }}" name="help_text" maxlength="2000"
                   value="{{ $fieldValue('help_text', $question?->help_text) }}"
                   class="mt-2 w-full rounded-lg border border-gray-300 px-3 py-2.5 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200">
        </div>
    </div>

    <div class="rounded-xl border border-gray-200 bg-gray-50 p-4">
        <h4 class="text-sm font-bold text-gray-900">Validation</h4>
        <div class="mt-3 grid gap-4 sm:grid-cols-2" data-standard-validation>
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wide text-gray-600">Minimum</label>
                <input name="validation[min]" type="number" min="0" value="{{ $fieldValue('validation.min', data_get($question?->validation, 'min')) }}"
                       class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2">
            </div>
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wide text-gray-600">Maximum</label>
                <input name="validation[max]" type="number" min="0" value="{{ $fieldValue('validation.max', data_get($question?->validation, 'max')) }}"
                       class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2">
            </div>
        </div>
        <div class="mt-3 hidden grid gap-4 sm:grid-cols-2" data-upload-validation>
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wide text-gray-600">Allowed extensions</label>
                <input name="validation[mimes]" value="{{ $fieldValue('validation.mimes', data_get($question?->validation, 'mimes')) }}" placeholder="pdf, jpg, png"
                       class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2">
            </div>
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wide text-gray-600">Maximum KB</label>
                <input name="validation[max_kb]" type="number" min="1" max="102400" value="{{ $fieldValue('validation.max_kb', data_get($question?->validation, 'max_kb')) }}"
                       class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2">
            </div>
        </div>
    </div>

    <div class="hidden rounded-xl border border-indigo-100 bg-indigo-50/50 p-4" data-options-section>
        <div class="flex items-center justify-between gap-3">
            <div>
                <h4 class="text-sm font-bold text-gray-900">Answer options</h4>
                <p class="text-xs text-gray-600">Labels are shown to admins; values are stored.</p>
            </div>
            <button type="button" data-add-option class="rounded-lg border border-indigo-200 bg-white px-3 py-2 text-xs font-semibold text-indigo-700 hover:bg-indigo-50">+ Add option</button>
        </div>
        <div class="mt-3 space-y-2" data-options-list>
            @foreach($options as $index => $option)
                @include('admin.custom-forms._option-row', ['index' => $index, 'option' => $option])
            @endforeach
        </div>
    </div>

    <div class="rounded-xl border border-violet-100 bg-violet-50/50 p-4">
        <div>
            <h4 class="text-sm font-bold text-gray-900">Visibility condition <span class="font-normal text-gray-500">(optional)</span></h4>
            <p class="mt-1 text-xs leading-5 text-gray-600">Choose one earlier question. The show/hide action runs when its answer matches this rule.</p>
        </div>
        @if($sourceQuestions->isEmpty())
            <p class="mt-3 rounded-lg bg-white px-3 py-2 text-sm text-gray-500">Add an earlier question before creating a condition.</p>
        @else
            <div class="mt-4 grid gap-3 md:grid-cols-2 xl:grid-cols-4">
                <select name="condition[source_question]" class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    <option value="">Always visible</option>
                    @foreach($sourceQuestions as $source)
                        <option value="{{ $source->public_id }}" @selected($fieldValue('condition.source_question', $condition?->sourceQuestion?->public_id) === $source->public_id)>{{ $source->label }}</option>
                    @endforeach
                </select>
                <select name="condition[operator]" data-condition-operator class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    @foreach($operators as $operator)
                        <option value="{{ $operator->value }}" @selected($fieldValue('condition.operator', $condition?->operator?->value ?? 'equals') === $operator->value)>{{ $operator->label() }}</option>
                    @endforeach
                </select>
                <input name="condition[compare_value]" data-compare-value value="{{ $fieldValue('condition.compare_value', $condition?->compare_value) }}"
                       placeholder="Comparison value" class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
                <select name="condition[action]" class="rounded-lg border border-gray-300 px-3 py-2 text-sm">
                    @foreach($actions as $action)
                        <option value="{{ $action->value }}" @selected($fieldValue('condition.action', $condition?->action?->value ?? 'show') === $action->value)>{{ $action->label() }} question</option>
                    @endforeach
                </select>
            </div>
        @endif
    </div>
</div>
