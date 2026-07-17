<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales_deals', function (Blueprint $table) {
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

            $table->foreign('sales_pipeline_id', 'sd_pipeline_fk')
                ->references('id')->on('sales_pipelines')->cascadeOnDelete();
            $table->foreign('sales_pipeline_stage_id', 'sd_stage_fk')
                ->references('id')->on('sales_pipeline_stages')->restrictOnDelete();
            $table->unique(['event_id', 'org_id', 'reference']);
            $table->index(['sales_pipeline_id', 'sales_pipeline_stage_id'], 'sd_pipeline_stage_idx');
            $table->index(['event_id', 'org_id', 'status']);
        });

        Schema::create('sales_deal_contacts', function (Blueprint $table) {
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

            $table->foreign('sales_deal_id', 'sdc_deal_fk')
                ->references('id')->on('sales_deals')->cascadeOnDelete();
        });

        Schema::create('sales_deal_notes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('event_id')->index();
            $table->unsignedBigInteger('org_id')->index();
            $table->unsignedBigInteger('sales_deal_id');
            $table->unsignedBigInteger('author_admin_id')->nullable();
            $table->text('body');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('sales_deal_id', 'sdn_deal_fk')
                ->references('id')->on('sales_deals')->cascadeOnDelete();
        });

        Schema::create('sales_deal_stage_histories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('event_id')->index();
            $table->unsignedBigInteger('org_id')->index();
            $table->unsignedBigInteger('sales_deal_id');
            $table->unsignedBigInteger('from_stage_id')->nullable();
            $table->unsignedBigInteger('to_stage_id');
            $table->unsignedBigInteger('moved_by')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();

            $table->foreign('sales_deal_id', 'sdsh_deal_fk')
                ->references('id')->on('sales_deals')->cascadeOnDelete();
            $table->foreign('from_stage_id', 'sdsh_from_fk')
                ->references('id')->on('sales_pipeline_stages')->nullOnDelete();
            $table->foreign('to_stage_id', 'sdsh_to_fk')
                ->references('id')->on('sales_pipeline_stages')->cascadeOnDelete();
        });

        Schema::create('sales_activities', function (Blueprint $table) {
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

            $table->index(['subject_type', 'subject_id']);
            $table->index(['event_id', 'org_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_activities');
        Schema::dropIfExists('sales_deal_stage_histories');
        Schema::dropIfExists('sales_deal_notes');
        Schema::dropIfExists('sales_deal_contacts');
        Schema::dropIfExists('sales_deals');
    }
};
