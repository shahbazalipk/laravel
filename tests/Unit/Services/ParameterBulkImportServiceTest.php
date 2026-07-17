<?php

namespace Tests\Unit\Services;

use App\Models\ProductType;
use App\Models\Sponsor;
use App\Services\AuditService;
use App\Services\ParameterBulkImportService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Schema;
use Mockery;
use Tests\TestCase;

class ParameterBulkImportServiceTest extends TestCase
{
    private ParameterBulkImportService $service;

    protected function setUp(): void
    {
        parent::setUp();

        config(['event.event_id' => 1, 'event.org_id' => 1]);

        $this->createTestTables();

        $audit = Mockery::mock(AuditService::class);
        $audit->shouldReceive('log')->zeroOrMoreTimes();
        $this->service = new ParameterBulkImportService($audit);
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('sponsors');
        Schema::dropIfExists('product_types');
        Schema::dropIfExists('hash_mappings');
        Mockery::close();

        parent::tearDown();
    }

    public function test_imports_named_parameters_with_defaults_and_incrementing_sort_order(): void
    {
        $result = $this->service->import('product-types', [
            'names' => "Software\nHardware\nServices",
            'color' => '#123456',
            'sort_order' => 4,
            'is_active' => true,
        ]);

        $this->assertSame(['imported' => 3, 'skipped' => 0], $result);
        $this->assertDatabaseHas('product_types', [
            'name' => 'Software',
            'slug' => 'software',
            'color' => '#123456',
            'sort_order' => 4,
            'is_active' => 1,
            'event_id' => 1,
            'org_id' => 1,
        ]);
        $this->assertDatabaseHas('product_types', ['name' => 'Services', 'sort_order' => 6]);
    }

    public function test_skips_existing_and_in_file_duplicates_case_insensitively(): void
    {
        ProductType::create([
            'name' => 'Software',
            'slug' => 'software',
            'event_id' => 1,
            'org_id' => 1,
        ]);

        $result = $this->service->import('product-types', [
            'names' => "software\nHardware\nHARDWARE\nServices",
            'is_active' => true,
        ]);

        $this->assertSame(['imported' => 2, 'skipped' => 2], $result);
        $this->assertSame(3, ProductType::count());
    }

    public function test_imports_names_from_txt_and_csv_files(): void
    {
        $file = UploadedFile::fake()->createWithContent(
            'products.csv',
            "name,color\n\"Digital Products\",#111111\nConsulting,#222222"
        );

        $result = $this->service->import('product-types', [
            'import_file' => $file,
            'color' => '#6366f1',
            'is_active' => true,
        ]);

        $this->assertSame(2, $result['imported']);
        $this->assertDatabaseHas('product_types', ['name' => 'Digital Products']);
        $this->assertDatabaseHas('product_types', ['name' => 'Consulting']);
    }

    public function test_imports_sponsors_with_required_defaults_and_visibility(): void
    {
        $result = $this->service->import('sponsors', [
            'names' => "Acme Corp\nGlobex",
            'type' => 'Corporate',
            'sponsorship_label' => 'Gold',
            'visible_online' => true,
            'visible_onsite' => false,
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $this->assertSame(2, $result['imported']);
        $this->assertDatabaseHas('sponsors', [
            'name' => 'Acme Corp',
            'type' => 'Corporate',
            'sponsorship_label' => 'Gold',
            'visible_online' => 1,
            'visible_onsite' => 0,
            'is_active' => 1,
            'sort_order' => 1,
        ]);
    }

    private function createTestTables(): void
    {
        Schema::dropIfExists('sponsors');
        Schema::dropIfExists('product_types');
        Schema::dropIfExists('hash_mappings');

        Schema::create('hash_mappings', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('event_id');
            $table->unsignedBigInteger('org_id');
            $table->string('hash', 64)->unique();
            $table->string('model_type');
            $table->unsignedBigInteger('model_id');
            $table->timestamps();
        });

        Schema::create('product_types', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('event_id');
            $table->unsignedBigInteger('org_id');
            $table->string('name');
            $table->string('slug')->nullable();
            $table->string('color')->nullable();
            $table->text('description')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('sponsors', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('event_id');
            $table->unsignedBigInteger('org_id');
            $table->string('name');
            $table->string('sponsorship_label');
            $table->string('type');
            $table->boolean('is_active')->default(true);
            $table->boolean('visible_online')->default(false);
            $table->boolean('visible_onsite')->default(false);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }
}
