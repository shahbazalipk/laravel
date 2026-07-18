<?php

namespace App\Projects\Services;

use App\Projects\Models\ProjectRecurrenceRule;
use App\Projects\Models\ProjectTask;
use App\Shared\Audit\AuditLogger;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ProjectRecurrenceService
{
    public function __construct(
        private readonly ProjectTaskService $tasks,
        private readonly ProjectCollaborationService $collaboration,
        private readonly AuditLogger $audit,
    ) {}

    public function create(ProjectTask $task, array $data): ProjectRecurrenceRule
    {
        $startsOn = CarbonImmutable::parse($data['starts_on']);
        $rule = ProjectRecurrenceRule::query()->create([
            'source_task_id' => $task->id,
            'frequency' => $data['frequency'],
            'interval' => (int) ($data['interval'] ?? 1),
            'weekdays' => $data['weekdays'] ?? null,
            'starts_on' => $startsOn,
            'ends_on' => $data['ends_on'] ?? null,
            'max_occurrences' => $data['max_occurrences'] ?? null,
            'next_occurrence_on' => $startsOn,
            'created_by' => session('admin_id'),
            'is_active' => true,
        ]);

        $this->audit->record('projects', 'task.recurrence-created', $task, after: [
            'rule' => $rule->public_id,
            'frequency' => $rule->frequency,
            'interval' => $rule->interval,
        ]);

        return $rule;
    }

    /** @return list<ProjectTask> */
    public function generateDue(ProjectRecurrenceRule $rule, ?CarbonImmutable $through = null): array
    {
        $through ??= CarbonImmutable::today();
        $generated = [];

        return DB::transaction(function () use ($rule, $through, &$generated): array {
            $locked = ProjectRecurrenceRule::query()->lockForUpdate()->findOrFail($rule->id);
            $source = $locked->sourceTask()->with(['project', 'board', 'column', 'checklistItems'])->firstOrFail();

            while ($locked->is_active && $locked->next_occurrence_on->lte($through) && count($generated) < 50) {
                if ($locked->ends_on && $locked->next_occurrence_on->gt($locked->ends_on)) {
                    $locked->is_active = false;
                    break;
                }
                if ($locked->max_occurrences !== null
                    && $locked->generated_occurrences >= $locked->max_occurrences) {
                    $locked->is_active = false;
                    break;
                }

                $occurrence = CarbonImmutable::parse($locked->next_occurrence_on);
                $duration = $source->start_date && $source->due_date
                    ? $source->start_date->diffInDays($source->due_date)
                    : 0;
                $task = $this->tasks->create($source->project, [
                    'title' => $source->title,
                    'description' => $source->description,
                    'type' => $source->type,
                    'priority' => $source->priority,
                    'start_date' => $occurrence->toDateString(),
                    'due_date' => $occurrence->addDays($duration)->toDateString(),
                    'estimated_minutes' => $source->estimated_minutes,
                    'labels' => implode(',', $source->labels ?? []),
                    'location' => $source->location,
                    'board_id' => $source->board->public_id,
                    'column_id' => $source->column->public_id,
                ]);

                foreach ($source->checklistItems as $item) {
                    $this->collaboration->addChecklistItem($task, [
                        'text' => $item->text,
                        'is_required' => $item->is_required,
                    ]);
                }

                $generated[] = $task;
                $locked->generated_occurrences++;
                $locked->last_generated_on = $occurrence;
                $locked->next_occurrence_on = $this->nextDate($locked, $occurrence);
            }

            $locked->save();

            return $generated;
        });
    }

    private function nextDate(ProjectRecurrenceRule $rule, CarbonImmutable $current): CarbonImmutable
    {
        return match ($rule->frequency) {
            'daily' => $current->addDays($rule->interval),
            'weekly' => $current->addWeeks($rule->interval),
            'monthly' => $current->addMonthsNoOverflow($rule->interval),
            'weekdays' => $this->nextSelectedWeekday($rule, $current),
            default => throw ValidationException::withMessages(['frequency' => 'Unsupported recurrence frequency.']),
        };
    }

    private function nextSelectedWeekday(ProjectRecurrenceRule $rule, CarbonImmutable $current): CarbonImmutable
    {
        $weekdays = array_map('intval', $rule->weekdays ?? []);
        if ($weekdays === []) {
            throw ValidationException::withMessages(['weekdays' => 'Select at least one weekday.']);
        }

        $candidate = $current;
        for ($day = 0; $day < 14 * $rule->interval; $day++) {
            $candidate = $candidate->addDay();
            if (in_array($candidate->dayOfWeekIso, $weekdays, true)) {
                return $candidate;
            }
        }

        throw new \LogicException('Unable to calculate the next recurring date.');
    }
}
