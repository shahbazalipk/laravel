<?php

namespace App\Sales\Services;

use App\Sales\Enums\InquiryFormStatus;
use App\Sales\Enums\SalesFieldType;
use App\Sales\Models\InquiryForm;
use App\Sales\Models\InquiryFormField;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class InquiryFormService
{
    public function __construct(private SalesActivityLogger $activities)
    {
    }

    public function list(array $filters = []): LengthAwarePaginator
    {
        $query = InquiryForm::query()
            ->with('pipelineType')
            ->withCount('submissions')
            ->orderByDesc('updated_at');

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
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

    public function create(array $data): InquiryForm
    {
        return DB::transaction(function () use ($data) {
            $form = InquiryForm::query()->create([
                'name' => $data['name'],
                'slug' => $this->availableSlug($data['slug'] ?? $data['name']),
                'description' => $data['description'] ?? null,
                'status' => InquiryFormStatus::Draft,
                'heading' => $data['heading'] ?? $data['name'],
                'intro_text' => $data['intro_text'] ?? null,
                'submit_button_label' => $data['submit_button_label'] ?? 'Submit',
                'success_message' => $data['success_message'] ?? 'Thank you for your inquiry.',
                'error_message' => $data['error_message'] ?? null,
                'redirect_url' => $data['redirect_url'] ?? null,
                'sales_pipeline_type_id' => $data['sales_pipeline_type_id'] ?? null,
                'sales_pipeline_id' => $data['sales_pipeline_id'] ?? null,
                'default_stage_id' => $data['default_stage_id'] ?? null,
                'auto_create_deal' => (bool) ($data['auto_create_deal'] ?? false),
                'allowed_domains' => $this->normalizeDomains($data['allowed_domains'] ?? null),
                'field_mappings' => $data['field_mappings'] ?? null,
                'settings' => $data['settings'] ?? null,
                'tags' => $data['tags'] ?? null,
                'created_by' => session('admin_id'),
                'updated_by' => session('admin_id'),
            ]);

            $this->syncFields($form, $data['fields'] ?? []);
            $this->activities->log($form, 'inquiry_form.created', 'Inquiry form created');

            return $form->fresh(['fields']);
        });
    }

    public function update(InquiryForm $form, array $data): InquiryForm
    {
        return DB::transaction(function () use ($form, $data) {
            $form->update([
                'name' => $data['name'],
                'slug' => $this->availableSlug($data['slug'] ?? $data['name'], $form),
                'description' => $data['description'] ?? null,
                'heading' => $data['heading'] ?? $form->heading,
                'intro_text' => $data['intro_text'] ?? null,
                'submit_button_label' => $data['submit_button_label'] ?? $form->submit_button_label,
                'success_message' => $data['success_message'] ?? $form->success_message,
                'error_message' => $data['error_message'] ?? $form->error_message,
                'redirect_url' => $data['redirect_url'] ?? null,
                'sales_pipeline_type_id' => $data['sales_pipeline_type_id'] ?? null,
                'sales_pipeline_id' => $data['sales_pipeline_id'] ?? null,
                'default_stage_id' => $data['default_stage_id'] ?? null,
                'auto_create_deal' => (bool) ($data['auto_create_deal'] ?? $form->auto_create_deal),
                'allowed_domains' => array_key_exists('allowed_domains', $data)
                    ? $this->normalizeDomains($data['allowed_domains'])
                    : $form->allowed_domains,
                'field_mappings' => $data['field_mappings'] ?? $form->field_mappings,
                'settings' => $data['settings'] ?? $form->settings,
                'tags' => $data['tags'] ?? $form->tags,
                'updated_by' => session('admin_id'),
            ]);

            if (array_key_exists('fields', $data)) {
                $this->syncFields($form, $data['fields']);
            }

            $this->activities->log($form, 'inquiry_form.updated', 'Inquiry form updated');

            return $form->fresh(['fields']);
        });
    }

    public function publish(InquiryForm $form): InquiryForm
    {
        if ($form->fields()->where('is_active', true)->count() === 0) {
            throw ValidationException::withMessages([
                'fields' => 'Add at least one active field before publishing.',
            ]);
        }

        $form->update([
            'status' => InquiryFormStatus::Published,
            'embed_token' => $form->embed_token ?: Str::random(40),
            'published_at' => now(),
            'updated_by' => session('admin_id'),
        ]);

        $this->activities->log($form, 'inquiry_form.published', 'Inquiry form published');

        return $form;
    }

    public function unpublish(InquiryForm $form): InquiryForm
    {
        $form->update([
            'status' => InquiryFormStatus::Unpublished,
            'updated_by' => session('admin_id'),
        ]);

        $this->activities->log($form, 'inquiry_form.unpublished', 'Inquiry form unpublished');

        return $form;
    }

    public function duplicate(InquiryForm $form): InquiryForm
    {
        $form->load(['fields', 'conditions']);

        return DB::transaction(function () use ($form) {
            $copy = InquiryForm::query()->create([
                'name' => $form->name.' (Copy)',
                'slug' => $this->availableSlug($form->slug.'-copy'),
                'description' => $form->description,
                'status' => InquiryFormStatus::Draft,
                'heading' => $form->heading,
                'intro_text' => $form->intro_text,
                'submit_button_label' => $form->submit_button_label,
                'success_message' => $form->success_message,
                'error_message' => $form->error_message,
                'redirect_url' => $form->redirect_url,
                'sales_pipeline_type_id' => $form->sales_pipeline_type_id,
                'sales_pipeline_id' => $form->sales_pipeline_id,
                'default_stage_id' => $form->default_stage_id,
                'auto_create_deal' => $form->auto_create_deal,
                'allowed_domains' => $form->allowed_domains,
                'field_mappings' => $form->field_mappings,
                'settings' => $form->settings,
                'tags' => $form->tags,
                'created_by' => session('admin_id'),
                'updated_by' => session('admin_id'),
            ]);

            $fieldMap = [];
            foreach ($form->fields as $field) {
                $new = $copy->fields()->create([
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
                    'map_to_deal_field' => $field->map_to_deal_field,
                    'map_to_contact_field' => $field->map_to_contact_field,
                ]);
                $fieldMap[$field->id] = $new->id;
            }

            foreach ($form->conditions as $condition) {
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

            $this->activities->log($copy, 'inquiry_form.duplicated', 'Duplicated from '.$form->name);

            return $copy->fresh(['fields']);
        });
    }

    public function delete(InquiryForm $form): void
    {
        if ($form->submissions()->exists()) {
            throw ValidationException::withMessages([
                'inquiry_form' => 'This form has submissions and cannot be deleted. Unpublish or archive it instead.',
            ]);
        }

        $form->delete();
        $this->activities->log($form, 'inquiry_form.deleted', 'Inquiry form deleted');
    }

    /**
     * @param  list<array<string, mixed>>  $fields
     */
    private function syncFields(InquiryForm $form, array $fields): void
    {
        $keepIds = [];

        foreach (array_values($fields) as $index => $fieldData) {
            $key = $fieldData['key'] ?? Str::slug($fieldData['label'] ?? 'field', '_');
            $type = $fieldData['type'] ?? SalesFieldType::Text->value;

            $attrs = [
                'key' => $key,
                'label' => $fieldData['label'],
                'type' => $type,
                'help_text' => $fieldData['help_text'] ?? null,
                'placeholder' => $fieldData['placeholder'] ?? null,
                'default_value' => $fieldData['default_value'] ?? null,
                'is_required' => filter_var($fieldData['is_required'] ?? false, FILTER_VALIDATE_BOOLEAN),
                'is_active' => filter_var($fieldData['is_active'] ?? true, FILTER_VALIDATE_BOOLEAN),
                'sort_order' => $index,
                'validation' => $fieldData['validation'] ?? null,
                'options' => $fieldData['options'] ?? null,
                'settings' => $fieldData['settings'] ?? null,
                'map_to_deal_field' => $fieldData['map_to_deal_field'] ?? null,
                'map_to_contact_field' => $fieldData['map_to_contact_field'] ?? null,
            ];

            if (! empty($fieldData['public_id'])) {
                $existing = $form->fields()->where('public_id', $fieldData['public_id'])->first();
                if ($existing) {
                    $existing->update($attrs);
                    $keepIds[] = $existing->id;
                    continue;
                }
            }

            $created = $form->fields()->create($attrs);
            $keepIds[] = $created->id;
        }

        $form->fields()
            ->whereNotIn('id', $keepIds)
            ->get()
            ->each(function (InquiryFormField $field) {
                $field->targetConditions()->delete();
                $field->sourceConditions()->delete();
                $field->delete();
            });
    }

    private function availableSlug(string $value, ?InquiryForm $except = null): string
    {
        $base = Str::slug($value) ?: 'inquiry-form';
        $slug = $base;
        $suffix = 2;

        while (InquiryForm::withTrashed()
            ->when($except, fn ($q) => $q->whereKeyNot($except->getKey()))
            ->where('slug', $slug)
            ->exists()) {
            $slug = $base.'-'.$suffix++;
        }

        return $slug;
    }

    /**
     * @return list<string>|null
     */
    private function normalizeDomains(null|string|array $value): ?array
    {
        if ($value === null || $value === '') {
            return null;
        }

        $items = is_array($value)
            ? $value
            : (preg_split('/[\s,]+/', (string) $value) ?: []);

        $domains = collect($items)
            ->map(fn ($domain) => strtolower(trim((string) $domain)))
            ->filter()
            ->unique()
            ->values()
            ->all();

        return $domains === [] ? null : $domains;
    }
}
