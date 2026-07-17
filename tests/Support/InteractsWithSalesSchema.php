<?php

namespace Tests\Support;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

trait InteractsWithSalesSchema
{
    protected function createSalesTables(): void
    {
        Schema::create('sales_pipeline_types', function (Blueprint $table): void {
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
        });

        Schema::create('sales_pipeline_type_stages', function (Blueprint $table): void {
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
        });

        Schema::create('sales_pipeline_type_fields', function (Blueprint $table): void {
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
        });

        Schema::create('sales_pipeline_type_field_conditions', function (Blueprint $table): void {
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
        });

        Schema::create('sales_pipelines', function (Blueprint $table): void {
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
        });

        Schema::create('sales_pipeline_stages', function (Blueprint $table): void {
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
        });

        Schema::create('sales_deals', function (Blueprint $table): void {
            $table->id();
            $table->uuid('public_id')->unique();
            $table->unsignedBigInteger('event_id')->index();
            $table->unsignedBigInteger('org_id')->index();
            $table->unsignedBigInteger('sales_pipeline_id');
            $table->unsignedBigInteger('sales_pipeline_stage_id');
            $table->string('title');
            $table->string('reference')->nullable();
            $table->decimal('value', 15, 2)->nullable();
            $table->string('currency', 10)->nullable();
            $table->unsignedTinyInteger('probability')->nullable();
            $table->date('expected_close_date')->nullable();
            $table->date('actual_close_date')->nullable();
            $table->unsignedBigInteger('owner_admin_id')->nullable();
            $table->json('assigned_admin_ids')->nullable();
            $table->string('lead_source')->nullable();
            $table->string('status', 16)->default('open');
            $table->string('priority', 16)->nullable();
            $table->text('description')->nullable();
            $table->text('lost_reason')->nullable();
            $table->text('won_notes')->nullable();
            $table->json('tags')->nullable();
            $table->json('custom_field_answers')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamp('last_activity_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('sales_deal_contacts', function (Blueprint $table): void {
            $table->id();
            $table->uuid('public_id')->unique();
            $table->unsignedBigInteger('event_id')->index();
            $table->unsignedBigInteger('org_id')->index();
            $table->unsignedBigInteger('sales_deal_id');
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('job_title')->nullable();
            $table->string('company_name')->nullable();
            $table->boolean('is_primary')->default(false);
            $table->json('meta')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('sales_deal_notes', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('event_id')->index();
            $table->unsignedBigInteger('org_id')->index();
            $table->unsignedBigInteger('sales_deal_id');
            $table->unsignedBigInteger('author_admin_id')->nullable();
            $table->text('body');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('sales_deal_stage_histories', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('event_id')->index();
            $table->unsignedBigInteger('org_id')->index();
            $table->unsignedBigInteger('sales_deal_id');
            $table->unsignedBigInteger('from_stage_id')->nullable();
            $table->unsignedBigInteger('to_stage_id');
            $table->unsignedBigInteger('moved_by')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();
        });

        Schema::create('sales_activities', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('event_id')->index();
            $table->unsignedBigInteger('org_id')->index();
            $table->string('subject_type');
            $table->unsignedBigInteger('subject_id');
            $table->string('type', 64);
            $table->string('summary');
            $table->json('properties')->nullable();
            $table->unsignedBigInteger('actor_admin_id')->nullable();
            $table->timestamps();
        });

        Schema::create('sales_inquiry_forms', function (Blueprint $table): void {
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
        });

        Schema::create('sales_inquiry_form_fields', function (Blueprint $table): void {
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
        });

        Schema::create('sales_inquiry_form_field_conditions', function (Blueprint $table): void {
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
        });

        Schema::create('sales_inquiry_submissions', function (Blueprint $table): void {
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
        });

        Schema::create('sales_inquiry_submission_files', function (Blueprint $table): void {
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
        });
    }

    protected function dropSalesTables(): void
    {
        Schema::dropIfExists('sales_inquiry_submission_files');
        Schema::dropIfExists('sales_inquiry_submissions');
        Schema::dropIfExists('sales_inquiry_form_field_conditions');
        Schema::dropIfExists('sales_inquiry_form_fields');
        Schema::dropIfExists('sales_inquiry_forms');
        Schema::dropIfExists('sales_activities');
        Schema::dropIfExists('sales_deal_stage_histories');
        Schema::dropIfExists('sales_deal_notes');
        Schema::dropIfExists('sales_deal_contacts');
        Schema::dropIfExists('sales_deals');
        Schema::dropIfExists('sales_pipeline_stages');
        Schema::dropIfExists('sales_pipelines');
        Schema::dropIfExists('sales_pipeline_type_field_conditions');
        Schema::dropIfExists('sales_pipeline_type_fields');
        Schema::dropIfExists('sales_pipeline_type_stages');
        Schema::dropIfExists('sales_pipeline_types');
    }
}
