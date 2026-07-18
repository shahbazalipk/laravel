@extends('admin.layout')

@section('title', $type->exists ? 'Configure Submission Type' : 'Create Submission Type')

@section('content')
@php($editing = $type->exists)
<div class="space-y-6" data-testid="submission-type-builder">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div><p class="text-sm font-semibold text-indigo-600">Configuration studio</p><h2 class="text-3xl font-bold text-slate-900">{{ $editing ? $type->name : 'New submission type' }}</h2></div>
        @if($editing)
            <div class="flex gap-2">
                <a target="_blank" href="{{ route('submissions.public.landing', ['eventSlug' => request()->getHost(), 'typeSlug' => $type->slug]) }}" class="rounded-xl border bg-white px-4 py-2 text-sm font-semibold">Preview</a>
                <form method="POST" action="{{ route('admin.submissions.types.publish', $type) }}">@csrf<button class="rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white">Publish version</button></form>
            </div>
        @endif
    </div>

    @if($errors->any())<div class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700"><ul class="list-disc pl-5">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

    <form method="POST" action="{{ $editing ? route('admin.submissions.types.update', $type) : route('admin.submissions.types.store') }}" class="rounded-2xl border border-slate-200 bg-white p-5 sm:p-7 shadow-sm">
        @csrf @if($editing) @method('PUT') @endif
        <div class="grid md:grid-cols-2 gap-5">
            <label class="block"><span class="text-sm font-semibold">Name</span><input name="name" value="{{ old('name', $type->name) }}" required class="mt-2 w-full rounded-xl border-slate-300"></label>
            <label class="block"><span class="text-sm font-semibold">Internal code</span><input name="code" value="{{ old('code', $type->code) }}" required class="mt-2 w-full rounded-xl border-slate-300"></label>
            <label class="block"><span class="text-sm font-semibold">Public title</span><input name="public_title" value="{{ old('public_title', $type->public_title) }}" class="mt-2 w-full rounded-xl border-slate-300"></label>
            <label class="block"><span class="text-sm font-semibold">Status</span><select name="status" class="mt-2 w-full rounded-xl border-slate-300">@foreach(['draft','active','closed','archived'] as $status)<option @selected(old('status', $type->status ?: 'draft') === $status)>{{ $status }}</option>@endforeach</select></label>
            <label class="block"><span class="text-sm font-semibold">Opens at</span><input type="datetime-local" name="opens_at" value="{{ old('opens_at', optional($type->opens_at)->format('Y-m-d\TH:i')) }}" class="mt-2 w-full rounded-xl border-slate-300"></label>
            <label class="block"><span class="text-sm font-semibold">Closes at</span><input type="datetime-local" name="closes_at" value="{{ old('closes_at', optional($type->closes_at)->format('Y-m-d\TH:i')) }}" class="mt-2 w-full rounded-xl border-slate-300"></label>
            <label class="block"><span class="text-sm font-semibold">Timezone</span><input name="timezone" value="{{ old('timezone', $type->timezone ?: config('app.timezone')) }}" class="mt-2 w-full rounded-xl border-slate-300"></label>
            <label class="block"><span class="text-sm font-semibold">Applicant limit</span><input type="number" min="1" name="maximum_submissions_per_applicant" value="{{ old('maximum_submissions_per_applicant', $type->maximum_submissions_per_applicant) }}" class="mt-2 w-full rounded-xl border-slate-300"></label>
            <label class="block"><span class="text-sm font-semibold">Number prefix</span><input name="number_prefix" value="{{ old('number_prefix', $type->number_prefix) }}" placeholder="ABS" class="mt-2 w-full rounded-xl border-slate-300"></label>
            <label class="block"><span class="text-sm font-semibold">Number pattern</span><input name="number_pattern" value="{{ old('number_pattern', $type->number_pattern ?: '{PREFIX}-{YEAR}-{NUMBER:4}') }}" class="mt-2 w-full rounded-xl border-slate-300"></label>
            <label class="md:col-span-2 block"><span class="text-sm font-semibold">Description</span><textarea name="description" rows="3" class="mt-2 w-full rounded-xl border-slate-300">{{ old('description', $type->description) }}</textarea></label>
            <label class="md:col-span-2 block"><span class="text-sm font-semibold">Instructions</span><textarea name="instructions" rows="4" class="mt-2 w-full rounded-xl border-slate-300">{{ old('instructions', $type->instructions) }}</textarea></label>
        </div>
        <div class="mt-6 flex flex-wrap gap-5">
            @foreach(['allow_drafts' => 'Draft saving', 'allow_editing_after_submission' => 'Post-submit editing', 'allow_anonymous_review' => 'Anonymous review', 'enable_scoring' => 'Scoring', 'enable_revisions' => 'Revisions', 'enable_speaker_onboarding' => 'Speaker onboarding'] as $field => $label)
                <label class="inline-flex items-center gap-2 text-sm"><input type="hidden" name="{{ $field }}" value="0"><input type="checkbox" name="{{ $field }}" value="1" @checked(old($field, $type->{$field})) class="rounded border-slate-300 text-indigo-600"> {{ $label }}</label>
            @endforeach
        </div>
        <button class="mt-7 rounded-xl bg-indigo-600 px-5 py-2.5 font-semibold text-white">{{ $editing ? 'Save settings' : 'Create and continue' }}</button>
    </form>

    @if($editing)
    <div class="grid xl:grid-cols-3 gap-6">
        <section class="xl:col-span-2 rounded-2xl border bg-white p-5 shadow-sm" data-testid="form-builder">
            <h3 class="text-xl font-bold">Form sections & questions</h3>
            <div class="mt-5 space-y-4">
                @foreach($type->sections as $section)
                    <div class="rounded-xl border border-slate-200 p-4">
                        <h4 class="font-bold">{{ $section->title }}</h4>
                        <div class="mt-3 space-y-2">@foreach($section->questions as $question)<div class="flex justify-between rounded-lg bg-slate-50 px-3 py-2 text-sm"><span>{{ $question->label }} <span class="text-slate-400">({{ $question->type->value ?? $question->type }})</span></span><form method="POST" action="{{ route('admin.submissions.questions.destroy', $question) }}">@csrf @method('DELETE')<button class="text-red-600">Archive</button></form></div>@endforeach</div>
                        <form method="POST" action="{{ route('admin.submissions.questions.store', $section) }}" class="mt-4 grid sm:grid-cols-2 gap-3">@csrf
                            <input name="label" required placeholder="Question label" class="rounded-lg border-slate-300 text-sm"><input name="key" required placeholder="field_key" class="rounded-lg border-slate-300 text-sm">
                            <select name="type" class="rounded-lg border-slate-300 text-sm">@foreach(['text','textarea','rich_text','email','phone','number','decimal','date','datetime','time','select','multiselect','radio','checkbox','yes_no','country','city','url','file','image','rating','consent','heading','description','divider','hidden','calculated','repeater'] as $fieldType)<option>{{ $fieldType }}</option>@endforeach</select>
                            <input name="options" placeholder="Options, one per line" class="rounded-lg border-slate-300 text-sm"><label class="text-sm"><input type="checkbox" name="is_required" value="1"> Required</label>
                            <button class="justify-self-start rounded-lg bg-slate-900 px-3 py-2 text-sm font-semibold text-white">Add question</button>
                        </form>
                    </div>
                @endforeach
            </div>
            <form method="POST" action="{{ route('admin.submissions.sections.store', $type) }}" class="mt-5 flex flex-col sm:flex-row gap-3">@csrf<input name="title" required placeholder="New section title" class="flex-1 rounded-xl border-slate-300"><button class="rounded-xl bg-indigo-50 px-4 py-2 font-semibold text-indigo-700">Add section</button></form>
        </section>
        <div class="space-y-6">
            <section class="rounded-2xl border bg-white p-5 shadow-sm" data-testid="conditional-builder">
                <h3 class="font-bold">Conditional logic</h3>
                <div class="mt-3 space-y-2">@foreach($type->conditionalRules as $rule)<div class="rounded-lg bg-slate-50 px-3 py-2 text-xs">{{ $rule->sourceQuestion->label }} {{ str_replace('_',' ',$rule->operator) }} {{ is_array($rule->compare_value) ? collect($rule->compare_value)->join(', ') : $rule->compare_value }} → {{ $rule->action }} {{ $rule->targetQuestion?->label }}</div>@endforeach</div>
                <form method="POST" action="{{ route('admin.submissions.rules.store', $type) }}" class="mt-4 space-y-2">@csrf
                    <select name="source_question_id" required class="w-full rounded-lg border-slate-300 text-sm"><option value="">Source question</option>@foreach($type->sections->flatMap->questions as $question)<option value="{{ $question->id }}">{{ $question->label }}</option>@endforeach</select>
                    <select name="operator" class="w-full rounded-lg border-slate-300 text-sm">@foreach(['equals','not_equals','contains','not_contains','is_empty','is_not_empty','includes_any','includes_all','greater_than','less_than','date_before','date_after'] as $operator)<option>{{ $operator }}</option>@endforeach</select>
                    <input name="compare_value" placeholder="Comparison value" class="w-full rounded-lg border-slate-300 text-sm">
                    <select name="action" class="w-full rounded-lg border-slate-300 text-sm">@foreach(['show','hide','require','optional','enable','disable','set_default','clear'] as $action)<option>{{ $action }}</option>@endforeach</select>
                    <select name="target_question_id" required class="w-full rounded-lg border-slate-300 text-sm"><option value="">Target question</option>@foreach($type->sections->flatMap->questions as $question)<option value="{{ $question->id }}">{{ $question->label }}</option>@endforeach</select>
                    <button class="rounded-lg bg-slate-900 px-3 py-2 text-sm font-semibold text-white">Add rule</button>
                </form>
            </section>
            <section class="rounded-2xl border bg-white p-5 shadow-sm" data-testid="workflow-builder">
                <h3 class="font-bold">Workflow</h3>
                <div class="mt-3 space-y-2">@foreach($type->workflowStages as $stage)<div class="rounded-lg bg-slate-50 px-3 py-2 text-sm">{{ $stage->name }} · {{ $stage->category->value ?? $stage->category }}</div>@endforeach</div>
                <form method="POST" action="{{ route('admin.submissions.stages.store', $type) }}" class="mt-4 space-y-2">@csrf<input name="name" required placeholder="Stage name" class="w-full rounded-lg border-slate-300 text-sm"><input name="slug" required placeholder="stage-code" class="w-full rounded-lg border-slate-300 text-sm"><input name="category" required placeholder="review" class="w-full rounded-lg border-slate-300 text-sm"><button class="rounded-lg bg-slate-900 px-3 py-2 text-sm font-semibold text-white">Add stage</button></form>
            </section>
            <section class="rounded-2xl border bg-white p-5 shadow-sm" data-testid="scorecard-builder">
                <h3 class="font-bold">Scorecard</h3>
                @foreach($type->scorecards as $scorecard) @foreach($scorecard->criteria as $criterion)<div class="mt-2 rounded-lg bg-slate-50 px-3 py-2 text-sm">{{ $criterion->name }} · {{ $criterion->weight }}%</div>@endforeach @endforeach
                <form method="POST" action="{{ route('admin.submissions.criteria.store', $type) }}" class="mt-4 space-y-2">@csrf<input name="name" required placeholder="Criterion" class="w-full rounded-lg border-slate-300 text-sm"><select name="type" class="w-full rounded-lg border-slate-300 text-sm"><option value="numeric">Numeric</option><option value="yes_no">Yes / No</option><option value="text">Comment</option></select><div class="grid grid-cols-3 gap-2"><input type="number" name="minimum_score" value="0" class="rounded-lg border-slate-300 text-sm"><input type="number" name="maximum_score" value="5" class="rounded-lg border-slate-300 text-sm"><input type="number" name="weight" value="100" class="rounded-lg border-slate-300 text-sm"></div><button class="rounded-lg bg-slate-900 px-3 py-2 text-sm font-semibold text-white">Add criterion</button></form>
            </section>
        </div>
    </div>
    @endif
</div>
@endsection
