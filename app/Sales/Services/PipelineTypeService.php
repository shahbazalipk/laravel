<?php

namespace App\Sales\Services;

use App\Sales\Enums\FieldScope;
use App\Sales\Enums\StageCategory;
use App\Sales\Models\PipelineType;
use App\Sales\Models\PipelineTypeField;
use App\Sales\Models\PipelineTypeStage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PipelineTypeService
{
    public function __construct(private SalesActivityLogger $activities)
    {
    }

    public function create(array $data): PipelineType
    {
        return DB::transaction(function () use ($data) {
            $stages = $data['stages'] ?? [];
            $this->assertValidStages($stages);

            $type = PipelineType::query()->create([
                'name' => $data['name'],
                'slug' => $this->availableSlug($data['slug'] ?? $data['name']),
                'description' => $data['description'] ?? null,
                'color' => $data['color'] ?? null,
                'is_active' => (bool) ($data['is_active'] ?? true),
                'created_by' => session('admin_id'),
                'updated_by' => session('admin_id'),
            ]);

            $this->syncStages($type, $stages);
            $this->syncFields($type, FieldScope::Pipeline, $data['pipeline_fields'] ?? []);
            $this->syncFields($type, FieldScope::Deal, $data['deal_fields'] ?? []);

            $this->activities->log($type, 'pipeline_type.created', 'Pipeline type created');

            return $type->fresh(['stages', 'fields']);
        });
    }

    public function update(PipelineType $type, array $data): PipelineType
    {
        return DB::transaction(function () use ($type, $data) {
            $stages = $data['stages'] ?? [];
            $this->assertValidStages($stages);

            $type->update([
                'name' => $data['name'],
                'slug' => $this->availableSlug($data['slug'] ?? $data['name'], $type),
                'description' => $data['description'] ?? null,
                'color' => $data['color'] ?? null,
                'is_active' => (bool) ($data['is_active'] ?? $type->is_active),
                'updated_by' => session('admin_id'),
            ]);

            $this->syncStages($type, $stages);
            $this->syncFields($type, FieldScope::Pipeline, $data['pipeline_fields'] ?? []);
            $this->syncFields($type, FieldScope::Deal, $data['deal_fields'] ?? []);

            $this->activities->log($type, 'pipeline_type.updated', 'Pipeline type updated');

            return $type->fresh(['stages', 'fields']);
        });
    }

    public function duplicate(PipelineType $type): PipelineType
    {
        $type->load(['stages', 'fields', 'conditions']);

        return DB::transaction(function () use ($type) {
            $copy = PipelineType::query()->create([
                'name' => $type->name.' (Copy)',
                'slug' => $this->availableSlug($type->slug.'-copy'),
                'description' => $type->description,
                'color' => $type->color,
                'is_active' => false,
                'created_by' => session('admin_id'),
                'updated_by' => session('admin_id'),
            ]);

            $stageMap = [];
            foreach ($type->stages as $stage) {
                $new = $copy->stages()->create([
                    'name' => $stage->name,
                    'slug' => $stage->slug,
                    'description' => $stage->description,
                    'color' => $stage->color,
                    'sort_order' => $stage->sort_order,
                    'probability' => $stage->probability,
                    'category' => $stage->category,
                    'is_default' => $stage->is_default,
                    'is_active' => $stage->is_active,
                    'sla_hours' => $stage->sla_hours,
                    'instructions' => $stage->instructions,
                    'required_field_keys' => $stage->required_field_keys,
                ]);
                $stageMap[$stage->id] = $new->id;
            }

            $fieldMap = [];
            foreach ($type->fields as $field) {
                $new = $copy->fields()->create([
                    'scope' => $field->scope,
                    'key' => $field->key,
                    'label' => $field->label,
                    'type' => $field->type,
                    'help_text' => $field->help_text,
                    'placeholder' => $field->placeholder,
                    'default_value' => $field->default_value,
                    'is_required' => $field->is_required,
                    'is_active' => $field->is_active,
                    'sort_order' => $field->sort_order,
                    'validation' => $field->validation,
                    'options' => $field->options,
                    'settings' => $field->settings,
                ]);
                $fieldMap[$field->id] = $new->id;
            }

            foreach ($type->conditions as $condition) {
                if (! isset($fieldMap[$condition->source_field_id], $fieldMap[$condition->target_field_id])) {
                    continue;
                }
                $copy->conditions()->create([
                    'source_field_id' => $fieldMap[$condition->source_field_id],
                    'target_field_id' => $fieldMap[$condition->target_field_id],
                    'operator' => $condition->operator,
                    'compare_value' => $condition->compare_value,
                    'action' => $condition->action,
                    'sort_order' => $condition->sort_order,
                    'is_active' => $condition->is_active,
                ]);
            }

            $this->activities->log($copy, 'pipeline_type.duplicated', 'Pipeline type duplicated from '.$type->name);

            return $copy->fresh(['stages', 'fields']);
        });
    }

    public function toggleActive(PipelineType $type): PipelineType
    {
        $type->update([
            'is_active' => ! $type->is_active,
            'updated_by' => session('admin_id'),
        ]);
        $this->activities->log($type, 'pipeline_type.toggled', $type->is_active ? 'Activated' : 'Deactivated');

        return $type;
    }

    public function delete(PipelineType $type): void
    {
        if ($type->pipelines()->exists()) {
            throw ValidationException::withMessages([
                'pipeline_type' => 'This pipeline type is used by existing pipelines. Deactivate it instead of deleting.',
            ]);
        }

        $type->delete();
        $this->activities->log($type, 'pipeline_type.deleted', 'Pipeline type deleted');
    }

    /**
     * @param  list<array<string, mixed>>  $stages
     */
    public function assertValidStages(array $stages): void
    {
        if ($stages === []) {
            throw ValidationException::withMessages([
                'stages' => 'At least one stage is required.',
            ]);
        }

        $defaultCount = collect($stages)->filter(
            fn ($s) => filter_var($s['is_default'] ?? false, FILTER_VALIDATE_BOOLEAN)
        )->count();

        if ($defaultCount !== 1) {
            throw ValidationException::withMessages([
                'stages' => 'Exactly one default stage is required.',
            ]);
        }

        $categories = collect($stages)->map(fn ($s) => $s['category'] ?? StageCategory::Open->value);
        if (! $categories->contains(StageCategory::Won->value)) {
            throw ValidationException::withMessages([
                'stages' => 'At least one Won stage is required.',
            ]);
        }
        if (! $categories->contains(StageCategory::Lost->value)) {
            throw ValidationException::withMessages([
                'stages' => 'At least one Lost stage is required.',
            ]);
        }
    }

    /**
     * @param  list<array<string, mixed>>  $stages
     */
    private function syncStages(PipelineType $type, array $stages): void
    {
        $keepIds = [];
        foreach (array_values($stages) as $index => $stageData) {
            $attrs = [
                'name' => $stageData['name'],
                'slug' => Str::slug($stageData['slug'] ?? $stageData['name']) ?: 'stage-'.$index,
                'description' => $stageData['description'] ?? null,
                'color' => $stageData['color'] ?? null,
                'sort_order' => $index,
                'probability' => (int) ($stageData['probability'] ?? 0),
                'category' => $stageData['category'] ?? StageCategory::Open->value,
                'is_default' => filter_var($stageData['is_default'] ?? false, FILTER_VALIDATE_BOOLEAN),
                'is_active' => filter_var($stageData['is_active'] ?? true, FILTER_VALIDATE_BOOLEAN),
                'sla_hours' => $stageData['sla_hours'] ?? null,
                'instructions' => $stageData['instructions'] ?? null,
                'required_field_keys' => $stageData['required_field_keys'] ?? null,
            ];

            if (! empty($stageData['public_id'])) {
                $existing = $type->stages()->where('public_id', $stageData['public_id'])->first();
                if ($existing) {
                    // Block delete check is separate; update is fine
                    if ($existing->pipelineStages()->exists() === false || true) {
                        $existing->update($attrs);
                        $keepIds[] = $existing->id;
                        continue;
                    }
                }
            }

            $created = $type->stages()->create($attrs);
            $keepIds[] = $created->id;
        }

        $type->stages()->whereNotIn('id', $keepIds)->get()->each(function (PipelineTypeStage $stage) {
            if ($stage->pipelineStages()->exists()) {
                throw ValidationException::withMessages([
                    'stages' => "Stage \"{$stage->name}\" is used by pipelines and cannot be deleted.",
                ]);
            }
            $stage->delete();
        });
    }

    /**
     * @param  list<array<string, mixed>>  $fields
     */
    private function syncFields(PipelineType $type, FieldScope $scope, array $fields): void
    {
        $keepIds = [];
        foreach (array_values($fields) as $index => $fieldData) {
            $key = $fieldData['key'] ?? Str::slug($fieldData['label'] ?? 'field', '_');
            $attrs = [
                'scope' => $scope,
                'key' => $key,
                'label' => $fieldData['label'],
                'type' => $fieldData['type'],
                'help_text' => $fieldData['help_text'] ?? null,
                'placeholder' => $fieldData['placeholder'] ?? null,
                'default_value' => $fieldData['default_value'] ?? null,
                'is_required' => filter_var($fieldData['is_required'] ?? false, FILTER_VALIDATE_BOOLEAN),
                'is_active' => filter_var($fieldData['is_active'] ?? true, FILTER_VALIDATE_BOOLEAN),
                'sort_order' => $index,
                'validation' => $fieldData['validation'] ?? null,
                'options' => $fieldData['options'] ?? null,
                'settings' => $fieldData['settings'] ?? null,
            ];

            if (! empty($fieldData['public_id'])) {
                $existing = $type->fields()->where('public_id', $fieldData['public_id'])->first();
                if ($existing) {
                    $existing->update($attrs);
                    $keepIds[] = $existing->id;
                    continue;
                }
            }

            $created = $type->fields()->create($attrs);
            $keepIds[] = $created->id;
        }

        $type->fields()
            ->where('scope', $scope->value)
            ->whereNotIn('id', $keepIds)
            ->get()
            ->each(function (PipelineTypeField $field) {
                $field->targetConditions()->delete();
                $field->sourceConditions()->delete();
                $field->delete();
            });
    }

    private function availableSlug(string $value, ?PipelineType $except = null): string
    {
        $base = Str::slug($value) ?: 'pipeline-type';
        $slug = $base;
        $suffix = 2;
        while (PipelineType::withTrashed()
            ->when($except, fn ($q) => $q->whereKeyNot($except->getKey()))
            ->where('slug', $slug)
            ->exists()) {
            $slug = $base.'-'.$suffix++;
        }

        return $slug;
    }
}
