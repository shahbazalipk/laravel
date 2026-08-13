<?php

namespace Tests\Unit\Registration;

use App\Forms\Enums\FormAudience;
use App\Forms\Models\CustomForm;
use App\Forms\Models\CustomFormCondition;
use App\Forms\Models\CustomFormQuestion;
use App\Forms\Services\FormResolver;
use App\Registration\Services\RegistrationListColumnCatalog;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class RegistrationListColumnCatalogTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    #[Test]
    public function it_includes_standard_columns_and_tags_conditional_questions(): void
    {
        $meal = new CustomFormQuestion;
        $meal->forceFill([
            'id' => 1,
            'public_id' => '11111111-1111-1111-1111-111111111111',
            'label' => 'Meal preference',
        ]);
        $allergy = new CustomFormQuestion;
        $allergy->forceFill([
            'id' => 2,
            'public_id' => '22222222-2222-2222-2222-222222222222',
            'label' => 'Allergy notes',
        ]);

        $form = new CustomForm(['name' => 'Registration Survey']);
        $form->setRelation('questions', new EloquentCollection([$meal, $allergy]));
        $condition = new CustomFormCondition;
        $condition->forceFill(['target_question_id' => 2]);
        $form->setRelation('conditions', new EloquentCollection([$condition]));

        $resolver = Mockery::mock(FormResolver::class);
        $resolver->shouldReceive('activeForAudience')
            ->once()
            ->with(FormAudience::Registration)
            ->andReturn(new EloquentCollection([$form]));

        $catalog = new RegistrationListColumnCatalog($resolver);
        $keyed = $catalog->keyed();

        $this->assertArrayHasKey('std:reference', $keyed);
        $this->assertArrayHasKey('std:company', $keyed);
        $this->assertSame('Meal preference', $keyed['q:11111111-1111-1111-1111-111111111111']['label']);
        $this->assertNull($keyed['q:11111111-1111-1111-1111-111111111111']['description']);
        $this->assertSame('Conditional question', $keyed['q:22222222-2222-2222-2222-222222222222']['description']);
    }

    #[Test]
    public function it_falls_back_to_default_columns_and_keeps_retired_question_keys(): void
    {
        $resolver = Mockery::mock(FormResolver::class);
        $resolver->shouldReceive('activeForAudience')
            ->andReturn(new EloquentCollection);

        $catalog = new RegistrationListColumnCatalog($resolver);

        $defaults = $catalog->resolve(null);
        $this->assertSame(RegistrationListColumnCatalog::DEFAULT_KEYS, array_column($defaults, 'key'));

        $resolved = $catalog->resolve([
            'std:name',
            'std:not-a-column',
            'q:retired-question',
        ]);

        $this->assertSame(['std:name', 'q:retired-question'], array_column($resolved, 'key'));
        $this->assertSame('This question is no longer active.', $resolved[1]['description']);
        $this->assertTrue($catalog->needsCustomAnswers(['std:name', 'q:retired-question']));
        $this->assertFalse($catalog->needsCustomAnswers(['std:name']));
    }
}
