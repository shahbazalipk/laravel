<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $form->heading ?: $form->name }}</title>
    @vite(['resources/css/app.css'])
</head>
<body class="bg-slate-50">
<div class="mx-auto max-w-2xl px-4 py-10">
    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8" data-testid="public-inquiry-form">
        <h1 class="text-2xl font-bold text-slate-900">{{ $form->heading ?: $form->name }}</h1>
        @if($form->intro_text)
            <p class="mt-2 text-slate-600">{{ $form->intro_text }}</p>
        @endif

        @if(session('success') || !empty($submitted))
            <div class="mt-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-emerald-800">
                {{ session('success') ?: ($form->success_message ?: 'Thank you. Your submission was received.') }}
            </div>
        @else
            <form method="POST" action="{{ url('/sales/f/'.$form->slug) }}" enctype="multipart/form-data" class="mt-6 space-y-5">
                @csrf
                @foreach($form->fields as $field)
                    <div>
                        <label class="mb-2 block text-sm font-medium text-slate-700">
                            {{ $field->label }}
                            @if($field->is_required)<span class="text-rose-600">*</span>@endif
                        </label>
                        @if($field->type->value === 'textarea')
                            <textarea name="answers[{{ $field->key }}]" rows="4" @required($field->is_required)
                                      class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm">{{ old('answers.'.$field->key) }}</textarea>
                        @elseif($field->type->value === 'upload')
                            <input type="file" name="answers[{{ $field->key }}]" @required($field->is_required)
                                   class="block w-full text-sm">
                        @elseif(in_array($field->type->value, ['select', 'radio'], true))
                            <select name="answers[{{ $field->key }}]" @required($field->is_required)
                                    class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm">
                                <option value="">Select…</option>
                                @foreach(($field->options ?? []) as $option)
                                    @php($opt = is_array($option) ? ($option['value'] ?? $option['label'] ?? '') : $option)
                                    <option value="{{ $opt }}">{{ is_array($option) ? ($option['label'] ?? $opt) : $opt }}</option>
                                @endforeach
                            </select>
                        @else
                            <input type="{{ $field->type->value === 'email' ? 'email' : ($field->type->value === 'number' ? 'number' : 'text') }}"
                                   name="answers[{{ $field->key }}]" value="{{ old('answers.'.$field->key) }}"
                                   @required($field->is_required)
                                   class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm">
                        @endif
                        @error('answers.'.$field->key)<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
                    </div>
                @endforeach
                <button type="submit" class="w-full rounded-xl bg-indigo-600 px-4 py-3 font-semibold text-white">
                    {{ $form->submit_button_label ?: 'Submit' }}
                </button>
            </form>
        @endif
    </div>
</div>
<script>
    if (window.parent !== window) {
        const sendHeight = () => window.parent.postMessage({ type: 'sales-form-resize', height: document.body.scrollHeight }, '*');
        sendHeight();
        window.addEventListener('load', sendHeight);
        new ResizeObserver(sendHeight).observe(document.body);
    }
</script>
</body>
</html>
