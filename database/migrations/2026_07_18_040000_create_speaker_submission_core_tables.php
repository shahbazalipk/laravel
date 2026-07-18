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

        Schema::create('submission_types', function (Blueprint $table) use ($tenant, $tenantIndex) {
            $tenant($table);
            $table->string('name');
            $table->string('code', 30);
            $table->string('slug');
            $table->text('description')->nullable();
            $table->string('public_title')->nullable();
            $table->longText('instructions')->nullable();
            $table->string('category', 32)->default('abstract');
            $table->string('status', 24)->default('draft');
            $table->json('settings')->nullable();
            $table->timestamp('opens_at')->nullable();
            $table->timestamp('closes_at')->nullable();
            $table->string('timezone')->default('UTC');
            $table->unsignedInteger('maximum_submissions_per_applicant')->nullable();
            $table->boolean('allow_drafts')->default(true);
            $table->boolean('allow_editing_after_submission')->default(false);
            $table->boolean('allow_anonymous_review')->default(false);
            $table->boolean('enable_scoring')->default(true);
            $table->boolean('enable_revisions')->default(true);
            $table->boolean('enable_speaker_onboarding')->default(true);
            $table->foreignId('default_stage_id')->nullable();
            $table->string('number_prefix', 12)->nullable();
            $table->string('number_pattern')->default('{PREFIX}-{YEAR}-{NUMBER:4}');
            $table->unsignedBigInteger('number_sequence')->default(0);
            $table->unsignedInteger('published_form_version')->default(0);
            $table->timestamp('published_at')->nullable();
            $table->text('success_message')->nullable();
            $table->text('closed_message')->nullable();
            $table->longText('terms')->nullable();
            $table->longText('privacy_consent')->nullable();
            $table->json('decision_rules')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['event_id', 'org_id', 'slug'], 'sub_types_tenant_slug_uq');
            $table->unique(['event_id', 'org_id', 'code'], 'sub_types_tenant_code_uq');
            $tenantIndex($table, 'types');
        });

        Schema::create('submission_schema_versions', function (Blueprint $table) use ($tenant, $tenantIndex) {
            $tenant($table);
            $table->foreignId('submission_type_id')->constrained('submission_types')->cascadeOnDelete();
            $table->unsignedInteger('version');
            $table->string('status', 24)->default('draft');
            $table->json('schema_snapshot')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->unsignedBigInteger('published_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['submission_type_id', 'version'], 'sub_schema_type_version_uq');
            $tenantIndex($table, 'schemas');
        });

        Schema::create('submission_form_sections', function (Blueprint $table) use ($tenant, $tenantIndex) {
            $tenant($table);
            $table->foreignId('submission_type_id')->constrained('submission_types')->cascadeOnDelete();
            $table->foreignId('schema_version_id')->nullable()->constrained('submission_schema_versions')->nullOnDelete();
            $table->string('title');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->json('settings')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['submission_type_id', 'slug'], 'sub_sections_type_slug_uq');
            $table->index(['submission_type_id', 'sort_order'], 'sub_sections_type_sort_idx');
            $tenantIndex($table, 'sections');
        });

        Schema::create('submission_questions', function (Blueprint $table) use ($tenant, $tenantIndex) {
            $tenant($table);
            $table->foreignId('form_section_id')->constrained('submission_form_sections')->cascadeOnDelete();
            $table->string('key');
            $table->string('label');
            $table->string('type', 32);
            $table->text('help_text')->nullable();
            $table->string('placeholder')->nullable();
            $table->boolean('is_required')->default(false);
            $table->boolean('is_active')->default(true);
            $table->boolean('allow_reviewer_access')->default(true);
            $table->boolean('hidden_during_anonymous_review')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->json('validation')->nullable();
            $table->json('settings')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['form_section_id', 'key'], 'sub_questions_section_key_uq');
            $table->index(['form_section_id', 'sort_order'], 'sub_questions_section_sort_idx');
            $tenantIndex($table, 'questions');
        });

        Schema::create('submission_question_options', function (Blueprint $table) use ($tenant, $tenantIndex) {
            $tenant($table);
            $table->foreignId('question_id')->constrained('submission_questions')->cascadeOnDelete();
            $table->string('label');
            $table->string('value');
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->json('settings')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['question_id', 'value'], 'sub_options_question_value_uq');
            $tenantIndex($table, 'options');
        });

        Schema::create('submission_conditional_rules', function (Blueprint $table) use ($tenant, $tenantIndex) {
            $tenant($table);
            $table->foreignId('submission_type_id')->constrained('submission_types')->cascadeOnDelete();
            $table->foreignId('target_question_id')->nullable()->constrained('submission_questions')->cascadeOnDelete();
            $table->foreignId('source_question_id')->constrained('submission_questions')->cascadeOnDelete();
            $table->string('operator', 32);
            $table->string('action', 24);
            $table->json('compare_value')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->json('settings')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $tenantIndex($table, 'rules');
        });

        Schema::create('submission_workflow_stages', function (Blueprint $table) use ($tenant, $tenantIndex) {
            $tenant($table);
            $table->foreignId('submission_type_id')->constrained('submission_types')->cascadeOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->string('category', 24)->default('draft');
            $table->string('color', 32)->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_initial')->default(false);
            $table->boolean('is_terminal')->default(false);
            $table->boolean('is_active')->default(true);
            $table->json('settings')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['submission_type_id', 'slug'], 'sub_stages_type_slug_uq');
            $table->index(['submission_type_id', 'sort_order'], 'sub_stages_type_sort_idx');
            $tenantIndex($table, 'stages');
        });

        Schema::create('submission_stage_transitions', function (Blueprint $table) use ($tenant, $tenantIndex) {
            $tenant($table);
            $table->foreignId('submission_type_id')->constrained('submission_types')->cascadeOnDelete();
            $table->foreignId('from_stage_id')->constrained('submission_workflow_stages')->cascadeOnDelete();
            $table->foreignId('to_stage_id')->constrained('submission_workflow_stages')->cascadeOnDelete();
            $table->string('name')->nullable();
            $table->boolean('is_automatic')->default(false);
            $table->boolean('is_active')->default(true);
            $table->json('conditions')->nullable();
            $table->json('settings')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['submission_type_id', 'from_stage_id', 'to_stage_id'], 'sub_transitions_path_uq');
            $tenantIndex($table, 'transitions');
        });

        Schema::create('submission_portal_users', function (Blueprint $table) use ($tenant, $tenantIndex) {
            $tenant($table);
            $table->string('name');
            $table->string('email');
            $table->string('phone')->nullable();
            $table->string('password')->nullable();
            $table->string('status', 24)->default('pending');
            $table->timestamp('email_verified_at')->nullable();
            $table->timestamp('last_login_at')->nullable();
            $table->rememberToken();
            $table->json('settings')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['event_id', 'org_id', 'email'], 'sub_portal_users_email_uq');
            $tenantIndex($table, 'portal_users');
        });

        Schema::create('submission_portal_tokens', function (Blueprint $table) use ($tenant, $tenantIndex) {
            $tenant($table);
            $table->foreignId('portal_user_id')->constrained('submission_portal_users')->cascadeOnDelete();
            $table->string('type', 32);
            $table->string('token_hash', 64)->unique();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('used_at')->nullable();
            $table->timestamps();
            $table->index(['portal_user_id', 'type', 'expires_at'], 'sub_portal_tokens_lookup_idx');
            $tenantIndex($table, 'portal_tokens');
        });

        Schema::create('submissions', function (Blueprint $table) use ($tenant, $tenantIndex) {
            $tenant($table);
            $table->foreignId('submission_type_id')->constrained('submission_types')->restrictOnDelete();
            $table->foreignId('schema_version_id')->nullable()->constrained('submission_schema_versions')->nullOnDelete();
            $table->foreignId('portal_user_id')->nullable()->constrained('submission_portal_users')->nullOnDelete();
            $table->foreignId('current_stage_id')->nullable()->constrained('submission_workflow_stages')->nullOnDelete();
            $table->string('reference')->nullable();
            $table->string('title');
            $table->string('status', 32)->default('draft');
            $table->text('abstract')->nullable();
            $table->boolean('is_draft')->default(true);
            $table->boolean('is_withdrawn')->default(false);
            $table->boolean('is_selected')->default(false);
            $table->string('final_decision', 32)->nullable();
            $table->timestamp('decision_at')->nullable();
            $table->json('settings')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('withdrawn_at')->nullable();
            $table->timestamp('last_activity_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['event_id', 'org_id', 'reference'], 'submissions_tenant_reference_uq');
            $table->index(['event_id', 'org_id', 'status'], 'submissions_tenant_status_idx');
            $table->index(['submission_type_id', 'current_stage_id'], 'submissions_type_stage_idx');
            $tenantIndex($table, 'records');
        });

        Schema::create('submission_answers', function (Blueprint $table) use ($tenant, $tenantIndex) {
            $tenant($table);
            $table->foreignId('submission_id')->constrained('submissions')->cascadeOnDelete();
            $table->foreignId('question_id')->constrained('submission_questions')->restrictOnDelete();
            $table->json('value')->nullable();
            $table->text('search_value')->nullable();
            $table->string('question_key');
            $table->string('question_label');
            $table->string('question_type', 32);
            $table->longText('answer_text')->nullable();
            $table->json('answer_json')->nullable();
            $table->text('normalized_value')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['submission_id', 'question_id'], 'sub_answers_submission_question_uq');
            $tenantIndex($table, 'answers');
        });

        Schema::create('submission_people', function (Blueprint $table) use ($tenant, $tenantIndex) {
            $tenant($table);
            $table->foreignId('submission_id')->constrained('submissions')->cascadeOnDelete();
            $table->foreignId('portal_user_id')->nullable()->constrained('submission_portal_users')->nullOnDelete();
            $table->string('role', 32)->default('speaker');
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('organization')->nullable();
            $table->boolean('is_primary')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->json('profile')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['submission_id', 'role', 'sort_order'], 'sub_people_role_sort_idx');
            $tenantIndex($table, 'people');
        });

        Schema::create('submission_stage_histories', function (Blueprint $table) use ($tenant, $tenantIndex) {
            $tenant($table);
            $table->foreignId('submission_id')->constrained('submissions')->cascadeOnDelete();
            $table->foreignId('from_stage_id')->nullable()->constrained('submission_workflow_stages')->nullOnDelete();
            $table->foreignId('to_stage_id')->constrained('submission_workflow_stages')->restrictOnDelete();
            $table->string('actor_type', 32)->nullable();
            $table->unsignedBigInteger('actor_id')->nullable();
            $table->text('reason')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->index(['submission_id', 'created_at'], 'sub_stage_history_timeline_idx');
            $tenantIndex($table, 'stage_history');
        });

        Schema::create('submission_tags', function (Blueprint $table) use ($tenant, $tenantIndex) {
            $tenant($table);
            $table->string('name');
            $table->string('slug');
            $table->string('color', 32)->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['event_id', 'org_id', 'slug'], 'sub_tags_tenant_slug_uq');
            $tenantIndex($table, 'tags');
        });

        Schema::create('submission_tag_links', function (Blueprint $table) use ($tenant, $tenantIndex) {
            $tenant($table);
            $table->foreignId('submission_id')->constrained('submissions')->cascadeOnDelete();
            $table->foreignId('tag_id')->constrained('submission_tags')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['submission_id', 'tag_id'], 'sub_tag_links_pair_uq');
            $tenantIndex($table, 'tag_links');
        });

        Schema::create('submission_notes', function (Blueprint $table) use ($tenant, $tenantIndex) {
            $tenant($table);
            $table->foreignId('submission_id')->constrained('submissions')->cascadeOnDelete();
            $table->unsignedBigInteger('author_id')->nullable();
            $table->text('body');
            $table->boolean('is_private')->default(true);
            $table->json('mentions')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['submission_id', 'created_at'], 'sub_notes_timeline_idx');
            $tenantIndex($table, 'notes');
        });

        Schema::create('submission_files', function (Blueprint $table) use ($tenant, $tenantIndex) {
            $tenant($table);
            $table->foreignId('submission_id')->constrained('submissions')->cascadeOnDelete();
            $table->foreignId('answer_id')->nullable()->constrained('submission_answers')->nullOnDelete();
            $table->string('category', 32)->default('attachment');
            $table->string('disk');
            $table->string('path');
            $table->string('original_name');
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('size')->default(0);
            $table->json('settings')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['submission_id', 'category'], 'sub_files_category_idx');
            $tenantIndex($table, 'files');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('submission_files');
        Schema::dropIfExists('submission_notes');
        Schema::dropIfExists('submission_tag_links');
        Schema::dropIfExists('submission_tags');
        Schema::dropIfExists('submission_stage_histories');
        Schema::dropIfExists('submission_people');
        Schema::dropIfExists('submission_answers');
        Schema::dropIfExists('submissions');
        Schema::dropIfExists('submission_portal_tokens');
        Schema::dropIfExists('submission_portal_users');
        Schema::dropIfExists('submission_stage_transitions');
        Schema::dropIfExists('submission_workflow_stages');
        Schema::dropIfExists('submission_conditional_rules');
        Schema::dropIfExists('submission_question_options');
        Schema::dropIfExists('submission_questions');
        Schema::dropIfExists('submission_form_sections');
        Schema::dropIfExists('submission_schema_versions');
        Schema::dropIfExists('submission_types');
    }
};
