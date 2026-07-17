<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales_pipeline_types', function (Blueprint $table) {
            $table->id();
            $table->uuid('public_id')->unique();
            $table->unsignedBigInteger('event_id')->index();
            $table->unsignedBigInteger('org_id')->index();
            $table->string('name');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->string('color', 32)->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['event_id', 'org_id', 'slug']);
            $table->index(['event_id', 'org_id', 'is_active']);
        });

        Schema::create('sales_pipeline_type_stages', function (Blueprint $table) {
            $table->id();
            $table->uuid('public_id')->unique();
            $table->unsignedBigInteger('event_id')->index();
            $table->unsignedBigInteger('org_id')->index();
            $table->unsignedBigInteger('sales_pipeline_type_id');
            $table->string('name');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->string('color', 32)->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->unsignedTinyInteger('probability')->default(0);
            $table->string('category', 16)->default('open');
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sla_hours')->nullable();
            $table->text('instructions')->nullable();
            $table->json('required_field_keys')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('sales_pipeline_type_id', 'spts_type_fk')
                ->references('id')->on('sales_pipeline_types')->cascadeOnDelete();
            $table->unique(['sales_pipeline_type_id', 'slug'], 'spts_type_slug_unique');
            $table->index(['sales_pipeline_type_id', 'sort_order'], 'spts_type_sort_idx');
        });

        Schema::create('sales_pipeline_type_fields', function (Blueprint $table) {
            $table->id();
            $table->uuid('public_id')->unique();
            $table->unsignedBigInteger('event_id')->index();
            $table->unsignedBigInteger('org_id')->index();
            $table->unsignedBigInteger('sales_pipeline_type_id');
            $table->string('scope', 16);
            $table->string('key');
            $table->string('label');
            $table->string('type', 32);
            $table->text('help_text')->nullable();
            $table->string('placeholder')->nullable();
            $table->json('default_value')->nullable();
            $table->boolean('is_required')->default(false);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->json('validation')->nullable();
            $table->json('options')->nullable();
            $table->json('settings')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('sales_pipeline_type_id', 'sptf_type_fk')
                ->references('id')->on('sales_pipeline_types')->cascadeOnDelete();
            $table->unique(['sales_pipeline_type_id', 'scope', 'key'], 'sptf_type_scope_key_unique');
            $table->index(['sales_pipeline_type_id', 'scope', 'sort_order'], 'sptf_type_scope_sort_idx');
        });

        Schema::create('sales_pipeline_type_field_conditions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('event_id')->index();
            $table->unsignedBigInteger('org_id')->index();
            $table->unsignedBigInteger('sales_pipeline_type_id');
            $table->unsignedBigInteger('target_field_id');
            $table->unsignedBigInteger('source_field_id');
            $table->string('operator', 32);
            $table->json('compare_value')->nullable();
            $table->string('action', 16);
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('sales_pipeline_type_id', 'sptfc_type_fk')
                ->references('id')->on('sales_pipeline_types')->cascadeOnDelete();
            $table->foreign('target_field_id', 'sptfc_target_fk')
                ->references('id')->on('sales_pipeline_type_fields')->cascadeOnDelete();
            $table->foreign('source_field_id', 'sptfc_source_fk')
                ->references('id')->on('sales_pipeline_type_fields')->cascadeOnDelete();
        });

        Schema::create('sales_pipelines', function (Blueprint $table) {
            $table->id();
            $table->uuid('public_id')->unique();
            $table->unsignedBigInteger('event_id')->index();
            $table->unsignedBigInteger('org_id')->index();
            $table->unsignedBigInteger('sales_pipeline_type_id');
            $table->string('name');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->string('status', 16)->default('active');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->string('currency', 10)->nullable();
            $table->decimal('revenue_target', 15, 2)->nullable();
            $table->unsignedBigInteger('owner_admin_id')->nullable();
            $table->json('custom_field_answers')->nullable();
            $table->json('team_admin_ids')->nullable();
            $table->json('settings')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamp('last_activity_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('sales_pipeline_type_id', 'sp_type_fk')
                ->references('id')->on('sales_pipeline_types')->restrictOnDelete();
            $table->unique(['event_id', 'org_id', 'slug']);
            $table->index(['event_id', 'org_id', 'status']);
        });

        Schema::create('sales_pipeline_stages', function (Blueprint $table) {
            $table->id();
            $table->uuid('public_id')->unique();
            $table->unsignedBigInteger('event_id')->index();
            $table->unsignedBigInteger('org_id')->index();
            $table->unsignedBigInteger('sales_pipeline_id');
            $table->unsignedBigInteger('source_type_stage_id')->nullable();
            $table->string('name');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->string('color', 32)->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->unsignedTinyInteger('probability')->default(0);
            $table->string('category', 16)->default('open');
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sla_hours')->nullable();
            $table->text('instructions')->nullable();
            $table->json('required_field_keys')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('sales_pipeline_id', 'sps_pipeline_fk')
                ->references('id')->on('sales_pipelines')->cascadeOnDelete();
            $table->foreign('source_type_stage_id', 'sps_source_stage_fk')
                ->references('id')->on('sales_pipeline_type_stages')->nullOnDelete();
            $table->unique(['sales_pipeline_id', 'slug'], 'sps_pipeline_slug_unique');
            $table->index(['sales_pipeline_id', 'sort_order'], 'sps_pipeline_sort_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_pipeline_stages');
        Schema::dropIfExists('sales_pipelines');
        Schema::dropIfExists('sales_pipeline_type_field_conditions');
        Schema::dropIfExists('sales_pipeline_type_fields');
        Schema::dropIfExists('sales_pipeline_type_stages');
        Schema::dropIfExists('sales_pipeline_types');
    }
};
