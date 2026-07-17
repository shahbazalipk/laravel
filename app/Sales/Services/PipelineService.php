<?php

namespace App\Sales\Services;

use App\Sales\Enums\PipelineStatus;
use App\Sales\Models\Pipeline;
use App\Sales\Models\PipelineType;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PipelineService
{
    public function __construct(private SalesActivityLogger $activities)
    {
    }

    public function list(array $filters = []): LengthAwarePaginator
    {
        $query = Pipeline::query()
            ->with('type')
            ->withCount(['deals', 'stages'])
            ->orderByDesc('updated_at');

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['type_id'])) {
            $query->where('sales_pipeline_type_id', $filters['type_id']);
        }

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        return $query->paginate($filters['per_page'] ?? 15)->withQueryString();
    }

    public function createFromType(PipelineType $type, array $data): Pipeline
    {
        $type->load(['stages', 'fields']);

        return DB::transaction(function () use ($type, $data) {
            $pipeline = Pipeline::query()->create([
                'sales_pipeline_type_id' => $type->id,
                'name' => $data['name'],
                'slug' => $this->availableSlug($data['slug'] ?? $data['name']),
                'description' => $data['description'] ?? null,
                'status' => $data['status'] ?? PipelineStatus::Active->value,
                'start_date' => $data['start_date'] ?? null,
                'end_date' => $data['end_date'] ?? null,
                'currency' => $data['currency'] ?? current_event_currency(),
                'revenue_target' => $data['revenue_target'] ?? null,
                'owner_admin_id' => $data['owner_admin_id'] ?? session('admin_id'),
                'custom_field_answers' => $data['custom_field_answers'] ?? [],
                'team_admin_ids' => $data['team_admin_ids'] ?? null,
                'settings' => $data['settings'] ?? null,
                'created_by' => session('admin_id'),
                'updated_by' => session('admin_id'),
                'last_activity_at' => now(),
            ]);

            foreach ($type->stages as $typeStage) {
                $pipeline->stages()->create([
                    'source_type_stage_id' => $typeStage->id,
                    'name' => $typeStage->name,
                    'slug' => $typeStage->slug,
                    'description' => $typeStage->description,
                    'color' => $typeStage->color,
                    'sort_order' => $typeStage->sort_order,
                    'probability' => $typeStage->probability,
                    'category' => $typeStage->category,
                    'is_default' => $typeStage->is_default,
                    'is_active' => $typeStage->is_active,
                    'sla_hours' => $typeStage->sla_hours,
                    'instructions' => $typeStage->instructions,
                    'required_field_keys' => $typeStage->required_field_keys,
                ]);
            }

            $this->activities->log($pipeline, 'pipeline.created', 'Pipeline created from '.$type->name);

            return $pipeline->fresh(['type', 'stages']);
        });
    }

    public function update(Pipeline $pipeline, array $data): Pipeline
    {
        $pipeline->update([
            'name' => $data['name'],
            'slug' => $this->availableSlug($data['slug'] ?? $data['name'], $pipeline),
            'description' => $data['description'] ?? null,
            'status' => $data['status'] ?? $pipeline->status->value,
            'start_date' => $data['start_date'] ?? null,
            'end_date' => $data['end_date'] ?? null,
            'currency' => $data['currency'] ?? $pipeline->currency,
            'revenue_target' => $data['revenue_target'] ?? null,
            'owner_admin_id' => $data['owner_admin_id'] ?? $pipeline->owner_admin_id,
            'custom_field_answers' => $data['custom_field_answers'] ?? $pipeline->custom_field_answers,
            'team_admin_ids' => $data['team_admin_ids'] ?? $pipeline->team_admin_ids,
            'settings' => $data['settings'] ?? $pipeline->settings,
            'updated_by' => session('admin_id'),
            'last_activity_at' => now(),
        ]);

        $this->activities->log($pipeline, 'pipeline.updated', 'Pipeline updated');

        return $pipeline->fresh(['type', 'stages']);
    }

    public function archive(Pipeline $pipeline): Pipeline
    {
        $pipeline->update([
            'status' => PipelineStatus::Archived,
            'updated_by' => session('admin_id'),
        ]);

        $this->activities->log($pipeline, 'pipeline.archived', 'Pipeline archived');

        return $pipeline;
    }

    public function delete(Pipeline $pipeline): void
    {
        if ($pipeline->deals()->exists()) {
            throw ValidationException::withMessages([
                'pipeline' => 'This pipeline has deals and cannot be deleted. Archive it instead.',
            ]);
        }

        $pipeline->delete();
        $this->activities->log($pipeline, 'pipeline.deleted', 'Pipeline deleted');
    }

    public function activeTypes(): Collection
    {
        return PipelineType::query()
            ->active()
            ->withCount('pipelines')
            ->orderBy('name')
            ->get();
    }

    private function availableSlug(string $value, ?Pipeline $except = null): string
    {
        $base = Str::slug($value) ?: 'pipeline';
        $slug = $base;
        $suffix = 2;

        while (Pipeline::withTrashed()
            ->when($except, fn ($q) => $q->whereKeyNot($except->getKey()))
            ->where('slug', $slug)
            ->exists()) {
            $slug = $base.'-'.$suffix++;
        }

        return $slug;
    }
}
