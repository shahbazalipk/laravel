@if($customFormResponses->isNotEmpty())
    <div class="rounded-xl bg-white p-6 shadow-sm">
        <h2 class="mb-5 text-lg font-semibold text-gray-800">Custom form responses</h2>

        <div class="space-y-6">
            @foreach($customFormResponses as $response)
                <section>
                    <div class="mb-3 border-b border-slate-100 pb-2">
                        <h3 class="font-semibold text-slate-900">{{ $response->form?->name ?? 'Custom form' }}</h3>
                        @if($response->submitted_at)
                            <p class="mt-1 text-xs text-slate-500">Submitted {{ $response->submitted_at->format('M d, Y H:i') }}</p>
                        @endif
                    </div>

                    <dl class="grid grid-cols-1 gap-4 text-sm sm:grid-cols-2">
                        @foreach($response->answers as $answer)
                            @php
                                $options = $answer->question?->options?->keyBy(fn ($option) => (string) $option->value) ?? collect();
                                $rawValues = $answer->question_type->value === 'checkbox'
                                    ? ($answer->value['values'] ?? [])
                                    : [$answer->value['value'] ?? null];
                                $displayValues = collect($rawValues)
                                    ->reject(fn ($value) => $value === null || $value === '')
                                    ->map(function ($value) use ($options) {
                                        $option = $options->get((string) $value);
                                        return $option ? "{$option->label} ({$value})" : (string) $value;
                                    });
                            @endphp

                            <div class="{{ $answer->question_type->value === 'upload' ? 'sm:col-span-2' : '' }}">
                                <dt class="text-slate-500">{{ $answer->question_label }}</dt>
                                <dd class="mt-1 font-medium text-slate-900">
                                    @if($answer->question_type->value === 'upload')
                                        @forelse($answer->files as $file)
                                            <a href="{{ route('admin.custom-form-answer-files.download', $file) }}"
                                               class="inline-flex items-center rounded-lg bg-slate-100 px-3 py-2 text-sm text-indigo-700 hover:bg-slate-200">
                                                Download {{ $file->original_name }}
                                                <span class="ml-2 text-xs font-normal text-slate-500">Secure file</span>
                                            </a>
                                        @empty
                                            <span class="text-slate-500">Upload unavailable</span>
                                        @endforelse
                                    @elseif($displayValues->isNotEmpty())
                                        {{ $displayValues->join(', ') }}
                                    @else
                                        <span class="text-slate-400">—</span>
                                    @endif
                                </dd>
                            </div>
                        @endforeach
                    </dl>
                </section>
            @endforeach
        </div>
    </div>
@endif
