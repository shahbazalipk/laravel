@foreach($customForms as $customForm)
    @php
        $response = $customFormResponses->get($customForm->id);
        $savedAnswers = $response?->answers?->keyBy('question_key') ?? collect();
        $questionsById = $customForm->questions->keyBy('id');
        $conditionMetadata = $customForm->conditions->map(function ($condition) use ($questionsById) {
            return [
                'target' => $questionsById->get($condition->target_question_id)?->key,
                'source' => $questionsById->get($condition->source_question_id)?->key,
                'operator' => $condition->operator->value,
                'compare' => $condition->compare_value,
                'action' => $condition->action->value,
            ];
        })->filter(fn ($condition) => $condition['target'] && $condition['source'])->values();
    @endphp

    <section class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-6"
             data-custom-form="{{ $customForm->public_id }}"
             data-testid="custom-form-fields-{{ $customForm->public_id }}">
        <div class="mb-5">
            <h3 class="text-base font-semibold text-slate-900">{{ $customForm->name }}</h3>
            @if($customForm->description)
                <p class="mt-1 text-sm leading-6 text-slate-500">{{ $customForm->description }}</p>
            @endif
        </div>

        <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
            @foreach($customForm->questions as $question)
                @php
                    $fieldName = "custom_forms[{$customForm->public_id}][{$question->key}]";
                    $errorKey = "custom_forms.{$customForm->public_id}.{$question->key}";
                    $savedAnswer = $savedAnswers->get($question->key);
                    $storedValue = $question->type->value === 'checkbox'
                        ? ($savedAnswer?->value['values'] ?? [])
                        : ($savedAnswer?->value['value'] ?? null);
                    $value = old($errorKey, $storedValue);
                    $existingFile = $savedAnswer?->files?->first();
                    $fieldId = 'custom-form-'.$customForm->public_id.'-'.$question->public_id;
                    $isWide = in_array($question->type->value, ['textarea', 'checkbox', 'radio', 'upload'], true);
                @endphp

                <div class="{{ $isWide ? 'md:col-span-2' : '' }}"
                     data-custom-question="{{ $question->key }}"
                     data-testid="custom-question-{{ $question->key }}">
                    <label for="{{ $fieldId }}" class="mb-2 block text-sm font-medium text-slate-700">
                        {{ $question->label }}
                        @if($question->is_required)
                            <span class="text-rose-600" aria-label="required">*</span>
                        @endif
                    </label>

                    @if($question->type->value === 'text')
                        <input id="{{ $fieldId }}" name="{{ $fieldName }}" type="text"
                               value="{{ $value }}"
                               placeholder="{{ $question->placeholder }}"
                               @required($question->is_required)
                               class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                    @elseif($question->type->value === 'textarea')
                        <textarea id="{{ $fieldId }}" name="{{ $fieldName }}" rows="4"
                                  placeholder="{{ $question->placeholder }}"
                                  @required($question->is_required)
                                  class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm focus:border-indigo-500 focus:ring-indigo-500">{{ $value }}</textarea>
                    @elseif($question->type->value === 'select')
                        <select id="{{ $fieldId }}" name="{{ $fieldName }}"
                                @required($question->is_required)
                                class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">{{ $question->placeholder ?: 'Select an option' }}</option>
                            @foreach($question->options as $option)
                                <option value="{{ $option->value }}" @selected((string) $value === (string) $option->value)>
                                    {{ $option->label }}
                                </option>
                            @endforeach
                        </select>
                    @elseif($question->type->value === 'radio')
                        <div id="{{ $fieldId }}" class="grid grid-cols-1 gap-2 sm:grid-cols-2">
                            @foreach($question->options as $option)
                                <label class="flex cursor-pointer items-start gap-3 rounded-xl border border-slate-200 p-3 hover:bg-slate-50">
                                    <input type="radio" name="{{ $fieldName }}" value="{{ $option->value }}"
                                           @checked((string) $value === (string) $option->value)
                                           @required($question->is_required)
                                           class="mt-0.5 text-indigo-600 focus:ring-indigo-500">
                                    <span class="text-sm text-slate-700">{{ $option->label }}</span>
                                </label>
                            @endforeach
                        </div>
                    @elseif($question->type->value === 'checkbox')
                        @php($checkedValues = is_array($value) ? array_map('strval', $value) : [])
                        <div id="{{ $fieldId }}" class="grid grid-cols-1 gap-2 sm:grid-cols-2">
                            @foreach($question->options as $option)
                                <label class="flex cursor-pointer items-start gap-3 rounded-xl border border-slate-200 p-3 hover:bg-slate-50">
                                    <input type="checkbox" name="{{ $fieldName }}[]" value="{{ $option->value }}"
                                           @checked(in_array((string) $option->value, $checkedValues, true))
                                           class="mt-0.5 rounded text-indigo-600 focus:ring-indigo-500">
                                    <span class="text-sm text-slate-700">{{ $option->label }}</span>
                                </label>
                            @endforeach
                        </div>
                    @elseif($question->type->value === 'upload')
                        <input id="{{ $fieldId }}" name="{{ $fieldName }}" type="file"
                               @required($question->is_required && !$existingFile)
                               class="block w-full rounded-xl border border-slate-300 bg-white text-sm text-slate-600 file:mr-4 file:border-0 file:bg-indigo-50 file:px-4 file:py-3 file:font-medium file:text-indigo-700">
                        @if($existingFile)
                            <p class="mt-2 text-xs text-slate-500">
                                Existing upload: <span class="font-medium text-slate-700">{{ $existingFile->original_name }}</span>.
                                Choose a new file only to replace it.
                            </p>
                        @endif
                    @endif

                    @if($question->help_text)
                        <p class="mt-2 text-xs leading-5 text-slate-500">{{ $question->help_text }}</p>
                    @endif
                    @error($errorKey)
                        <p class="mt-2 text-sm text-rose-600">{{ $message }}</p>
                    @enderror
                </div>
            @endforeach
        </div>

        <script type="application/json" data-custom-form-conditions>@json($conditionMetadata)</script>
    </section>
@endforeach

@once
    @push('scripts')
        <script>
            document.querySelectorAll('[data-custom-form]').forEach((formSection) => {
                const metadata = JSON.parse(formSection.querySelector('[data-custom-form-conditions]')?.textContent || '[]');
                const byTarget = Object.groupBy
                    ? Object.groupBy(metadata, condition => condition.target)
                    : metadata.reduce((groups, condition) => {
                        (groups[condition.target] ||= []).push(condition);
                        return groups;
                    }, {});

                const valuesFor = (key) => {
                    const field = formSection.querySelector(`[data-custom-question="${CSS.escape(key)}"]`);
                    if (!field) return [];

                    return [...field.querySelectorAll('input, select, textarea')]
                        .filter(control => !['radio', 'checkbox'].includes(control.type) || control.checked)
                        .map(control => control.value)
                        .filter(value => value !== '');
                };

                const normalizeCompare = (compare) => {
                    if (compare && typeof compare === 'object' && !Array.isArray(compare) && Object.keys(compare).length === 1 && 'value' in compare) {
                        return compare.value;
                    }
                    return compare;
                };

                const matches = (condition) => {
                    const values = valuesFor(condition.source);
                    const compare = normalizeCompare(condition.compare);
                    const expected = Array.isArray(compare) ? compare.map(String) : [String(compare ?? '')];
                    const equals = Array.isArray(compare)
                        ? (values.length > 1
                            ? [...values].sort().join('\u0000') === [...expected].sort().join('\u0000')
                            : expected.includes(String(values[0] ?? '')))
                        : values.includes(String(compare ?? ''));

                    switch (condition.operator) {
                        case 'is_empty': return values.length === 0;
                        case 'is_not_empty': return values.length > 0;
                        case 'equals': return equals;
                        case 'not_equals': return !equals;
                        case 'contains':
                            return values.some(value => String(value).includes(
                                Array.isArray(compare) ? JSON.stringify(compare) : String(compare ?? '')
                            ));
                        default: return false;
                    }
                };

                const evaluate = () => {
                    Object.entries(byTarget).forEach(([target, conditions]) => {
                        const field = formSection.querySelector(`[data-custom-question="${CSS.escape(target)}"]`);
                        if (!field || conditions.length === 0) return;

                        const allMatch = conditions.every(matches);
                        const visible = conditions[0].action === 'show' ? allMatch : !allMatch;
                        field.classList.toggle('hidden', !visible);
                        field.querySelectorAll('input, select, textarea').forEach(control => {
                            control.disabled = !visible;
                        });
                    });
                };

                formSection.addEventListener('input', evaluate);
                formSection.addEventListener('change', evaluate);
                evaluate();
            });
        </script>
    @endpush
@endonce
