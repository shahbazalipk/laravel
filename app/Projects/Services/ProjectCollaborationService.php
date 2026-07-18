<?php

namespace App\Projects\Services;

use App\Projects\Models\ProjectTask;
use App\Projects\Models\ProjectTaskChecklistItem;
use App\Projects\Models\ProjectTaskComment;
use App\Shared\Audit\AuditLogger;
use Illuminate\Support\Facades\DB;

class ProjectCollaborationService
{
    public function __construct(private readonly AuditLogger $audit) {}

    public function addComment(ProjectTask $task, array $data): ProjectTaskComment
    {
        $comment = ProjectTaskComment::query()->create([
            'task_id' => $task->id,
            'author_admin_id' => session('admin_id'),
            'body' => $data['body'],
            'is_internal' => (bool) ($data['is_internal'] ?? false),
            'is_pinned' => false,
        ]);

        $this->audit->record('projects', 'task.comment-added', $task, after: [
            'comment_public_id' => $comment->public_id,
            'is_internal' => $comment->is_internal,
        ]);

        return $comment;
    }

    public function addChecklistItem(ProjectTask $task, array $data): ProjectTaskChecklistItem
    {
        $item = ProjectTaskChecklistItem::query()->create([
            ...$data,
            'task_id' => $task->id,
            'is_required' => (bool) ($data['is_required'] ?? false),
            'is_completed' => false,
            'sort_order' => ((int) $task->checklistItems()->max('sort_order')) + 10,
        ]);

        $this->audit->record('projects', 'task.checklist-item-added', $task, after: [
            'checklist_public_id' => $item->public_id,
            'text' => $item->text,
        ]);

        return $item;
    }

    public function toggleChecklistItem(
        ProjectTask $task,
        ProjectTaskChecklistItem $item,
        bool $completed,
    ): ProjectTaskChecklistItem {
        abort_unless((int) $item->task_id === (int) $task->id, 404);

        return DB::transaction(function () use ($task, $item, $completed): ProjectTaskChecklistItem {
            $item->forceFill([
                'is_completed' => $completed,
                'completed_by' => $completed ? session('admin_id') : null,
                'completed_at' => $completed ? now() : null,
            ])->save();

            $total = $task->checklistItems()->count();
            $completedCount = $task->checklistItems()->where('is_completed', true)->count();
            $progress = $total > 0 ? (int) round(($completedCount / $total) * 100) : 0;

            if ($task->status !== 'done') {
                $task->forceFill([
                    'progress_percent' => $progress,
                    'updated_by' => session('admin_id'),
                    'version' => $task->version + 1,
                ])->save();
            }

            $this->audit->record('projects', 'task.checklist-item-toggled', $task, after: [
                'checklist_public_id' => $item->public_id,
                'is_completed' => $completed,
                'progress_percent' => $task->progress_percent,
            ]);

            return $item->fresh();
        });
    }
}
