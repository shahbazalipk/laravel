@php
    $hasDates = $event->start_date || $event->end_date;
    $venue = $event->location ?: $event->address_line1;
    $hasVenue = $venue || $event->address_line2 || $event->city || $event->state || $event->country;
    $format = $event->format ?: $event->event_mode;
@endphp

@if($hasDates || $hasVenue || $format)
    <section class="border-b border-slate-200 bg-white px-5 py-5 sm:px-8"
             aria-label="Event details"
             data-testid="event-details">
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @if($hasDates)
                <div class="flex items-start gap-3">
                    <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </span>
                    <div class="min-w-0">
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Date & time</p>
                        <p class="mt-1 text-sm font-semibold text-slate-900">
                            {{ $event->start_date?->format('M d, Y') }}
                            @if($event->end_date && (!$event->start_date || !$event->start_date->isSameDay($event->end_date)))
                                – {{ $event->end_date->format('M d, Y') }}
                            @endif
                        </p>
                        @if($event->start_date)
                            <p class="mt-0.5 text-xs text-slate-500">
                                {{ $event->start_date->format('g:i A') }}
                                @if($event->timezone) · {{ $event->timezone }} @endif
                            </p>
                        @endif
                    </div>
                </div>
            @endif

            @if($hasVenue)
                <div class="flex items-start gap-3">
                    <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a2 2 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </span>
                    <div class="min-w-0">
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Venue</p>
                        <p class="mt-1 text-sm font-semibold text-slate-900">{{ $venue ?: 'Venue to be announced' }}</p>
                        @if($event->address_line2 || $event->city || $event->state || $event->country)
                            <p class="mt-0.5 text-xs text-slate-500">
                                {{ collect([$event->address_line2, $event->city, $event->state, $event->country])->filter()->implode(', ') }}
                            </p>
                        @endif
                        @if($event->map_url)
                            <a href="{{ $event->map_url }}" target="_blank" rel="noopener" class="mt-1 inline-flex text-xs font-semibold text-indigo-600 hover:text-indigo-800">
                                View map
                            </a>
                        @endif
                    </div>
                </div>
            @endif

            @if($format)
                <div class="flex items-start gap-3">
                    <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h10M4 18h10"/>
                        </svg>
                    </span>
                    <div class="min-w-0">
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Event format</p>
                        <p class="mt-1 text-sm font-semibold capitalize text-slate-900">{{ $format }}</p>
                        @if($event->type)
                            <p class="mt-0.5 text-xs capitalize text-slate-500">{{ $event->type }}</p>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </section>
@endif
