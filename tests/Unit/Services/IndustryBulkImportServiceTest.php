<?php

namespace Tests\Unit\Services;

use App\Models\Industry;
use App\Services\AuditService;
use App\Services\IndustryService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Schema;
use Mockery;
use Tests\TestCase;

class IndustryBulkImportServiceTest extends TestCase
{
    private IndustryService $service;

    protected function setUp(): void
    {
        parent::setUp();

        config(['event.event_id' => 1, 'event.org_id' => 1]);

        Schema::dropIfExists('industries');
        Schema::dropIfExists('hash_mappings');

        Schema::create('hash_mappings', function (Blueprint $table) {
            $table->id();
            $table->string('hash', 64)->unique();
            $table->string('model_type');
            $table->unsignedBigInteger('model_id');
            $table->unsignedBigInteger('event_id');
            $table->unsignedBigInteger('org_id');
            $table->timestamps();
        });

        Schema::create('industries', function (Blueprint $table) {
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

        $audit = Mockery::mock(AuditService::class);
        $audit->shouldReceive('log')->andReturnNull();

        $this->service = new IndustryService($audit);
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('industries');
        Schema::dropIfExists('hash_mappings');
        Mockery::close();
        parent::tearDown();
    }

    public function test_bulk_import_from_pasted_names(): void
    {
        $result = $this->service->bulkImportIndustries([
            'names' => "Technology\nHealthcare\nFinance",
            'color' => '#3b82f6',
            'sort_order' => 10,
            'is_active' => true,
        ]);

        $this->assertSame(3, $result['imported']);
        $this->assertSame(0, $result['skipped']);
        $this->assertDatabaseHas('industries', [
            'name' => 'Technology',
            'slug' => 'technology',
            'color' => '#3b82f6',
            'sort_order' => 10,
            'event_id' => 1,
            'org_id' => 1,
        ]);
        $this->assertDatabaseHas('industries', [
            'name' => 'Healthcare',
            'sort_order' => 11,
        ]);
        $this->assertDatabaseHas('industries', [
            'name' => 'Finance',
            'sort_order' => 12,
        ]);
    }

    public function test_bulk_import_skips_duplicates_case_insensitively(): void
    {
        Industry::create([
            'name' => 'Technology',
            'slug' => 'technology',
            'event_id' => 1,
            'org_id' => 1,
        ]);

        $result = $this->service->bulkImportIndustries([
            'names' => "technology\nHealthcare\nHealthcare\nRetail",
            'is_active' => true,
            'sort_order' => 0,
        ]);

        $this->assertSame(2, $result['imported']);
        $this->assertSame(2, $result['skipped']);
        $this->assertEquals(3, Industry::count());
    }

    public function test_bulk_import_from_uploaded_file(): void
    {
        $file = UploadedFile::fake()->createWithContent(
            'industries.txt',
            "Education\nRetail\n"
        );

        $result = $this->service->bulkImportIndustries([
            'import_file' => $file,
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $this->assertSame(2, $result['imported']);
        $this->assertDatabaseHas('industries', ['name' => 'Education', 'slug' => 'education']);
        $this->assertDatabaseHas('industries', ['name' => 'Retail', 'slug' => 'retail']);
    }

    public function test_bulk_import_parses_csv_first_column_and_skips_header(): void
    {
        $result = $this->service->bulkImportIndustries([
            'names' => "name,color\nAerospace,#111111\n\"Defense\",#222222",
            'is_active' => true,
            'sort_order' => 0,
        ]);

        $this->assertSame(2, $result['imported']);
        $this->assertDatabaseHas('industries', ['name' => 'Aerospace']);
        $this->assertDatabaseHas('industries', ['name' => 'Defense']);
    }

    public function test_bulk_import_ignores_blank_lines(): void
    {
        $result = $this->service->bulkImportIndustries([
            'names' => "\n\nMedia\n\n\nPublishing\n",
            'is_active' => true,
            'sort_order' => 0,
        ]);

        $this->assertSame(2, $result['imported']);
        $this->assertSame(0, $result['skipped']);
    }
}
