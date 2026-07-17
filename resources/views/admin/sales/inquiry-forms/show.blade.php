@extends('admin.layout')

@section('title', $form->name ?? 'Inquiry Form')

@section('content')
<div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
    <div>
        <a href="{{ route('admin.sales.inquiry-forms.index') }}" class="text-sm font-semibold text-indigo-600">← Inquiry Forms</a>
        <h1 class="mt-3 text-3xl font-bold text-gray-900">{{ $form->name }}</h1>
        <p class="mt-1 text-gray-600">{{ $form->status->label() }} · /sales/f/{{ $form->slug }}</p>
    </div>
    <div class="flex flex-wrap gap-2">
        <a href="{{ route('admin.sales.inquiry-forms.edit', $form) }}" class="rounded-xl border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700">Edit</a>
        @if($form->status->value !== 'published')
            <form method="POST" action="{{ route('admin.sales.inquiry-forms.publish', $form) }}">@csrf<button class="rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white">Publish</button></form>
        @else
            <form method="POST" action="{{ route('admin.sales.inquiry-forms.unpublish', $form) }}">@csrf<button class="rounded-xl bg-amber-500 px-4 py-2 text-sm font-semibold text-white">Unpublish</button></form>
        @endif
    </div>
</div>

<div class="grid gap-6 lg:grid-cols-3">
    <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm lg:col-span-2">
        <h2 class="text-lg font-semibold">Fields</h2>
        <ul class="mt-4 space-y-3">
            @foreach($form->fields as $field)
                <li class="rounded-xl border border-gray-100 px-4 py-3">
                    <div class="font-semibold text-gray-900">{{ $field->label }}</div>
                    <div class="text-xs text-gray-500">{{ $field->key }} · {{ $field->type->label() }}</div>
                </li>
            @endforeach
        </ul>
    </section>
    <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm text-sm">
        <h2 class="text-lg font-semibold">Public access</h2>
        <p class="mt-3 break-all text-indigo-600">{{ url('/sales/f/'.$form->slug) }}</p>
        @if($form->embed_token)
            <p class="mt-4 font-semibold text-gray-800">Embed snippet</p>
            <pre class="mt-2 overflow-x-auto rounded-lg bg-slate-900 p-3 text-xs text-slate-100" data-testid="embed-snippet">&lt;div data-sales-form-token="{{ $form->embed_token }}" data-sales-form-base="{{ url('/') }}"&gt;&lt;/div&gt;
&lt;script src="{{ url('/js/sales/embed.js') }}" async&gt;&lt;/script&gt;</pre>
            @if(!empty($form->allowed_domains))
                <p class="mt-3 text-gray-600">Allowed domains: {{ implode(', ', $form->allowed_domains) }}</p>
            @endif
        @endif
        <form method="POST" action="{{ route('admin.sales.inquiry-forms.duplicate', $form) }}" class="mt-4">
            @csrf
            <button class="rounded-xl border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700">Duplicate</button>
        </form>
    </section>
</div>
@endsection
