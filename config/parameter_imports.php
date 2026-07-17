<?php

use App\Models\BoothType;
use App\Models\BusinessActivity;
use App\Models\CategoryType;
use App\Models\ExhibitorTag;
use App\Models\ExhibitorType;
use App\Models\GroupType;
use App\Models\Partner;
use App\Models\Persona;
use App\Models\ProductType;
use App\Models\RegistrationStatus;
use App\Models\Sponsor;

$namedParameter = static fn (string $model, string $label, string $plural): array => [
    'model' => $model,
    'label' => $label,
    'plural' => $plural,
    'supports_color' => true,
    'supports_slug' => true,
    'requires_sponsorship_fields' => false,
];

return [
    'registration-statuses' => $namedParameter(RegistrationStatus::class, 'Registration Status', 'Registration Statuses'),
    'personas' => $namedParameter(Persona::class, 'Persona', 'Personas'),
    'category-types' => $namedParameter(CategoryType::class, 'Category Type', 'Category Types'),
    'product-types' => $namedParameter(ProductType::class, 'Product Type', 'Product Types'),
    'exhibitor-tags' => $namedParameter(ExhibitorTag::class, 'Exhibitor Tag', 'Exhibitor Tags'),
    'booth-types' => $namedParameter(BoothType::class, 'Booth Type', 'Booth Types'),
    'exhibitor-types' => $namedParameter(ExhibitorType::class, 'Exhibitor Type', 'Exhibitor Types'),
    'business-activities' => $namedParameter(BusinessActivity::class, 'Business Activity', 'Business Activities'),
    'group-types' => $namedParameter(GroupType::class, 'Group Type', 'Group Types'),

    'sponsors' => [
        'model' => Sponsor::class,
        'label' => 'Sponsor',
        'plural' => 'Sponsors',
        'supports_color' => false,
        'supports_slug' => false,
        'requires_sponsorship_fields' => true,
        'sponsorship_label' => 'Sponsorship Label',
        'default_type' => 'Sponsor',
    ],
    'partners' => [
        'model' => Partner::class,
        'label' => 'Partner',
        'plural' => 'Partners',
        'supports_color' => false,
        'supports_slug' => false,
        'requires_sponsorship_fields' => true,
        'sponsorship_label' => 'Partnership Label',
        'default_type' => 'Partner',
    ],
];
