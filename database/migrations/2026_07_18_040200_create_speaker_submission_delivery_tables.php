<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tenant = static function (Blueprint $table): void {
            $table->id();
            $table->uuid('public_id')->unique();
            $table->unsignedBigInteger('event_id');
            $table->unsignedBigInteger('org_id');
        };

        $tenantIndex = static function (Blueprint $table, string $suffix): void {
            $table->index(['event_id', 'org_id'], "sub_{$suffix}_tenant_idx");
        };

        Schema::create('speaker_submission_links', function (Blueprint $table) use ($tenant, $tenantIndex) {
            $tenant($table);
            $table->foreignId('submission_id')->constrained('submissions')->cascadeOnDelete();
            $table->foreignId('submission_person_id')->nullable()->constrained('submission_people')->nullOnDelete();
            $table->unsignedBigInteger('speaker_id');
            $table->string('status', 24)->default('linked');
            $table->unsignedBigInteger('linked_by')->nullable();
            $table->timestamp('linked_at')->nullable();
            $table->json('settings')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['submission_id', 'speaker_id'], 'speaker_sub_links_pair_uq');
            $table->index(['speaker_id', 'status'], 'speaker_sub_links_speaker_status_idx');
            $tenantIndex($table, 'speaker_links');
        });

        Schema::create('speaker_onboarding', function (Blueprint $table) use ($tenant, $tenantIndex) {
            $tenant($table);
            $table->foreignId('speaker_submission_link_id')->constrained('speaker_submission_links')->cascadeOnDelete();
            $table->string('status', 24)->default('not_started');
            $table->unsignedTinyInteger('completion_percent')->default(0);
            $table->json('profile_data')->nullable();
            $table->json('settings')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->unique('speaker_submission_link_id', 'speaker_onboarding_link_uq');
            $tenantIndex($table, 'onboarding');
        });

        Schema::create('speaker_checklists', function (Blueprint $table) use ($tenant, $tenantIndex) {
            $tenant($table);
            $table->foreignId('submission_type_id')->nullable()->constrained('submission_types')->cascadeOnDelete();
            $table->string('name');
            $table->string('category', 32)->default('onboarding');
            $table->json('items');
            $table->boolean('is_active')->default(true);
            $table->json('settings')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['submission_type_id', 'is_active'], 'speaker_checklists_type_active_idx');
            $tenantIndex($table, 'checklists');
        });

        Schema::create('speaker_checklist_progress', function (Blueprint $table) use ($tenant, $tenantIndex) {
            $tenant($table);
            $table->foreignId('speaker_onboarding_id')->constrained('speaker_onboarding')->cascadeOnDelete();
            $table->foreignId('speaker_checklist_id')->constrained('speaker_checklists')->restrictOnDelete();
            $table->string('item_key');
            $table->string('status', 24)->default('pending');
            $table->json('response')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->unique(['speaker_onboarding_id', 'speaker_checklist_id', 'item_key'], 'speaker_check_progress_item_uq');
            $tenantIndex($table, 'check_progress');
        });

        Schema::create('speaker_contracts', function (Blueprint $table) use ($tenant, $tenantIndex) {
            $tenant($table);
            $table->foreignId('speaker_submission_link_id')->constrained('speaker_submission_links')->cascadeOnDelete();
            $table->string('status', 24)->default('draft');
            $table->string('document_path')->nullable();
            $table->string('signed_document_path')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('signed_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->json('terms')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['speaker_submission_link_id', 'status'], 'speaker_contracts_link_status_idx');
            $tenantIndex($table, 'contracts');
        });

        Schema::create('speaker_commercial_terms', function (Blueprint $table) use ($tenant, $tenantIndex) {
            $tenant($table);
            $table->foreignId('speaker_submission_link_id')->constrained('speaker_submission_links')->cascadeOnDelete();
            $table->string('currency', 3)->nullable();
            $table->decimal('fee_amount', 15, 2)->nullable();
            $table->decimal('expense_limit', 15, 2)->nullable();
            $table->string('payment_status', 24)->default('not_applicable');
            $table->json('terms')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->unique('speaker_submission_link_id', 'speaker_commercial_link_uq');
            $tenantIndex($table, 'commercial');
        });

        Schema::create('speaker_travel', function (Blueprint $table) use ($tenant, $tenantIndex) {
            $tenant($table);
            $table->foreignId('speaker_submission_link_id')->constrained('speaker_submission_links')->cascadeOnDelete();
            $table->string('status', 24)->default('not_required');
            $table->json('itinerary')->nullable();
            $table->json('preferences')->nullable();
            $table->json('documents')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['speaker_submission_link_id', 'status'], 'speaker_travel_link_status_idx');
            $tenantIndex($table, 'travel');
        });

        Schema::create('speaker_accommodations', function (Blueprint $table) use ($tenant, $tenantIndex) {
            $tenant($table);
            $table->foreignId('speaker_submission_link_id')->constrained('speaker_submission_links')->cascadeOnDelete();
            $table->string('status', 24)->default('not_required');
            $table->string('property_name')->nullable();
            $table->date('check_in')->nullable();
            $table->date('check_out')->nullable();
            $table->string('confirmation_number')->nullable();
            $table->json('details')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['speaker_submission_link_id', 'status'], 'speaker_accom_link_status_idx');
            $tenantIndex($table, 'accommodations');
        });

        Schema::create('speaker_presentations', function (Blueprint $table) use ($tenant, $tenantIndex) {
            $tenant($table);
            $table->foreignId('speaker_submission_link_id')->constrained('speaker_submission_links')->cascadeOnDelete();
            $table->string('status', 24)->default('pending');
            $table->string('title')->nullable();
            $table->string('disk')->nullable();
            $table->string('path')->nullable();
            $table->unsignedInteger('version')->default(1);
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->json('settings')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['speaker_submission_link_id', 'status'], 'speaker_presentations_link_status_idx');
            $tenantIndex($table, 'presentations');
        });

        Schema::create('submission_communication_templates', function (Blueprint $table) use ($tenant, $tenantIndex) {
            $tenant($table);
            $table->foreignId('submission_type_id')->nullable()->constrained('submission_types')->cascadeOnDelete();
            $table->string('name');
            $table->string('channel', 24)->default('email');
            $table->string('trigger', 64)->nullable();
            $table->string('subject')->nullable();
            $table->longText('body');
            $table->boolean('is_active')->default(true);
            $table->json('settings')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['submission_type_id', 'trigger', 'is_active'], 'sub_comm_templates_trigger_idx');
            $tenantIndex($table, 'comm_templates');
        });

        Schema::create('submission_communication_logs', function (Blueprint $table) use ($tenant, $tenantIndex) {
            $tenant($table);
            $table->foreignId('submission_id')->nullable()->constrained('submissions')->nullOnDelete();
            $table->foreignId('template_id')->nullable()->constrained('submission_communication_templates')->nullOnDelete();
            $table->string('channel', 24);
            $table->string('recipient');
            $table->string('subject')->nullable();
            $table->longText('body')->nullable();
            $table->string('status', 24)->default('queued');
            $table->string('provider_message_id')->nullable();
            $table->text('error')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->index(['submission_id', 'created_at'], 'sub_comm_logs_timeline_idx');
            $table->index(['event_id', 'org_id', 'status'], 'sub_comm_logs_tenant_status_idx');
            $tenantIndex($table, 'comm_logs');
        });

        Schema::create('submission_activities', function (Blueprint $table) use ($tenant, $tenantIndex) {
            $tenant($table);
            $table->foreignId('submission_id')->nullable()->constrained('submissions')->nullOnDelete();
            $table->string('subject_type')->nullable();
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->string('actor_type')->nullable();
            $table->unsignedBigInteger('actor_id')->nullable();
            $table->string('type', 64);
            $table->text('description')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->index(['submission_id', 'created_at'], 'sub_activities_timeline_idx');
            $table->index(['subject_type', 'subject_id'], 'sub_activities_subject_idx');
            $tenantIndex($table, 'activities');
        });

        Schema::create('submission_saved_filters', function (Blueprint $table) use ($tenant, $tenantIndex) {
            $tenant($table);
            $table->unsignedBigInteger('admin_id');
            $table->string('name');
            $table->string('scope', 32)->default('submissions');
            $table->json('filters');
            $table->json('columns')->nullable();
            $table->boolean('is_shared')->default(false);
            $table->boolean('is_default')->default(false);
            $table->timestamps();
            $table->softDeletes();
            $table->index(['admin_id', 'scope'], 'sub_saved_filters_admin_scope_idx');
            $tenantIndex($table, 'saved_filters');
        });

        Schema::create('submission_export_jobs', function (Blueprint $table) use ($tenant, $tenantIndex) {
            $tenant($table);
            $table->unsignedBigInteger('requested_by');
            $table->string('type', 32)->default('submissions');
            $table->string('format', 16)->default('csv');
            $table->string('status', 24)->default('queued');
            $table->json('filters')->nullable();
            $table->string('disk')->nullable();
            $table->string('path')->nullable();
            $table->unsignedInteger('row_count')->nullable();
            $table->text('error')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
            $table->index(['event_id', 'org_id', 'status'], 'sub_export_jobs_tenant_status_idx');
            $table->index(['requested_by', 'created_at'], 'sub_export_jobs_requester_idx');
            $tenantIndex($table, 'export_jobs');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('submission_export_jobs');
        Schema::dropIfExists('submission_saved_filters');
        Schema::dropIfExists('submission_activities');
        Schema::dropIfExists('submission_communication_logs');
        Schema::dropIfExists('submission_communication_templates');
        Schema::dropIfExists('speaker_presentations');
        Schema::dropIfExists('speaker_accommodations');
        Schema::dropIfExists('speaker_travel');
        Schema::dropIfExists('speaker_commercial_terms');
        Schema::dropIfExists('speaker_contracts');
        Schema::dropIfExists('speaker_checklist_progress');
        Schema::dropIfExists('speaker_checklists');
        Schema::dropIfExists('speaker_onboarding');
        Schema::dropIfExists('speaker_submission_links');
    }
};
