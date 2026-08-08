@extends('admin.layout')

@section('title', 'Custom Questions Report')

@section('content')
@php
    $chartPalette = [
        'rgb(99, 102, 241)',
        'rgb(16, 185, 129)',
        'rgb(245, 158, 11)',
        'rgb(236, 72, 153)',
        'rgb(14, 165, 233)',
        'rgb(168, 85, 247)',
        'rgb(239, 68, 68)',
        'rgb(100, 116, 139)',
    ];
@endphp

<div class="mb-6" data-testid="reports-questions-page">
    <div class="mb-4 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <a href="{{ route('admin.reports.index') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-800">← All reports</a>
            <h1 class="mt-2 text-2xl font-bold text-gray-900">Custom questions report</h1>
            <p class="mt-1 text-sm text-gray-600">Answer distribution charts from registration custom forms</p>
        </div>
        <a href="{{ route('admin.reports.questions.export', ['from' => $from, 'to' => $to]) }}"
           class="inline-flex items-center rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700"
           data-testid="reports-questions-export">
            Export CSV
        </a>
    </div>

    <form method="GET" action="{{ route('admin.reports.questions') }}" class="mb-6 flex flex-col gap-3 rounded-xl bg-white p-4 shadow-sm sm:flex-row sm:items-end" data-testid="reports-questions-filters">
        <div>
            <label class="mb-1 block text-xs font-medium text-gray-500">From</label>
            <input type="date" name="from" value="{{ $from }}" class="rounded-lg border-gray-300 text-sm">
        </div>
        <div>
            <label class="mb-1 block text-xs font-medium text-gray-500">To</label>
            <input type="date" name="to" value="{{ $to }}" class="rounded-lg border-gray-300 text-sm">
        </div>
        <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">Apply</button>
        <a href="{{ route('admin.reports.questions') }}" class="rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Reset</a>
    </form>

    <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4" data-testid="reports-questions-summary">
        <div class="rounded-xl bg-white p-5 shadow-sm">
            <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Forms</p>
            <p class="mt-2 text-3xl font-bold text-gray-900">{{ number_format($report['totals']['forms']) }}</p>
        </div>
        <div class="rounded-xl bg-white p-5 shadow-sm">
            <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Questions</p>
            <p class="mt-2 text-3xl font-bold text-indigo-700">{{ number_format($report['totals']['questions']) }}</p>
        </div>
        <div class="rounded-xl bg-white p-5 shadow-sm">
            <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Submitted responses</p>
            <p class="mt-2 text-3xl font-bold text-emerald-700">{{ number_format($report['totals']['responses']) }}</p>
        </div>
        <div class="rounded-xl bg-white p-5 shadow-sm">
            <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Answers</p>
            <p class="mt-2 text-3xl font-bold text-amber-700">{{ number_format($report['totals']['answers']) }}</p>
        </div>
    </div>

    @forelse($report['forms'] as $form)
        <section class="mb-8 rounded-2xl bg-white p-5 shadow-sm" data-testid="reports-questions-form-{{ $form['form_id'] }}">
            <div class="mb-5 flex flex-col gap-1 border-b border-gray-100 pb-4 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">{{ $form['form_name'] }}</h2>
                    <p class="text-sm text-gray-500">{{ $form['audience'] }} form</p>
                </div>
                <p class="text-sm text-gray-600">{{ number_format($form['responses']) }} submitted responses</p>
            </div>

            @if(empty($form['questions']))
                <p class="text-sm text-gray-500">No active questions on this form.</p>
            @else
                <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">
                    @foreach($form['questions'] as $question)
                        <article class="rounded-xl border border-gray-100 p-4" data-testid="reports-question-card-{{ $question['question_id'] }}">
                            <div class="mb-3 flex items-start justify-between gap-3">
                                <div>
                                    <h3 class="font-semibold text-gray-900">{{ $question['label'] }}</h3>
                                    <p class="text-xs text-gray-500">{{ $question['type_label'] }} · {{ number_format($question['answered']) }} answers</p>
                                </div>
                            </div>

                            @if($question['chartable'])
                                @if($question['answered'] === 0)
                                    <p class="py-8 text-center text-sm text-gray-500">No answers yet for this question.</p>
                                @else
                                    <div class="relative mx-auto h-56 max-w-md">
                                        <canvas id="question-chart-{{ $question['question_id'] }}"
                                                aria-label="Chart for {{ $question['label'] }}"
                                                data-testid="reports-question-chart-{{ $question['question_id'] }}"></canvas>
                                    </div>
                                    <ul class="mt-4 space-y-2">
                                        @foreach($question['options'] as $option)
                                            <li class="flex items-center justify-between text-sm">
                                                <span class="text-gray-700">{{ $option['label'] }}</span>
                                                <span class="font-medium text-gray-900">{{ number_format($option['count']) }} <span class="text-xs font-normal text-gray-500">({{ number_format($option['percent'], 1) }}%)</span></span>
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif
                            @else
                                @if(empty($question['text_samples']))
                                    <p class="py-8 text-center text-sm text-gray-500">No answers yet for this question.</p>
                                @else
                                    <ul class="space-y-2" data-testid="reports-question-text-{{ $question['question_id'] }}">
                                        @foreach($question['text_samples'] as $sample)
                                            <li class="rounded-lg bg-slate-50 px-3 py-2 text-sm">
                                                <div class="flex items-start justify-between gap-3">
                                                    <p class="text-gray-800 break-words">{{ \Illuminate\Support\Str::limit($sample['value'], 120) }}</p>
                                                    <span class="shrink-0 text-xs font-semibold text-gray-500">×{{ $sample['count'] }}</span>
                                                </div>
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif
                            @endif
                        </article>
                    @endforeach
                </div>
            @endif
        </section>
    @empty
        <div class="rounded-2xl bg-white px-6 py-16 text-center shadow-sm" data-testid="reports-questions-empty">
            <h2 class="text-lg font-semibold text-gray-800">No custom registration forms</h2>
            <p class="mt-2 text-sm text-gray-500">Create an active registration custom form to see question graphs here.</p>
        </div>
    @endforelse
</div>
@endsection

@section('scripts')
@php
    $questionCharts = [];
    foreach ($report['forms'] as $form) {
        foreach ($form['questions'] as $question) {
            if (! $question['chartable'] || $question['answered'] <= 0) {
                continue;
            }

            $questionCharts[] = [
                'id' => $question['question_id'],
                'type' => $question['type'] === 'checkbox' ? 'bar' : 'doughnut',
                'labels' => array_column($question['options'], 'label'),
                'data' => array_column($question['options'], 'count'),
            ];
        }
    }
@endphp
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
    const reportPalette = @json($chartPalette);
    const questionCharts = @json($questionCharts);

    questionCharts.forEach((chartConfig) => {
        const canvas = document.getElementById(`question-chart-${chartConfig.id}`);
        if (!canvas || typeof Chart === 'undefined') {
            return;
        }

        new Chart(canvas, {
            type: chartConfig.type,
            data: {
                labels: chartConfig.labels,
                datasets: [{
                    label: 'Answers',
                    data: chartConfig.data,
                    backgroundColor: chartConfig.labels.map((_, index) => reportPalette[index % reportPalette.length]),
                    borderWidth: 0,
                    borderRadius: chartConfig.type === 'bar' ? 6 : 0,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: chartConfig.type !== 'bar',
                        position: 'bottom',
                        labels: { boxWidth: 12, padding: 12 },
                    },
                },
                scales: chartConfig.type === 'bar' ? {
                    x: { ticks: { maxRotation: 45, minRotation: 0 } },
                    y: { beginAtZero: true, ticks: { precision: 0 } },
                } : {},
            },
        });
    });
</script>
@endsection
