@php
    $hasLinkedContent = $eventUrlSponsors->isNotEmpty()
        || $eventUrlPartners->isNotEmpty()
        || filled($eventUrl->custom_html);
@endphp

@if($hasLinkedContent)
    <div class="mt-6 space-y-6" data-testid="event-url-linked-content">
        @if(filled($eventUrl->custom_html))
            <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-8"
                     data-testid="event-url-custom-content">
                <div class="text-slate-700 [&_a]:font-semibold [&_a]:text-indigo-600 [&_a]:underline [&_blockquote]:border-l-4 [&_blockquote]:border-indigo-200 [&_blockquote]:pl-4 [&_h1]:mb-3 [&_h1]:text-2xl [&_h1]:font-bold [&_h2]:mb-3 [&_h2]:text-xl [&_h2]:font-bold [&_h3]:mb-2 [&_h3]:text-lg [&_h3]:font-semibold [&_img]:max-w-full [&_img]:rounded-xl [&_li]:ml-5 [&_li]:list-disc [&_ol]:space-y-1 [&_p]:mb-3 [&_ul]:space-y-1">
                    {!! $eventUrl->custom_html !!}
                </div>
            </section>
        @endif

        @if($eventUrlSponsors->isNotEmpty())
            <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-8"
                     aria-labelledby="registration-sponsors-title"
                     data-testid="event-url-sponsors">
                <div class="mb-5 text-center">
                    <p class="text-xs font-bold uppercase tracking-[0.2em] text-indigo-600">With support from</p>
                    <h2 id="registration-sponsors-title" class="mt-1 text-xl font-bold text-slate-900">Our Sponsors</h2>
                </div>
                <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4">
                    @foreach($eventUrlSponsors as $sponsor)
                        @php
                            $sponsorWebsite = \Illuminate\Support\Str::startsWith(
                                strtolower((string) $sponsor->website_url),
                                ['https://', 'http://']
                            ) ? $sponsor->website_url : null;
                        @endphp
                        <a @if($sponsorWebsite) href="{{ $sponsorWebsite }}" target="_blank" rel="noopener noreferrer" @endif
                           class="flex min-h-32 flex-col items-center justify-center rounded-xl border border-slate-200 bg-slate-50 p-4 text-center transition {{ $sponsorWebsite ? 'hover:-translate-y-0.5 hover:border-indigo-200 hover:shadow-md' : 'cursor-default' }}">
                            @if($sponsor->logo_thumbnail_url)
                                <img src="{{ $sponsor->logo_thumbnail_url }}" alt="{{ $sponsor->name }}" class="mb-3 h-14 w-full object-contain" loading="lazy">
                            @else
                                <span class="mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-indigo-100 text-lg font-bold text-indigo-700">
                                    {{ mb_substr($sponsor->name, 0, 1) }}
                                </span>
                            @endif
                            <span class="text-sm font-semibold text-slate-800">{{ $sponsor->name }}</span>
                            @if($sponsor->sponsorship_label)
                                <span class="mt-1 text-xs text-slate-500">{{ $sponsor->sponsorship_label }}</span>
                            @endif
                        </a>
                    @endforeach
                </div>
            </section>
        @endif

        @if($eventUrlPartners->isNotEmpty())
            <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-8"
                     aria-labelledby="registration-partners-title"
                     data-testid="event-url-partners">
                <div class="mb-5 text-center">
                    <p class="text-xs font-bold uppercase tracking-[0.2em] text-violet-600">Working together</p>
                    <h2 id="registration-partners-title" class="mt-1 text-xl font-bold text-slate-900">Our Partners</h2>
                </div>
                <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4">
                    @foreach($eventUrlPartners as $partner)
                        @php
                            $partnerWebsite = \Illuminate\Support\Str::startsWith(
                                strtolower((string) $partner->website_url),
                                ['https://', 'http://']
                            ) ? $partner->website_url : null;
                        @endphp
                        <a @if($partnerWebsite) href="{{ $partnerWebsite }}" target="_blank" rel="noopener noreferrer" @endif
                           class="flex min-h-32 flex-col items-center justify-center rounded-xl border border-slate-200 bg-slate-50 p-4 text-center transition {{ $partnerWebsite ? 'hover:-translate-y-0.5 hover:border-violet-200 hover:shadow-md' : 'cursor-default' }}">
                            @if($partner->logo_thumbnail_url)
                                <img src="{{ $partner->logo_thumbnail_url }}" alt="{{ $partner->name }}" class="mb-3 h-14 w-full object-contain" loading="lazy">
                            @else
                                <span class="mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-violet-100 text-lg font-bold text-violet-700">
                                    {{ mb_substr($partner->name, 0, 1) }}
                                </span>
                            @endif
                            <span class="text-sm font-semibold text-slate-800">{{ $partner->name }}</span>
                            @if($partner->sponsorship_label)
                                <span class="mt-1 text-xs text-slate-500">{{ $partner->sponsorship_label }}</span>
                            @endif
                        </a>
                    @endforeach
                </div>
            </section>
        @endif
    </div>
@endif
