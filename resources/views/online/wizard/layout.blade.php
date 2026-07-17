<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Register') - {{ $event->title }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-50">
<div class="min-h-screen py-8 px-4 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-4xl">
        <div class="mb-6 overflow-hidden rounded-2xl bg-white shadow-lg">
            <div class="bg-gradient-to-r from-indigo-600 to-violet-600 px-6 py-6 text-white sm:px-8">
                <h1 class="text-2xl font-bold sm:text-3xl">{{ $event->title }}</h1>
                <p class="mt-2 text-sm text-indigo-100 sm:text-base">{{ $event->seo_description ?? 'Complete your registration in a few steps' }}</p>
            </div>

            @include('online.partials.event-details')

            <nav class="border-b border-slate-200 px-4 py-4 sm:px-8" aria-label="Registration progress" data-testid="wizard-progress">
                <ol class="flex items-center gap-2 overflow-x-auto pb-1">
                    @foreach($steps as $step)
                        @php
                            $isCurrent = $activeStep === $step;
                            $isComplete = $activeStep->number() > $step->number();
                            $isReachable = $draft && $step->canAccessFrom($draft->current_step);
                        @endphp
                        <li class="flex min-w-max items-center gap-2">
                            @if($isReachable)
                                <a href="{{ $wizardStepRoute($step->value) }}"
                                   class="inline-flex items-center gap-2 rounded-full px-3 py-1.5 text-xs font-semibold transition {{ $isCurrent ? 'bg-indigo-600 text-white' : ($isComplete ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-600') }}"
                                   @if($isCurrent) aria-current="step" @endif
                                   data-testid="wizard-step-{{ $step->value }}">
                                    <span class="inline-flex h-5 w-5 items-center justify-center rounded-full bg-white/20 text-[11px]">{{ $step->number() }}</span>
                                    {{ $step->label() }}
                                </a>
                            @else
                                <span class="inline-flex items-center gap-2 rounded-full bg-slate-100 px-3 py-1.5 text-xs font-semibold text-slate-400"
                                      data-testid="wizard-step-{{ $step->value }}">
                                    <span class="inline-flex h-5 w-5 items-center justify-center rounded-full bg-white text-[11px]">{{ $step->number() }}</span>
                                    {{ $step->label() }}
                                </span>
                            @endif
                            @if(!$loop->last)
                                <span class="hidden h-px w-6 bg-slate-200 sm:block" aria-hidden="true"></span>
                            @endif
                        </li>
                    @endforeach
                </ol>
            </nav>
        </div>

        @if(session('success'))
            <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800" data-testid="flash-success">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800" data-testid="flash-error">
                {{ session('error') }}
            </div>
        @endif

        @if($errors->any())
            <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800" data-testid="flash-errors" role="alert">
                <ul class="list-disc space-y-1 pl-4">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="rounded-2xl bg-white p-5 shadow-lg sm:p-8" data-testid="wizard-panel">
            @yield('content')
        </div>

        @include('online.partials.event-url-content')

        @include('online.partials.contact-footer')
    </div>
</div>
@stack('scripts')
</body>
</html>
