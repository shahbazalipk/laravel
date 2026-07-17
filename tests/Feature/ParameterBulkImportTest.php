<?php

namespace Tests\Feature;

use App\Services\ParameterBulkImportService;
use Mockery;
use Tests\TestCase;

class ParameterBulkImportTest extends TestCase
{
    private const PARAMETERS = [
        'sponsors',
        'partners',
        'registration-statuses',
        'personas',
        'category-types',
        'product-types',
        'exhibitor-tags',
        'booth-types',
        'exhibitor-types',
        'business-activities',
        'group-types',
    ];

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_bulk_import_route_requires_admin_authentication(): void
    {
        $response = $this->post(route('admin.parameters.bulk-import', 'product-types'), [
            'names' => 'Software',
        ]);

        $response->assertRedirect(route('admin.login'));
    }

    public function test_named_parameter_bulk_import_uses_shared_service(): void
    {
        $service = Mockery::mock(ParameterBulkImportService::class);
        $service->shouldReceive('import')
            ->once()
            ->with('product-types', Mockery::on(function (array $data): bool {
                return $data['names'] === "Software\nHardware"
                    && $data['color'] === '#6366f1'
                    && $data['is_active'] === true;
            }))
            ->andReturn(['imported' => 2, 'skipped' => 0]);
        $this->app->instance(ParameterBulkImportService::class, $service);

        $response = $this->asAdmin()->post(
            route('admin.parameters.bulk-import', 'product-types'),
            [
                'names' => "Software\nHardware",
                'color' => '#6366f1',
                'sort_order' => 0,
                'is_active' => '1',
            ]
        );

        $response->assertRedirect(route('admin.product-types.index'));
        $response->assertSessionHas('success', 'Successfully imported 2 Product Types');
    }

    public function test_sponsor_import_requires_type_and_sponsorship_label(): void
    {
        $response = $this->asAdmin()->post(
            route('admin.parameters.bulk-import', 'sponsors'),
            ['names' => 'Acme Corp']
        );

        $response->assertSessionHasErrors(['type', 'sponsorship_label']);
    }

    public function test_import_requires_pasted_names_or_a_file(): void
    {
        $response = $this->asAdmin()->post(
            route('admin.parameters.bulk-import', 'personas'),
            ['names' => '   ']
        );

        $response->assertSessionHasErrors('names');
    }

    public function test_every_parameter_nav_module_has_config_and_bulk_import_ui(): void
    {
        $definitions = config('parameter_imports');

        $this->assertEqualsCanonicalizing(self::PARAMETERS, array_keys($definitions));

        foreach (self::PARAMETERS as $parameter) {
            $view = resource_path("views/admin/{$parameter}/index.blade.php");

            $this->assertFileExists($view);
            $this->assertStringContainsString(
                "['parameter' => '{$parameter}']",
                file_get_contents($view),
                "Missing bulk import UI for {$parameter}"
            );
        }
    }

    public function test_shared_modal_renders_for_every_parameter_module(): void
    {
        foreach (self::PARAMETERS as $parameter) {
            $html = view(
                'admin.components.parameter-bulk-import',
                [
                    'parameter' => $parameter,
                    'errors' => new \Illuminate\Support\ViewErrorBag(),
                ]
            )->render();

            $this->assertStringContainsString('data-testid="bulk-import-button"', $html);
            $this->assertStringContainsString('data-testid="bulk-import-form"', $html);
            $this->assertStringContainsString(
                route('admin.parameters.bulk-import', $parameter),
                $html
            );
        }
    }

    private function asAdmin(): static
    {
        return $this->withSession([
            'admin_logged_in' => true,
            'admin_id' => 1,
            'admin_email' => 'admin@test.com',
            'event_id' => 1,
            'org_id' => 1,
        ]);
    }
}
