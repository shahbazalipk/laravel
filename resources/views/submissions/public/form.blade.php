@extends('submissions.layout')
@section('title', $submission->title)
@section('content')
@php($saved = $submission->answers->keyBy('question_key'))
<div class="grid lg:grid-cols-[1fr_18rem] gap-7" data-testid="submission-wizard">
    <form method="POST" enctype="multipart/form-data" action="{{ route('submissions.public.update', compact('eventSlug', 'submission')) }}" class="space-y-6">@csrf @method('PUT')
        <div><p class="text-sm font-semibold text-indigo-600">{{ $submission->type->name }}</p><h1 class="text-3xl font-bold">Submission details</h1></div>
        <label class="block rounded-2xl border bg-white p-5"><span class="text-sm font-bold">Submission title</span><input name="title" value="{{ old('title', $submission->title) }}" required class="mt-2 w-full rounded-xl border-slate-300"></label>
        @foreach($submission->type->sections as $section)
            <section class="rounded-2xl border border-slate-200 bg-white p-5 sm:p-7 shadow-sm" data-form-section="{{ $section->public_id }}">
                <h2 class="text-xl font-bold">{{ $section->title }}</h2>@if($section->description)<p class="mt-1 text-sm text-slate-500">{{ $section->description }}</p>@endif
                <div class="mt-6 space-y-6">
                @foreach($section->questions->where('is_active', true) as $question)
                    @php($value = old('answers.'.$question->key, $saved->get($question->key)?->answer_json ?? $saved->get($question->key)?->answer_text))
                    <div data-question="{{ $question->key }}">
                        @if(in_array(($question->type->value ?? $question->type), ['heading','description','divider']))
                            @if(($question->type->value ?? $question->type) === 'heading')<h3 class="text-lg font-bold">{{ $question->label }}</h3>@elseif(($question->type->value ?? $question->type) === 'divider')<hr>@else<p class="text-sm text-slate-600">{{ $question->help_text ?: $question->label }}</p>@endif
                        @else
                            <label class="block text-sm font-semibold">{{ $question->label }} @if($question->is_required)<span class="text-red-500">*</span>@endif</label>
                            @if($question->help_text)<p class="mt-1 text-xs text-slate-500">{{ $question->help_text }}</p>@endif
                            @switch($question->type->value ?? $question->type)
                                @case('textarea') @case('rich_text')
                                    <textarea name="answers[{{ $question->key }}]" rows="5" @required($question->is_required) class="mt-2 w-full rounded-xl border-slate-300">{{ $value }}</textarea>
                                    @break
                                @case('select') @case('country') @case('city')
                                    <select name="answers[{{ $question->key }}]" @required($question->is_required) class="mt-2 w-full rounded-xl border-slate-300"><option value="">Select…</option>@foreach($question->options as $option)<option value="{{ $option->value }}" @selected($value == $option->value)>{{ $option->label }}</option>@endforeach</select>
                                    @break
                                @case('multiselect') @case('checkbox')
                                    <div class="mt-2 grid sm:grid-cols-2 gap-2">@foreach($question->options as $option)<label class="rounded-xl border p-3 text-sm"><input type="checkbox" name="answers[{{ $question->key }}][]" value="{{ $option->value }}" @checked(in_array($option->value, (array) $value))> {{ $option->label }}</label>@endforeach</div>
                                    @break
                                @case('radio') @case('yes_no')
                                    <div class="mt-2 flex flex-wrap gap-3">@foreach(($question->options->isNotEmpty() ? $question->options : collect([(object)['value'=>'yes','label'=>'Yes'],(object)['value'=>'no','label'=>'No']])) as $option)<label class="rounded-xl border px-4 py-3 text-sm"><input type="radio" name="answers[{{ $question->key }}]" value="{{ $option->value }}" @checked($value == $option->value)> {{ $option->label }}</label>@endforeach</div>
                                    @break
                                @case('file') @case('image')
                                    <input type="file" name="files[]" class="mt-2 block w-full rounded-xl border border-dashed border-slate-300 p-4 text-sm">
                                    @break
                                @default
                                    <input type="{{ match($question->type->value ?? $question->type) {'email'=>'email','number','decimal','rating'=>'number','date'=>'date','datetime'=>'datetime-local','time'=>'time','url'=>'url',default=>'text'} }}" name="answers[{{ $question->key }}]" value="{{ $value }}" @required($question->is_required) placeholder="{{ $question->placeholder }}" class="mt-2 w-full rounded-xl border-slate-300">
                            @endswitch
                        @endif
                    </div>
                @endforeach
                </div>
            </section>
        @endforeach
        <div class="sticky bottom-3 flex flex-col sm:flex-row gap-3 rounded-2xl border bg-white/95 p-4 shadow-xl backdrop-blur"><button class="rounded-xl bg-slate-900 px-5 py-3 font-semibold text-white">Save draft</button></div>
    </form>
    <aside class="space-y-4 lg:sticky lg:top-6 h-fit">
        <div class="rounded-2xl bg-indigo-600 p-5 text-white"><p class="text-xs font-semibold uppercase text-indigo-200">Progress</p><p class="mt-2 text-2xl font-bold">{{ $submission->answers->count() }} answers saved</p><p class="mt-2 text-sm text-indigo-100">Your work is saved whenever you use Save draft.</p></div>
        @if($submission->is_draft)<form method="POST" action="{{ route('submissions.public.submit', compact('eventSlug', 'submission')) }}">@csrf<button class="w-full rounded-xl bg-emerald-600 px-5 py-3 font-bold text-white shadow-lg">Review & submit</button></form>@endif
    </aside>
</div>
@endsection
