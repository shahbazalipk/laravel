@php
    $hasContact = $event->manager_name || $event->manager_email || $event->manager_phone || $event->website_url || $event->footer_information;
@endphp

@if($hasContact)
    <footer class="mt-6 overflow-hidden rounded-2xl border border-slate-200 bg-slate-900 text-white shadow-lg"
            data-testid="registration-contact-footer">
        <div class="grid grid-cols-1 gap-6 px-5 py-6 sm:px-8 md:grid-cols-[1.2fr_1fr]">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-indigo-300">We’re here to help</p>
                <h2 class="mt-2 text-lg font-semibold">Questions about your registration?</h2>
                @if($event->footer_information)
                    <p class="mt-2 max-w-xl text-sm leading-6 text-slate-300">{{ $event->footer_information }}</p>
                @else
                    <p class="mt-2 text-sm leading-6 text-slate-300">Contact the event team and we’ll be happy to assist.</p>
                @endif
            </div>

            <div class="grid grid-cols-1 gap-3 text-sm sm:grid-cols-2 md:grid-cols-1">
                @if($event->manager_name)
                    <div>
                        <p class="text-xs uppercase tracking-wide text-slate-400">Event contact</p>
                        <p class="mt-1 font-semibold text-white">{{ $event->manager_name }}</p>
                    </div>
                @endif
                @if($event->manager_email)
                    <a href="mailto:{{ $event->manager_email }}" class="group flex items-center gap-2 text-slate-200 hover:text-white">
                        <svg class="h-4 w-4 text-indigo-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                        <span class="break-all">{{ $event->manager_email }}</span>
                    </a>
                @endif
                @if($event->manager_phone)
                    <a href="tel:{{ preg_replace('/[^0-9+]/', '', $event->manager_phone) }}" class="flex items-center gap-2 text-slate-200 hover:text-white">
                        <svg class="h-4 w-4 text-indigo-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.95.684l1.5 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.04 11.04 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.5a1 1 0 01.684.95V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                        </svg>
                        {{ $event->manager_phone }}
                    </a>
                @endif
                @if($event->website_url)
                    <a href="{{ $event->website_url }}" target="_blank" rel="noopener" class="inline-flex items-center gap-2 font-semibold text-indigo-300 hover:text-indigo-200">
                        Visit event website
                        <span aria-hidden="true">↗</span>
                    </a>
                @endif
            </div>
        </div>
    </footer>
@endif
