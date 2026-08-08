<?php

namespace App\Services;

use App\Forms\Enums\FormAudience;
use App\Forms\Enums\FormQuestionType;
use App\Forms\Enums\FormResponseStatus;
use App\Forms\Models\CustomForm;
use App\Forms\Models\CustomFormAnswer;
use App\Forms\Models\CustomFormQuestion;
use App\Forms\Models\CustomFormResponse;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class CustomFormReportService
{
    public function __construct(
        private RegistrationReportService $registrationReports,
    ) {}

    /**
     * @return array{
     *   from: string,
     *   to: string,
     *   totals: array{forms: int, questions: int, responses: int, answers: int},
     *   forms: list<array{
     *     form_id: int,
     *     form_name: string,
     *     audience: string,
     *     responses: int,
     *     questions: list<array{
     *       question_id: int,
     *       label: string,
     *       type: string,
     *       type_label: string,
     *       chartable: bool,
     *       answered: int,
     *       options: list<array{value: string, label: string, count: int, percent: float}>,
     *       text_samples: list<array{value: string, count: int}>
     *     }>
     *   }>
     * }
     */
    public function questionsReport(?CarbonInterface $from = null, ?CarbonInterface $to = null): array
    {
        [$from, $to] = $this->registrationReports->normalizeRange($from, $to);

        if (! Schema::hasTable('custom_forms') || ! Schema::hasTable('custom_form_answers')) {
            return $this->emptyReport($from, $to);
        }

        $forms = CustomForm::query()
            ->active()
            ->where('audience', FormAudience::Registration->value)
            ->with([
                'questions' => fn ($query) => $query->active()->orderBy('sort_order')->with([
                    'options' => fn ($options) => $options->active()->orderBy('sort_order'),
                ]),
            ])
            ->orderBy('name')
            ->get();

        $responseQuery = CustomFormResponse::query()
            ->where('status', FormResponseStatus::Submitted->value)
            ->whereIn('custom_form_id', $forms->pluck('id')->all());

        if (Schema::hasColumn('custom_form_responses', 'submitted_at')) {
            $responseQuery->where(function ($query) use ($from, $to): void {
                $query->whereBetween('submitted_at', [$from, $to])
                    ->orWhere(function ($fallback) use ($from, $to): void {
                        $fallback->whereNull('submitted_at')
                            ->whereBetween('created_at', [$from, $to]);
                    });
            });
        } else {
            $responseQuery->whereBetween('created_at', [$from, $to]);
        }

        if (Schema::hasColumn('custom_form_responses', 'deleted_at')) {
            $responseQuery->whereNull('deleted_at');
        }

        $responses = $responseQuery->get(['id', 'custom_form_id']);
        $responsesByForm = $responses->groupBy('custom_form_id');
        $responseIds = $responses->pluck('id')->all();

        $answers = empty($responseIds)
            ? collect()
            : CustomFormAnswer::query()
                ->whereIn('custom_form_response_id', $responseIds)
                ->get();

        $answersByQuestion = $answers->groupBy('custom_form_question_id');

        $formRows = [];
        foreach ($forms as $form) {
            $formResponses = $responsesByForm->get($form->id, collect());
            $questionRows = [];

            foreach ($form->questions as $question) {
                $questionAnswers = $answersByQuestion->get($question->id, collect());
                $questionRows[] = $this->buildQuestionRow($question, $questionAnswers);
            }

            $formRows[] = [
                'form_id' => (int) $form->id,
                'form_name' => (string) $form->name,
                'audience' => $form->audience instanceof FormAudience
                    ? $form->audience->label()
                    : (string) $form->audience,
                'responses' => $formResponses->count(),
                'questions' => $questionRows,
            ];
        }

        return [
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
            'totals' => [
                'forms' => count($formRows),
                'questions' => array_sum(array_map(fn (array $form) => count($form['questions']), $formRows)),
                'responses' => $responses->count(),
                'answers' => $answers->count(),
            ],
            'forms' => $formRows,
        ];
    }

    /**
     * @param  array{from: string, to: string, forms: list<array<string, mixed>>}  $report
     */
    public function questionsReportCsv(array $report): string
    {
        $lines = [];
        $lines[] = $this->csvLine([
            'Form',
            'Question',
            'Type',
            'Answer',
            'Count',
            'Percent',
        ]);

        foreach ($report['forms'] as $form) {
            foreach ($form['questions'] as $question) {
                if ($question['chartable'] && ! empty($question['options'])) {
                    foreach ($question['options'] as $option) {
                        $lines[] = $this->csvLine([
                            $form['form_name'],
                            $question['label'],
                            $question['type_label'],
                            $option['label'],
                            $option['count'],
                            number_format($option['percent'], 1, '.', '').'%',
                        ]);
                    }
                } elseif (! empty($question['text_samples'])) {
                    foreach ($question['text_samples'] as $sample) {
                        $lines[] = $this->csvLine([
                            $form['form_name'],
                            $question['label'],
                            $question['type_label'],
                            $sample['value'],
                            $sample['count'],
                            '',
                        ]);
                    }
                } else {
                    $lines[] = $this->csvLine([
                        $form['form_name'],
                        $question['label'],
                        $question['type_label'],
                        '(no answers)',
                        0,
                        '',
                    ]);
                }
            }
        }

        return implode("\n", $lines)."\n";
    }

    /**
     * @param  Collection<int, CustomFormAnswer>  $answers
     * @return array{
     *   question_id: int,
     *   label: string,
     *   type: string,
     *   type_label: string,
     *   chartable: bool,
     *   answered: int,
     *   options: list<array{value: string, label: string, count: int, percent: float}>,
     *   text_samples: list<array{value: string, count: int}>
     * }
     */
    private function buildQuestionRow(CustomFormQuestion $question, Collection $answers): array
    {
        $type = $question->type instanceof FormQuestionType
            ? $question->type
            : FormQuestionType::tryFrom((string) $question->type);

        $chartable = $type?->hasOptions() ?? false;
        $answered = $answers->count();

        $optionCounts = [];
        $textCounts = [];

        foreach ($answers as $answer) {
            $extracted = $this->extractAnswerValues($answer, $type);
            foreach ($extracted as $value) {
                if ($chartable) {
                    $optionCounts[$value] = ($optionCounts[$value] ?? 0) + 1;
                } else {
                    $normalized = trim($value);
                    if ($normalized === '') {
                        continue;
                    }
                    $textCounts[$normalized] = ($textCounts[$normalized] ?? 0) + 1;
                }
            }
        }

        $options = [];
        if ($chartable) {
            $known = $question->options
                ->mapWithKeys(fn ($option) => [(string) $option->value => (string) $option->label])
                ->all();

            foreach ($known as $value => $label) {
                $count = (int) ($optionCounts[$value] ?? 0);
                $options[] = [
                    'value' => $value,
                    'label' => $label,
                    'count' => $count,
                    'percent' => $answered > 0 ? round(($count / $answered) * 100, 1) : 0.0,
                ];
                unset($optionCounts[$value]);
            }

            foreach ($optionCounts as $value => $count) {
                $options[] = [
                    'value' => (string) $value,
                    'label' => (string) $value,
                    'count' => (int) $count,
                    'percent' => $answered > 0 ? round(($count / $answered) * 100, 1) : 0.0,
                ];
            }

            usort($options, fn (array $a, array $b) => $b['count'] <=> $a['count']);
        }

        arsort($textCounts);
        $textSamples = [];
        foreach (array_slice($textCounts, 0, 8, true) as $value => $count) {
            $textSamples[] = [
                'value' => (string) $value,
                'count' => (int) $count,
            ];
        }

        return [
            'question_id' => (int) $question->id,
            'label' => (string) $question->label,
            'type' => $type?->value ?? (string) $question->type,
            'type_label' => $type?->label() ?? ucfirst((string) $question->type),
            'chartable' => $chartable,
            'answered' => $answered,
            'options' => $options,
            'text_samples' => $textSamples,
        ];
    }

    /**
     * @return list<string>
     */
    private function extractAnswerValues(CustomFormAnswer $answer, ?FormQuestionType $type): array
    {
        $value = $answer->value;
        if (! is_array($value)) {
            return $value === null || $value === '' ? [] : [(string) $value];
        }

        if ($type === FormQuestionType::Checkbox || array_key_exists('values', $value)) {
            $values = $value['values'] ?? [];

            return array_values(array_map(fn ($item) => (string) $item, is_array($values) ? $values : [$values]));
        }

        if (array_key_exists('value', $value)) {
            $single = $value['value'];
            if (is_array($single)) {
                return array_values(array_map(fn ($item) => (string) $item, $single));
            }

            return $single === null || $single === '' ? [] : [(string) $single];
        }

        return [];
    }

    /**
     * @return array{from: string, to: string, totals: array{forms: int, questions: int, responses: int, answers: int}, forms: list<never>}
     */
    private function emptyReport(CarbonInterface $from, CarbonInterface $to): array
    {
        return [
            'from' => Carbon::parse($from)->toDateString(),
            'to' => Carbon::parse($to)->toDateString(),
            'totals' => [
                'forms' => 0,
                'questions' => 0,
                'responses' => 0,
                'answers' => 0,
            ],
            'forms' => [],
        ];
    }

    /**
     * @param  list<mixed>  $values
     */
    private function csvLine(array $values): string
    {
        return collect($values)
            ->map(function ($value) {
                $string = (string) $value;
                if (str_contains($string, ',') || str_contains($string, '"') || str_contains($string, "\n")) {
                    return '"'.str_replace('"', '""', $string).'"';
                }

                return $string;
            })
            ->implode(',');
    }
}
