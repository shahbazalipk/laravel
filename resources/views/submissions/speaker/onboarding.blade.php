@extends('submissions.layout')
@section('title', 'Speaker onboarding')
@section('content')
<div class="space-y-7" data-testid="speaker-onboarding">
    <div><p class="text-sm font-semibold text-indigo-600">{{ $link->submission->type->name }}</p><h1 class="text-3xl font-bold">Speaker onboarding</h1><p class="mt-1 text-slate-500">{{ $link->speaker->full_name }}</p></div>
    <div class="grid lg:grid-cols-[1fr_20rem] gap-6">
        <div class="space-y-6">
            <form method="POST" action="{{ route('submissions.speaker.update', $link) }}" class="rounded-2xl border bg-white p-6 shadow-sm">@csrf @method('PUT')
                <h2 class="text-lg font-bold">Public profile & requirements</h2>
                <div class="mt-5 grid sm:grid-cols-2 gap-4">
                    <label class="block"><span class="text-sm font-semibold">Job title</span><input name="job_title" value="{{ $link->speaker->job_title }}" class="mt-2 w-full rounded-xl border-slate-300"></label>
                    <label class="block"><span class="text-sm font-semibold">Organization</span><input name="company" value="{{ $link->speaker->company }}" class="mt-2 w-full rounded-xl border-slate-300"></label>
                    <label class="sm:col-span-2 block"><span class="text-sm font-semibold">Final biography</span><textarea name="bio" rows="5" class="mt-2 w-full rounded-xl border-slate-300">{{ $link->speaker->bio }}</textarea></label>
                    <label class="block"><span class="text-sm font-semibold">Dietary requirements</span><textarea name="dietary_requirements" class="mt-2 w-full rounded-xl border-slate-300">{{ $link->speaker->profile['dietary_requirements'] ?? '' }}</textarea></label>
                    <label class="block"><span class="text-sm font-semibold">Accessibility requirements</span><textarea name="accessibility_requirements" class="mt-2 w-full rounded-xl border-slate-300">{{ $link->speaker->profile['accessibility_requirements'] ?? '' }}</textarea></label>
                    <label class="sm:col-span-2 inline-flex gap-2 text-sm"><input type="checkbox" name="recording_consent" value="1" @checked($link->speaker->profile['recording_consent'] ?? false)> I consent to recording and distribution under the event terms.</label>
                </div>
                <button class="mt-6 rounded-xl bg-indigo-600 px-5 py-2.5 font-semibold text-white">Submit onboarding</button>
            </form>
            <form method="POST" enctype="multipart/form-data" action="{{ route('submissions.speaker.presentation', $link) }}" class="rounded-2xl border bg-white p-6 shadow-sm">@csrf<h2 class="text-lg font-bold">Presentation material</h2><p class="mt-1 text-sm text-slate-500">Upload PDF, PowerPoint, or MP4 files up to 50 MB.</p><input name="title" placeholder="Presentation title" class="mt-4 w-full rounded-xl border-slate-300"><input type="file" name="presentation" required class="mt-3 w-full rounded-xl border border-dashed p-4"><button class="mt-4 rounded-xl bg-slate-900 px-4 py-2 font-semibold text-white">Upload version</button></form>
            @foreach($link->contracts as $contract)<div class="rounded-2xl border bg-white p-6"><div class="flex justify-between"><h2 class="font-bold">Speaker agreement</h2><span class="text-sm">{{ ucfirst($contract->status) }}</span></div>@if(!in_array($contract->status,['accepted','signed']))<form method="POST" action="{{ route('submissions.speaker.contract.accept', $contract) }}" class="mt-4">@csrf<label class="text-sm"><input type="checkbox" name="accept" value="1" required> I have reviewed and accept the agreement.</label><button class="mt-3 block rounded-xl bg-emerald-600 px-4 py-2 font-semibold text-white">Accept agreement</button></form>@endif</div>@endforeach
        </div>
        <aside class="rounded-2xl bg-slate-900 p-6 text-white h-fit"><p class="text-xs font-semibold uppercase text-indigo-300">Completion</p><p class="mt-2 text-4xl font-bold">{{ $link->onboarding->completion_percent ?? 0 }}%</p><div class="mt-4 h-2 rounded-full bg-slate-700"><div class="h-2 rounded-full bg-indigo-400" style="width:{{ $link->onboarding->completion_percent ?? 0 }}%"></div></div><ul class="mt-6 space-y-3 text-sm text-slate-300">@forelse($link->onboarding?->checklistProgress ?? [] as $item)<li class="flex gap-2"><span>{{ $item->status === 'completed' ? '✓' : '○' }}</span><span>{{ str_replace('_',' ',$item->item_key) }}</span></li>@empty<li>Profile information</li><li>Agreement acceptance</li><li>Presentation upload</li><li>Session confirmation</li>@endforelse</ul></aside>
    </div>
</div>
@endsection
