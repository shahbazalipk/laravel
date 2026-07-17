<?php

namespace App\Forms\Services;

use App\Forms\Enums\FormAudience;
use App\Forms\Models\CustomForm;
use App\Forms\Models\CustomFormCondition;
use App\Forms\Models\CustomFormQuestion;
use Illuminate\Database\QueryException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class FormDefinitionService
{
    /**
     * @return list<FormAudience>
     */
    public function availableAudiences(?CustomForm $except = null): array
    {
        $taken = CustomForm::query()
            ->when($except, fn ($query) => $query->whereKeyNot($except->getKey()))
            ->pluck('audience')
            ->map(fn ($audience) => $audience instanceof FormAudience ? $audience->value : (string) $audience)
            ->all();

        return array_values(array_filter(
            FormAudience::cases(),
            fn (FormAudience $audience) => ! in_array($audience->value, $taken, true)
        ));
    }

    public function createForm(array $data): CustomForm
    {
        $this->assertAudienceAvailable($data['audience'] ?? null);
        $data['slug'] = $this->availableSlug($data['slug'] ?? $data['name']);

        try {
            return CustomForm::create($data);
        } catch (QueryException $exception) {
            $this->rethrowAudienceConflict($exception, $data['audience'] ?? null);
            throw $exception;
        }
    }

    public function updateForm(CustomForm $form, array $data): CustomForm
    {
        // Audience stays locked on the model; ignore attempts to change it.
        $data['audience'] = $form->audience->value;
        $data['slug'] = $this->availableSlug($data['slug'] ?? $data['name'], $form);

        try {
            $form->update($data);
        } catch (QueryException $exception) {
            $this->rethrowAudienceConflict($exception, $data['audience'] ?? null);
            throw $exception;
        }

        return $form;
    }

    public function createQuestion(CustomForm $form, array $data): CustomFormQuestion
    {
        return DB::transaction(function () use ($form, $data): CustomFormQuestion {
            $question = $form->questions()->create($this->questionAttributes($data) + [
                'sort_order' => ($form->questions()->max('sort_order') ?? -1) + 1,
                'is_active' => true,
            ]);

            $this->replaceOptionsAndCondition($form, $question, $data);
            $this->bumpVersion($form);

            return $question;
        });
    }

    public function updateQuestion(CustomForm $form, CustomFormQuestion $question, array $data): CustomFormQuestion
    {
        $this->ensureQuestionBelongsToForm($form, $question);

        return DB::transaction(function () use ($form, $question, $data): CustomFormQuestion {
            $question->update($this->questionAttributes($data));
            $this->replaceOptionsAndCondition($form, $question, $data);
            $this->bumpVersion($form);

            return $question;
        });
    }

    public function deleteQuestion(CustomForm $form, CustomFormQuestion $question): void
    {
        $this->ensureQuestionBelongsToForm($form, $question);

        DB::transaction(function () use ($form, $question): void {
            CustomFormCondition::query()
                ->where(fn ($query) => $query
                    ->where('target_question_id', $question->id)
                    ->orWhere('source_question_id', $question->id))
                ->delete();
            $question->options()->delete();
            $question->delete();
            $this->normalizeSortOrder($form);
            $this->bumpVersion($form);
        });
    }

    public function reorderQuestion(CustomForm $form, CustomFormQuestion $question, string $direction): void
    {
        $this->ensureQuestionBelongsToForm($form, $question);

        DB::transaction(function () use ($form, $question, $direction): void {
            $ordered = $form->questions()->lockForUpdate()->get()->values();
            $index = $ordered->search(fn (CustomFormQuestion $item) => $item->is($question));
            $swapIndex = $direction === 'up' ? $index - 1 : $index + 1;

            if ($index === false || ! $ordered->has($swapIndex)) {
                return;
            }

            $other = $ordered[$swapIndex];
            $ordered->forget([$index, $swapIndex]);
            $ordered->put($index, $other);
            $ordered->put($swapIndex, $question);
            $positions = $ordered->sortKeys()->values()->pluck('id')->flip();
            $invalidCondition = $form->conditions()
                ->get()
                ->first(fn (CustomFormCondition $condition) =>
                    $positions->get($condition->source_question_id) >= $positions->get($condition->target_question_id)
                );

            if ($invalidCondition) {
                throw ValidationException::withMessages([
                    'direction' => 'That move would place a condition source after its target question.',
                ]);
            }

            [$question->sort_order, $other->sort_order] = [$other->sort_order, $question->sort_order];
            $question->save();
            $other->save();
            $this->normalizeSortOrder($form);
            $this->bumpVersion($form);
        });
    }

    private function assertAudienceAvailable(FormAudience|string|null $audience, ?CustomForm $except = null): void
    {
        if ($audience === null || $audience === '') {
            return;
        }

        $value = $audience instanceof FormAudience ? $audience->value : (string) $audience;
        $exists = CustomForm::query()
            ->when($except, fn ($query) => $query->whereKeyNot($except->getKey()))
            ->where('audience_unique', $value)
            ->exists();

        if ($exists) {
            $label = FormAudience::tryFrom($value)?->label() ?? $value;
            throw ValidationException::withMessages([
                'audience' => "A {$label} form already exists for this event. Open the existing form to edit it, or delete it before creating another.",
            ]);
        }
    }

    private function rethrowAudienceConflict(QueryException $exception, FormAudience|string|null $audience): void
    {
        $message = $exception->getMessage();
        if (! str_contains($message, 'custom_forms_event_org_audience_unique')
            && ! str_contains($message, 'audience_unique')) {
            return;
        }

        $value = $audience instanceof FormAudience ? $audience->value : (string) $audience;
        $label = FormAudience::tryFrom($value)?->label() ?? 'that';

        throw ValidationException::withMessages([
            'audience' => "A {$label} form already exists for this event. Open the existing form to edit it, or delete it before creating another.",
        ]);
    }

    private function replaceOptionsAndCondition(CustomForm $form, CustomFormQuestion $question, array $data): void
    {
        $question->options()->delete();

        if ($question->type->hasOptions()) {
            foreach (array_values($data['options'] ?? []) as $index => $option) {
                $question->options()->create([
                    'label' => $option['label'],
                    'value' => $option['value'],
                    'sort_order' => $index,
                    'is_active' => true,
                ]);
            }
        }

        $question->targetConditions()->delete();
        $condition = $data['condition'] ?? null;

        if (! is_array($condition) || empty($condition['source_question'])) {
            return;
        }

        $source = $form->questions()
            ->where('public_id', $condition['source_question'])
            ->first();

        if (! $source || $source->is($question) || $source->sort_order >= $question->sort_order) {
            throw ValidationException::withMessages([
                'condition.source_question' => 'The condition source must be an earlier question in this form.',
            ]);
        }

        $form->conditions()->create([
            'target_question_id' => $question->id,
            'source_question_id' => $source->id,
            'operator' => $condition['operator'],
            'compare_value' => in_array($condition['operator'], ['is_empty', 'is_not_empty'], true)
                ? null
                : ($condition['compare_value'] ?? null),
            'action' => $condition['action'],
            'sort_order' => 0,
            'is_active' => true,
        ]);
    }

    private function questionAttributes(array $data): array
    {
        return Arr::only($data, [
            'key', 'label', 'type', 'is_required', 'placeholder', 'help_text',
        ]) + [
            'validation' => array_filter($data['validation'] ?? [], fn ($value) => $value !== null && $value !== ''),
        ];
    }

    private function bumpVersion(CustomForm $form): void
    {
        $settings = $form->fresh()->settings ?? [];
        $settings['version'] = ((int) ($settings['version'] ?? 0)) + 1;
        $form->update(['settings' => $settings]);
    }

    private function normalizeSortOrder(CustomForm $form): void
    {
        $form->questions()->orderBy('sort_order')->get()->each(
            fn (CustomFormQuestion $question, int $index) => $question->update(['sort_order' => $index])
        );
    }

    private function ensureQuestionBelongsToForm(CustomForm $form, CustomFormQuestion $question): void
    {
        abort_unless($question->custom_form_id === $form->id, 404);
    }

    private function availableSlug(string $value, ?CustomForm $except = null): string
    {
        $base = Str::slug($value) ?: 'custom-form';
        $slug = $base;
        $suffix = 2;

        while (CustomForm::withTrashed()
            ->when($except, fn ($query) => $query->whereKeyNot($except->getKey()))
            ->where('slug', $slug)
            ->exists()) {
            $slug = $base.'-'.$suffix++;
        }

        return $slug;
    }
}
