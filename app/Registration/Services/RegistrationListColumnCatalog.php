<?php

namespace App\Registration\Services;

use App\Forms\Enums\FormAudience;
use App\Forms\Models\CustomForm;
use App\Forms\Models\CustomFormQuestion;
use App\Forms\Services\FormResolver;
use Illuminate\Database\QueryException;
use Illuminate\Support\Collection;

class RegistrationListColumnCatalog
{
    public const DEFAULT_KEYS = [
        'std:reference',
        'std:name',
        'std:email',
        'std:category',
        'std:stage',
        'std:payment',
    ];

    public function __construct(
        private FormResolver $forms
    ) {}

    /**
     * @return array<int, array{key: string, label: string, group: string, description?: string}>
     */
    public function all(): array
    {
        return array_merge($this->standardColumns(), $this->questionColumns());
    }

    /**
     * @return array<string, array{key: string, label: string, group: string, description?: string}>
     */
    public function keyed(): array
    {
        return collect($this->all())->keyBy('key')->all();
    }

    /**
     * @param  array<int, string>|null  $keys
     * @return array<int, array{key: string, label: string, group: string, description?: string}>
     */
    public function resolve(?array $keys): array
    {
        $catalog = $this->keyed();
        $selected = is_array($keys) && $keys !== [] ? $keys : self::DEFAULT_KEYS;

        $columns = [];
        foreach ($selected as $key) {
            if (isset($catalog[$key])) {
                $columns[] = $catalog[$key];
            } elseif (str_starts_with((string) $key, 'q:')) {
                $columns[] = [
                    'key' => $key,
                    'label' => 'Custom question',
                    'group' => 'Custom questions',
                    'description' => 'This question is no longer active.',
                ];
            }
        }

        return $columns !== [] ? $columns : $this->resolve(self::DEFAULT_KEYS);
    }

    public function needsCustomAnswers(array $keys): bool
    {
        foreach ($keys as $key) {
            if (str_starts_with((string) $key, 'q:')) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array<int, array{key: string, label: string, group: string, description?: string}>
     */
    private function standardColumns(): array
    {
        return [
            ['key' => 'std:reference', 'label' => 'Reference', 'group' => 'Core'],
            ['key' => 'std:name', 'label' => 'Name', 'group' => 'Core'],
            ['key' => 'std:email', 'label' => 'Email', 'group' => 'Contact'],
            ['key' => 'std:phone', 'label' => 'Phone', 'group' => 'Contact'],
            ['key' => 'std:mobile', 'label' => 'Mobile', 'group' => 'Contact'],
            ['key' => 'std:company', 'label' => 'Company', 'group' => 'Company'],
            ['key' => 'std:job_title', 'label' => 'Job title', 'group' => 'Company'],
            ['key' => 'std:department', 'label' => 'Department', 'group' => 'Company'],
            ['key' => 'std:industry', 'label' => 'Industry', 'group' => 'Company'],
            ['key' => 'std:category', 'label' => 'Category', 'group' => 'Registration'],
            ['key' => 'std:status', 'label' => 'Status', 'group' => 'Registration'],
            ['key' => 'std:stage', 'label' => 'Stage', 'group' => 'Registration'],
            ['key' => 'std:type', 'label' => 'Type', 'group' => 'Registration'],
            ['key' => 'std:group', 'label' => 'Group', 'group' => 'Registration'],
            ['key' => 'std:exhibitor', 'label' => 'Exhibitor', 'group' => 'Registration'],
            ['key' => 'std:payment', 'label' => 'Payment', 'group' => 'Billing'],
            ['key' => 'std:total_amount', 'label' => 'Total amount', 'group' => 'Billing'],
            ['key' => 'std:check_in', 'label' => 'Check-in', 'group' => 'Event'],
            ['key' => 'std:city', 'label' => 'City', 'group' => 'Address'],
            ['key' => 'std:country', 'label' => 'Country', 'group' => 'Address'],
            ['key' => 'std:notes', 'label' => 'Notes', 'group' => 'Additional'],
            ['key' => 'std:step', 'label' => 'Draft step', 'group' => 'Additional'],
            ['key' => 'std:created_at', 'label' => 'Created', 'group' => 'Additional'],
        ];
    }

    /**
     * @return array<int, array{key: string, label: string, group: string, description?: string}>
     */
    private function questionColumns(): array
    {
        try {
            $forms = $this->forms->activeForAudience(FormAudience::Registration);
        } catch (QueryException) {
            return [];
        }

        $columns = [];

        /** @var CustomForm $form */
        foreach ($forms as $form) {
            $conditionalTargets = $form->conditions->pluck('target_question_id')->unique();

            /** @var CustomFormQuestion $question */
            foreach ($form->questions as $question) {
                $isConditional = $conditionalTargets->contains($question->id);
                $columns[] = [
                    'key' => 'q:'.$question->public_id,
                    'label' => $question->label,
                    'group' => $form->name ?: 'Custom questions',
                    'description' => $isConditional ? 'Conditional question' : null,
                ];
            }
        }

        return $columns;
    }

    /**
     * @return Collection<string, Collection<int, array{key: string, label: string, group: string, description?: string}>>
     */
    public function grouped(): Collection
    {
        return collect($this->all())->groupBy('group');
    }
}
