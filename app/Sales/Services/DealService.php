<?php

namespace App\Sales\Services;

use App\Sales\Enums\DealStatus;
use App\Sales\Enums\StageCategory;
use App\Sales\Models\Deal;
use App\Sales\Models\DealStageHistory;
use App\Sales\Models\Pipeline;
use App\Sales\Models\PipelineStage;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DealService
{
    public function __construct(private SalesActivityLogger $activities)
    {
    }

    public function list(array $filters = []): LengthAwarePaginator
    {
        $query = Deal::query()
            ->with(['pipeline', 'stage'])
            ->orderByDesc('updated_at');

        if (! empty($filters['pipeline_id'])) {
            $query->where('sales_pipeline_id', $filters['pipeline_id']);
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['stage_id'])) {
            $query->where('sales_pipeline_stage_id', $filters['stage_id']);
        }

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('reference', 'like', "%{$search}%");
            });
        }

        return $query->paginate($filters['per_page'] ?? 20)->withQueryString();
    }

    public function create(Pipeline $pipeline, array $data): Deal
    {
        $pipeline->load('stages');

        $stage = $this->resolveStage($pipeline, $data['sales_pipeline_stage_id'] ?? null);

        return DB::transaction(function () use ($pipeline, $data, $stage) {
            $deal = Deal::query()->create([
                'sales_pipeline_id' => $pipeline->id,
                'sales_pipeline_stage_id' => $stage->id,
                'title' => $data['title'],
                'reference' => $this->nextReference(),
                'value' => $data['value'] ?? null,
                'currency' => $data['currency'] ?? $pipeline->currency ?? current_event_currency(),
                'probability' => $data['probability'] ?? $stage->probability,
                'expected_close_date' => $data['expected_close_date'] ?? null,
                'owner_admin_id' => $data['owner_admin_id'] ?? session('admin_id'),
                'assigned_admin_ids' => $data['assigned_admin_ids'] ?? null,
                'lead_source' => $data['lead_source'] ?? null,
                'status' => $this->statusForStage($stage),
                'priority' => $data['priority'] ?? null,
                'description' => $data['description'] ?? null,
                'tags' => $data['tags'] ?? null,
                'custom_field_answers' => $data['custom_field_answers'] ?? [],
                'created_by' => session('admin_id'),
                'updated_by' => session('admin_id'),
                'last_activity_at' => now(),
            ]);

            if (! empty($data['contact'])) {
                $deal->contacts()->create([
                    'name' => $data['contact']['name'],
                    'email' => $data['contact']['email'] ?? null,
                    'phone' => $data['contact']['phone'] ?? null,
                    'job_title' => $data['contact']['job_title'] ?? null,
                    'company_name' => $data['contact']['company_name'] ?? null,
                    'is_primary' => true,
                ]);
            }

            DealStageHistory::query()->create([
                'sales_deal_id' => $deal->id,
                'from_stage_id' => null,
                'to_stage_id' => $stage->id,
                'moved_by' => session('admin_id'),
                'note' => 'Deal created',
            ]);

            $this->activities->log($deal, 'deal.created', 'Deal created: '.$deal->title);

            return $deal->fresh(['pipeline', 'stage', 'contacts']);
        });
    }

    public function update(Deal $deal, array $data): Deal
    {
        $deal->update([
            'title' => $data['title'] ?? $deal->title,
            'value' => array_key_exists('value', $data) ? $data['value'] : $deal->value,
            'currency' => $data['currency'] ?? $deal->currency,
            'probability' => $data['probability'] ?? $deal->probability,
            'expected_close_date' => $data['expected_close_date'] ?? $deal->expected_close_date,
            'owner_admin_id' => $data['owner_admin_id'] ?? $deal->owner_admin_id,
            'assigned_admin_ids' => $data['assigned_admin_ids'] ?? $deal->assigned_admin_ids,
            'lead_source' => $data['lead_source'] ?? $deal->lead_source,
            'priority' => $data['priority'] ?? $deal->priority,
            'description' => $data['description'] ?? $deal->description,
            'tags' => $data['tags'] ?? $deal->tags,
            'custom_field_answers' => $data['custom_field_answers'] ?? $deal->custom_field_answers,
            'updated_by' => session('admin_id'),
            'last_activity_at' => now(),
        ]);

        $this->activities->log($deal, 'deal.updated', 'Deal updated');

        return $deal->fresh(['pipeline', 'stage', 'contacts']);
    }

    public function moveStage(Deal $deal, PipelineStage $stage, ?string $note = null): Deal
    {
        if ($stage->sales_pipeline_id !== $deal->sales_pipeline_id) {
            throw ValidationException::withMessages([
                'stage' => 'The selected stage does not belong to this pipeline.',
            ]);
        }

        $this->assertRequiredFields($deal, $stage);

        $fromStageId = $deal->sales_pipeline_stage_id;

        return DB::transaction(function () use ($deal, $stage, $fromStageId, $note) {
            $updates = [
                'sales_pipeline_stage_id' => $stage->id,
                'probability' => $stage->probability,
                'status' => $this->statusForStage($stage),
                'updated_by' => session('admin_id'),
                'last_activity_at' => now(),
            ];

            if ($stage->category === StageCategory::Won) {
                $updates['actual_close_date'] = now()->toDateString();
            }

            if ($stage->category === StageCategory::Lost) {
                $updates['actual_close_date'] = now()->toDateString();
            }

            $deal->update($updates);

            DealStageHistory::query()->create([
                'sales_deal_id' => $deal->id,
                'from_stage_id' => $fromStageId,
                'to_stage_id' => $stage->id,
                'moved_by' => session('admin_id'),
                'note' => $note,
            ]);

            $this->activities->log($deal, 'deal.stage_moved', 'Moved to '.$stage->name, [
                'from_stage_id' => $fromStageId,
                'to_stage_id' => $stage->id,
            ]);

            return $deal->fresh(['pipeline', 'stage']);
        });
    }

    public function delete(Deal $deal): void
    {
        $deal->delete();
        $this->activities->log($deal, 'deal.deleted', 'Deal deleted');
    }

    public function nextReference(): string
    {
        $year = now()->year;
        $prefix = "DL-{$year}-";

        $latest = Deal::withTrashed()
            ->where('reference', 'like', $prefix.'%')
            ->orderByDesc('reference')
            ->value('reference');

        $next = 1;
        if ($latest && preg_match('/-(\d+)$/', $latest, $matches)) {
            $next = ((int) $matches[1]) + 1;
        }

        return $prefix.str_pad((string) $next, 5, '0', STR_PAD_LEFT);
    }

    private function resolveStage(Pipeline $pipeline, mixed $stageId): PipelineStage
    {
        if ($stageId) {
            $stage = $pipeline->stages()->whereKey($stageId)->first();
            if ($stage) {
                return $stage;
            }
        }

        $default = $pipeline->stages()->where('is_default', true)->first()
            ?? $pipeline->stages()->orderBy('sort_order')->first();

        if (! $default) {
            throw ValidationException::withMessages([
                'stage' => 'This pipeline has no stages configured.',
            ]);
        }

        return $default;
    }

    private function statusForStage(PipelineStage $stage): DealStatus
    {
        return match ($stage->category) {
            StageCategory::Won => DealStatus::Won,
            StageCategory::Lost => DealStatus::Lost,
            default => DealStatus::Open,
        };
    }

    private function assertRequiredFields(Deal $deal, PipelineStage $stage): void
    {
        $required = $stage->required_field_keys ?? [];
        if ($required === []) {
            return;
        }

        $answers = $deal->custom_field_answers ?? [];
        $missing = [];

        foreach ($required as $key) {
            $value = $answers[$key] ?? null;
            if ($value === null || $value === '' || $value === []) {
                $missing[] = $key;
            }
        }

        if ($missing !== []) {
            throw ValidationException::withMessages([
                'custom_field_answers' => 'Required deal fields before moving to '.$stage->name.': '.implode(', ', $missing),
            ]);
        }
    }
}
