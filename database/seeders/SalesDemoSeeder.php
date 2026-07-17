<?php

namespace Database\Seeders;

use App\Sales\Enums\SalesFieldType;
use App\Sales\Enums\StageCategory;
use App\Sales\Services\PipelineTypeService;
use Illuminate\Database\Seeder;

class SalesDemoSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment(['local', 'development', 'testing'])) {
            $this->command?->warn('SalesDemoSeeder skipped outside local/development/testing.');

            return;
        }

        $service = app(PipelineTypeService::class);

        $service->create([
            'name' => 'Sponsorship Sales',
            'slug' => 'sponsorship-sales',
            'description' => 'Demo sponsorship pipeline type',
            'color' => '#4f46e5',
            'is_active' => true,
            'stages' => [
                ['name' => 'New Inquiry', 'category' => StageCategory::Open->value, 'probability' => 10, 'is_default' => true, 'color' => '#6366f1'],
                ['name' => 'Contacted', 'category' => StageCategory::Open->value, 'probability' => 20, 'color' => '#818cf8'],
                ['name' => 'Qualified', 'category' => StageCategory::Open->value, 'probability' => 40, 'color' => '#a5b4fc'],
                ['name' => 'Proposal Sent', 'category' => StageCategory::Open->value, 'probability' => 60, 'color' => '#c7d2fe'],
                ['name' => 'Negotiation', 'category' => StageCategory::Open->value, 'probability' => 75, 'color' => '#e0e7ff'],
                ['name' => 'Won', 'category' => StageCategory::Won->value, 'probability' => 100, 'color' => '#10b981'],
                ['name' => 'Lost', 'category' => StageCategory::Lost->value, 'probability' => 0, 'color' => '#ef4444'],
            ],
            'deal_fields' => [
                ['label' => 'Company', 'key' => 'company', 'type' => SalesFieldType::Text->value, 'is_required' => true],
                ['label' => 'Sponsorship Package', 'key' => 'sponsorship_package', 'type' => SalesFieldType::Select->value, 'options' => [
                    ['label' => 'Gold', 'value' => 'gold'],
                    ['label' => 'Silver', 'value' => 'silver'],
                    ['label' => 'Bronze', 'value' => 'bronze'],
                ]],
                ['label' => 'Budget', 'key' => 'budget', 'type' => SalesFieldType::Number->value],
                ['label' => 'Industry', 'key' => 'industry', 'type' => SalesFieldType::Text->value],
                ['label' => 'Expected Close Date', 'key' => 'expected_close_date', 'type' => SalesFieldType::Date->value],
                ['label' => 'Requirements', 'key' => 'requirements', 'type' => SalesFieldType::Textarea->value],
                ['label' => 'Lost Reason', 'key' => 'lost_reason', 'type' => SalesFieldType::Textarea->value],
            ],
            'pipeline_fields' => [
                ['label' => 'Sales Target', 'key' => 'sales_target', 'type' => SalesFieldType::Number->value],
                ['label' => 'Brochure URL', 'key' => 'brochure_url', 'type' => SalesFieldType::Url->value],
            ],
        ]);

        $service->create([
            'name' => 'Exhibitor Sales',
            'slug' => 'exhibitor-sales',
            'description' => 'Demo exhibitor pipeline type',
            'color' => '#d97706',
            'is_active' => true,
            'stages' => [
                ['name' => 'Inquiry Received', 'category' => StageCategory::Open->value, 'probability' => 10, 'is_default' => true, 'color' => '#f59e0b'],
                ['name' => 'Qualification', 'category' => StageCategory::Open->value, 'probability' => 25, 'color' => '#fbbf24'],
                ['name' => 'Booth Options Shared', 'category' => StageCategory::Open->value, 'probability' => 40, 'color' => '#fcd34d'],
                ['name' => 'Quotation Sent', 'category' => StageCategory::Open->value, 'probability' => 55, 'color' => '#fde68a'],
                ['name' => 'Follow-up', 'category' => StageCategory::Open->value, 'probability' => 70, 'color' => '#fef3c7'],
                ['name' => 'Confirmed', 'category' => StageCategory::Won->value, 'probability' => 100, 'color' => '#10b981'],
                ['name' => 'Lost', 'category' => StageCategory::Lost->value, 'probability' => 0, 'color' => '#ef4444'],
            ],
            'deal_fields' => [
                ['label' => 'Company', 'key' => 'company', 'type' => SalesFieldType::Text->value, 'is_required' => true],
                ['label' => 'Booth Size', 'key' => 'booth_size', 'type' => SalesFieldType::Text->value],
                ['label' => 'Hall Preference', 'key' => 'hall_preference', 'type' => SalesFieldType::Text->value],
                ['label' => 'Product Category', 'key' => 'product_category', 'type' => SalesFieldType::Text->value],
                ['label' => 'Budget', 'key' => 'budget', 'type' => SalesFieldType::Number->value],
                ['label' => 'Country', 'key' => 'country', 'type' => SalesFieldType::Text->value],
                ['label' => 'Expected Close Date', 'key' => 'expected_close_date', 'type' => SalesFieldType::Date->value],
            ],
            'pipeline_fields' => [
                ['label' => 'Hall', 'key' => 'hall', 'type' => SalesFieldType::Text->value],
                ['label' => 'Pricing Plan', 'key' => 'pricing_plan', 'type' => SalesFieldType::Text->value],
            ],
        ]);

        $this->command?->info('Sales demo pipeline types seeded.');
    }
}
