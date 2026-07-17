@extends('admin.layout')

@section('title', 'Build '.$form->name)

@section('content')
<div class="mb-7 flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
    <div>
        <a href="{{ route('admin.custom-forms.index') }}" class="text-sm font-semibold text-indigo-600 hover:text-indigo-800">← Custom Questions</a>
        <h1 class="mt-3 text-3xl font-bold text-gray-900">{{ $form->name }}</h1>
        <p class="mt-2 text-gray-600">Form builder · version {{ data_get($form->settings, 'version', 0) }}</p>
    </div>
    <div class="flex flex-wrap items-center gap-2 text-sm">
        <span class="rounded-full bg-indigo-50 px-3 py-1.5 font-semibold text-indigo-700">{{ $form->audience->label() }}</span>
        <span class="rounded-full px-3 py-1.5 font-semibold {{ $form->is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-gray-200 text-gray-600' }}">{{ $form->is_active ? 'Active' : 'Inactive' }}</span>
        <span class="rounded-full bg-gray-200 px-3 py-1.5 font-semibold text-gray-700">{{ $form->questions->count() }} {{ Str::plural('question', $form->questions->count()) }}</span>
    </div>
</div>

@if($errors->any())
    <div class="mb-6 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-800" data-testid="builder-errors">
        <p class="font-bold">Please correct these fields:</p>
        <ul class="mt-2 list-disc space-y-1 pl-5">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
    </div>
@endif

<div class="grid gap-7 xl:grid-cols-[minmax(0,1fr)_22rem]">
    <section class="min-w-0">
        <div class="mb-4 flex items-center justify-between">
            <div>
                <h2 class="text-xl font-bold text-gray-900">Questions</h2>
                <p class="mt-1 text-sm text-gray-600">Use the arrow controls to set the order used by conditions.</p>
            </div>
        </div>

        @if($form->questions->isEmpty())
            <div class="rounded-2xl border border-dashed border-gray-300 bg-white px-6 py-12 text-center" data-testid="questions-empty">
                <h3 class="text-lg font-bold text-gray-900">Start with your first question</h3>
                <p class="mt-2 text-sm text-gray-600">The add-question panel is directly below.</p>
            </div>
        @else
            <div class="space-y-4" data-testid="question-list">
                @foreach($form->questions as $index => $question)
                    @php
                        $condition = $question->targetConditions->first();
                        $sourceQuestions = $form->questions->take($index);
                    @endphp
                    <article class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm" data-testid="question-card-{{ $question->public_id }}">
                        <div class="flex flex-col gap-4 p-5 sm:flex-row sm:items-start sm:justify-between">
                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-gray-100 text-xs font-bold text-gray-600">{{ $index + 1 }}</span>
                                    <h3 class="font-bold text-gray-900">{{ $question->label }}</h3>
                                    <span class="rounded-full bg-sky-50 px-2.5 py-1 text-xs font-semibold text-sky-700">{{ $question->type->label() }}</span>
                                    @if($question->is_required)<span class="rounded-full bg-red-50 px-2.5 py-1 text-xs font-semibold text-red-700">Required</span>@endif
                                </div>
                                <p class="mt-2 font-mono text-xs text-gray-500">{{ $question->key }}</p>
                                @if($question->type->hasOptions())
                                    <p class="mt-2 text-xs text-gray-600">{{ $question->options->pluck('label')->join(' · ') ?: 'No options configured' }}</p>
                                @endif
                                @if($condition)
                                    <p class="mt-2 rounded-lg bg-violet-50 px-3 py-2 text-xs text-violet-800">
                                        {{ $condition->action->label() }} when “{{ $condition->sourceQuestion->label }}” {{ strtolower($condition->operator->label()) }}
                                        @if($condition->operator->requiresCompareValue()) “{{ $condition->compare_value }}” @endif
                                    </p>
                                @endif
                            </div>
                            <div class="flex shrink-0 items-center gap-1">
                                <form action="{{ route('admin.custom-forms.questions.reorder', [$form, $question]) }}" method="POST">
                                    @csrf @method('PATCH') <input type="hidden" name="direction" value="up">
                                    <button type="submit" @disabled($loop->first) class="rounded-lg border border-gray-200 px-3 py-2 text-gray-600 hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-30" aria-label="Move {{ $question->label }} up" data-testid="move-question-up-{{ $question->public_id }}">↑</button>
                                </form>
                                <form action="{{ route('admin.custom-forms.questions.reorder', [$form, $question]) }}" method="POST">
                                    @csrf @method('PATCH') <input type="hidden" name="direction" value="down">
                                    <button type="submit" @disabled($loop->last) class="rounded-lg border border-gray-200 px-3 py-2 text-gray-600 hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-30" aria-label="Move {{ $question->label }} down" data-testid="move-question-down-{{ $question->public_id }}">↓</button>
                                </form>
                                <button type="button" data-toggle-editor="editor-{{ $question->public_id }}" class="rounded-lg border border-indigo-200 px-3 py-2 text-sm font-semibold text-indigo-700 hover:bg-indigo-50">Edit</button>
                                <form action="{{ route('admin.custom-forms.questions.destroy', [$form, $question]) }}" method="POST" onsubmit="return confirm('Delete this question? Conditions that use it will also be removed.');">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="rounded-lg px-3 py-2 text-sm font-semibold text-red-600 hover:bg-red-50" data-testid="delete-question-{{ $question->public_id }}">Delete</button>
                                </form>
                            </div>
                        </div>
                        <div id="editor-{{ $question->public_id }}" class="{{ old('_question_public_id') === $question->public_id ? '' : 'hidden' }} border-t border-gray-100 bg-gray-50/50 p-5 sm:p-6">
                            <form action="{{ route('admin.custom-forms.questions.update', [$form, $question]) }}" method="POST" data-testid="edit-question-form-{{ $question->public_id }}">
                                @csrf @method('PUT')
                                @include('admin.custom-forms._question-fields', compact('question', 'sourceQuestions'))
                                <div class="mt-6 flex justify-end">
                                    <button type="submit" class="rounded-xl bg-indigo-600 px-5 py-2.5 font-semibold text-white hover:bg-indigo-700">Save question</button>
                                </div>
                            </form>
                        </div>
                    </article>
                @endforeach
            </div>
        @endif

        <div class="mt-6 rounded-2xl border border-indigo-200 bg-white p-5 shadow-sm sm:p-6" data-testid="add-question-panel">
            <h2 class="text-lg font-bold text-gray-900">Add a question</h2>
            <p class="mt-1 text-sm text-gray-600">New questions are added to the end of the form.</p>
            <form action="{{ route('admin.custom-forms.questions.store', $form) }}" method="POST" class="mt-5" data-testid="add-question-form">
                @csrf
                @php($sourceQuestions = $form->questions)
                @include('admin.custom-forms._question-fields', ['question' => null, 'sourceQuestions' => $sourceQuestions])
                <div class="mt-6 flex justify-end">
                    <button type="submit" class="rounded-xl bg-indigo-600 px-5 py-3 font-semibold text-white hover:bg-indigo-700" data-testid="save-question">Add question</button>
                </div>
            </form>
        </div>
    </section>

    <aside class="space-y-5 xl:sticky xl:top-5 xl:self-start">
        <form action="{{ route('admin.custom-forms.update', $form) }}" method="POST" class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm" data-testid="edit-custom-form-form">
            @csrf @method('PUT')
            <h2 class="text-lg font-bold text-gray-900">Form details</h2>
            <div class="mt-5">@include('admin.custom-forms._form')</div>
            <button type="submit" class="mt-6 w-full rounded-xl bg-gray-900 px-4 py-3 font-semibold text-white hover:bg-gray-800">Save form details</button>
        </form>
        <div class="rounded-2xl border border-violet-200 bg-violet-50 p-5">
            <h2 class="font-bold text-violet-950">How conditions work</h2>
            <p class="mt-2 text-sm leading-6 text-violet-900">A question can have one visibility rule in v1. Its source must be earlier in the form, so moving questions may affect which sources are available when editing.</p>
        </div>
    </aside>
</div>

<script>
document.querySelectorAll('[data-toggle-editor]').forEach((button) => {
    button.addEventListener('click', () => document.getElementById(button.dataset.toggleEditor)?.classList.toggle('hidden'));
});

document.querySelectorAll('[data-question-fields]').forEach((container) => {
    const type = container.querySelector('[data-question-type]');
    const optionsSection = container.querySelector('[data-options-section]');
    const optionsList = container.querySelector('[data-options-list]');
    const uploadValidation = container.querySelector('[data-upload-validation]');
    const standardValidation = container.querySelector('[data-standard-validation]');
    const operator = container.querySelector('[data-condition-operator]');
    const compare = container.querySelector('[data-compare-value]');

    const addOption = () => {
        const index = optionsList.children.length;
        const row = document.createElement('div');
        row.className = 'grid grid-cols-[1fr_1fr_auto] gap-2';
        row.dataset.optionRow = '';
        row.innerHTML = `<input name="options[${index}][label]" placeholder="Label" class="min-w-0 rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm"><input name="options[${index}][value]" placeholder="stored_value" class="min-w-0 rounded-lg border border-gray-300 bg-white px-3 py-2 font-mono text-sm"><button type="button" data-remove-option class="rounded-lg px-3 py-2 text-sm font-bold text-red-600 hover:bg-red-50" aria-label="Remove option">×</button>`;
        optionsList.appendChild(row);
    };
    const syncType = () => {
        const hasOptions = ['select', 'radio', 'checkbox'].includes(type.value);
        optionsSection.classList.toggle('hidden', !hasOptions);
        uploadValidation.classList.toggle('hidden', type.value !== 'upload');
        standardValidation.classList.toggle('hidden', type.value === 'upload');
        if (hasOptions && optionsList.children.length === 0) addOption();
    };
    const syncOperator = () => {
        if (!operator || !compare) return;
        const needsValue = !['is_empty', 'is_not_empty'].includes(operator.value);
        compare.classList.toggle('hidden', !needsValue);
        compare.disabled = !needsValue;
    };

    container.querySelector('[data-add-option]')?.addEventListener('click', addOption);
    container.addEventListener('click', (event) => {
        if (event.target.matches('[data-remove-option]')) event.target.closest('[data-option-row]')?.remove();
    });
    type.addEventListener('change', syncType);
    operator?.addEventListener('change', syncOperator);
    syncType();
    syncOperator();
});
</script>
@endsection
