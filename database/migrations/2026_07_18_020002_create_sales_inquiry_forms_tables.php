<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales_inquiry_forms', function (Blueprint $table) {
            $table->id();
            $table->uuid('public_id')->unique();
            $table->unsignedBigInteger('event_id')->index();
            $table->unsignedBigInteger('org_id')->index();
            $table->string('name');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->string('status', 16)->default('draft');
            $table->string('heading')->nullable();
            $table->text('intro_text')->nullable();
            $table->string('submit_button_label')->default('Submit');
            $table->text('success_message')->nullable();
            $table->text('error_message')->nullable();
            $table->string('redirect_url')->nullable();
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->unsignedInteger('max_submissions')->nullable();
            $table->boolean('allow_multiple_per_email')->default(true);
            $table->boolean('require_captcha')->default(false);
            $table->boolean('require_terms')->default(false);
            $table->boolean('require_privacy')->default(false);
            $table->unsignedBigInteger('sales_pipeline_type_id')->nullable();
            $table->unsignedBigInteger('sales_pipeline_id')->nullable();
            $table->unsignedBigInteger('default_stage_id')->nullable();
            $table->boolean('auto_create_deal')->default(false);
            $table->string('embed_token', 64)->nullable()->unique();
            $table->json('allowed_domains')->nullable();
            $table->json('field_mappings')->nullable();
            $table->json('settings')->nullable();
            $table->json('tags')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('sales_pipeline_type_id', 'sif_type_fk')
                ->references('id')->on('sales_pipeline_types')->nullOnDelete();
            $table->foreign('sales_pipeline_id', 'sif_pipeline_fk')
                ->references('id')->on('sales_pipelines')->nullOnDelete();
            $table->foreign('default_stage_id', 'sif_stage_fk')
                ->references('id')->on('sales_pipeline_stages')->nullOnDelete();
            $table->unique(['event_id', 'org_id', 'slug']);
            $table->index(['event_id', 'org_id', 'status']);
        });

        Schema::create('sales_inquiry_form_fields', function (Blueprint $table) {
            $table->id();
            $table->uuid('public_id')->unique();
            $table->unsignedBigInteger('event_id')->index();
            $table->unsignedBigInteger('org_id')->index();
            $table->unsignedBigInteger('sales_inquiry_form_id');
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
            $table->string('map_to_deal_field')->nullable();
            $table->string('map_to_contact_field')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('sales_inquiry_form_id', 'siff_form_fk')
                ->references('id')->on('sales_inquiry_forms')->cascadeOnDelete();
            $table->unique(['sales_inquiry_form_id', 'key'], 'siff_form_key_unique');
        });

        Schema::create('sales_inquiry_form_field_conditions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('event_id')->index();
            $table->unsignedBigInteger('org_id')->index();
            $table->unsignedBigInteger('sales_inquiry_form_id');
            $table->unsignedBigInteger('target_field_id');
            $table->unsignedBigInteger('source_field_id');
            $table->string('operator', 32);
            $table->json('compare_value')->nullable();
            $table->string('action', 16);
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('sales_inquiry_form_id', 'siffc_form_fk')
                ->references('id')->on('sales_inquiry_forms')->cascadeOnDelete();
            $table->foreign('target_field_id', 'siffc_target_fk')
                ->references('id')->on('sales_inquiry_form_fields')->cascadeOnDelete();
            $table->foreign('source_field_id', 'siffc_source_fk')
                ->references('id')->on('sales_inquiry_form_fields')->cascadeOnDelete();
        });

        Schema::create('sales_inquiry_submissions', function (Blueprint $table) {
            $table->id();
            $table->uuid('public_id')->unique();
            $table->unsignedBigInteger('event_id')->index();
            $table->unsignedBigInteger('org_id')->index();
            $table->unsignedBigInteger('sales_inquiry_form_id');
            $table->string('reference')->nullable();
            $table->string('status', 32)->default('new');
            $table->string('submitter_name')->nullable();
            $table->string('submitter_email')->nullable();
            $table->string('submitter_phone')->nullable();
            $table->string('company_name')->nullable();
            $table->json('answers')->nullable();
            $table->string('source_url')->nullable();
            $table->string('embed_domain')->nullable();
            $table->json('utm')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->unsignedBigInteger('assigned_admin_id')->nullable();
            $table->text('internal_notes')->nullable();
            $table->unsignedBigInteger('sales_deal_id')->nullable();
            $table->timestamp('converted_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('sales_inquiry_form_id', 'sis_form_fk')
                ->references('id')->on('sales_inquiry_forms')->cascadeOnDelete();
            $table->foreign('sales_deal_id', 'sis_deal_fk')
                ->references('id')->on('sales_deals')->nullOnDelete();
            $table->unique(['event_id', 'org_id', 'reference']);
            $table->index(['sales_inquiry_form_id', 'status'], 'sis_form_status_idx');
        });

        Schema::create('sales_inquiry_submission_files', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('event_id')->index();
            $table->unsignedBigInteger('org_id')->index();
            $table->unsignedBigInteger('sales_inquiry_submission_id');
            $table->string('field_key');
            $table->string('disk');
            $table->string('path');
            $table->string('original_name');
            $table->string('mime')->nullable();
            $table->unsignedBigInteger('size')->default(0);
            $table->timestamps();

            $table->foreign('sales_inquiry_submission_id', 'sisf_submission_fk')
                ->references('id')->on('sales_inquiry_submissions')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_inquiry_submission_files');
        Schema::dropIfExists('sales_inquiry_submissions');
        Schema::dropIfExists('sales_inquiry_form_field_conditions');
        Schema::dropIfExists('sales_inquiry_form_fields');
        Schema::dropIfExists('sales_inquiry_forms');
    }
};
