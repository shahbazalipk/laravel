<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_task_dependencies', function (Blueprint $table): void {
            $table->id();
            $table->uuid('public_id')->unique();
            $table->unsignedBigInteger('event_id');
            $table->unsignedBigInteger('org_id');
            $table->unsignedBigInteger('task_id');
            $table->unsignedBigInteger('depends_on_task_id');
            $table->string('type', 32);
            $table->integer('lag_days')->default(0);
            $table->boolean('is_enforced')->default(true);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->foreign('task_id')->references('id')->on('project_tasks')->cascadeOnDelete();
            $table->foreign('depends_on_task_id')->references('id')->on('project_tasks')->cascadeOnDelete();
            $table->unique(['task_id', 'depends_on_task_id', 'type'], 'project_task_dependencies_unique');
            $table->index(['event_id', 'org_id', 'depends_on_task_id'], 'project_task_dependencies_blocker');
        });

        Schema::create('project_recurrence_rules', function (Blueprint $table): void {
            $table->id();
            $table->uuid('public_id')->unique();
            $table->unsignedBigInteger('event_id');
            $table->unsignedBigInteger('org_id');
            $table->unsignedBigInteger('source_task_id');
            $table->string('frequency', 16);
            $table->unsignedInteger('interval')->default(1);
            $table->json('weekdays')->nullable();
            $table->date('starts_on');
            $table->date('ends_on')->nullable();
            $table->unsignedInteger('max_occurrences')->nullable();
            $table->unsignedInteger('generated_occurrences')->default(0);
            $table->date('next_occurrence_on');
            $table->date('last_generated_on')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('source_task_id')->references('id')->on('project_tasks')->cascadeOnDelete();
            $table->index(['event_id', 'org_id', 'is_active', 'next_occurrence_on'], 'project_recurrence_rules_due');
        });

        Schema::create('project_templates', function (Blueprint $table): void {
            $table->id();
            $table->uuid('public_id')->unique();
            $table->unsignedBigInteger('event_id');
            $table->unsignedBigInteger('org_id');
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('category', 64)->nullable();
            $table->json('definition');
            $table->unsignedInteger('task_count')->default(0);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_shared')->default(false);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['event_id', 'org_id', 'name'], 'project_templates_name_unique');
            $table->index(['event_id', 'org_id', 'is_active', 'category'], 'project_templates_lookup');
        });

        Schema::create('project_saved_filters', function (Blueprint $table): void {
            $table->id();
            $table->uuid('public_id')->unique();
            $table->unsignedBigInteger('event_id');
            $table->unsignedBigInteger('org_id');
            $table->unsignedBigInteger('organization_admin_user_id');
            $table->string('name');
            $table->string('view', 32);
            $table->json('criteria');
            $table->json('visible_columns')->nullable();
            $table->string('visibility', 16)->default('private');
            $table->boolean('is_default')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(
                ['event_id', 'org_id', 'organization_admin_user_id', 'name', 'view'],
                'project_saved_filters_name_unique'
            );
            $table->index(['event_id', 'org_id', 'view', 'visibility'], 'project_saved_filters_lookup');
        });

        Schema::create('project_guests', function (Blueprint $table): void {
            $table->id();
            $table->uuid('public_id')->unique();
            $table->unsignedBigInteger('event_id');
            $table->unsignedBigInteger('org_id');
            $table->string('name');
            $table->string('email');
            $table->string('type', 32);
            $table->string('organization_name')->nullable();
            $table->string('token_hash', 64)->nullable();
            $table->timestamp('invitation_expires_at')->nullable();
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('last_accessed_at')->nullable();
            $table->string('status', 16)->default('invited');
            $table->unsignedBigInteger('invited_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['event_id', 'org_id', 'email'], 'project_guests_email_unique');
            $table->index(['event_id', 'org_id', 'status'], 'project_guests_status');
        });

        Schema::create('project_guest_access', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('event_id');
            $table->unsignedBigInteger('org_id');
            $table->unsignedBigInteger('guest_id');
            $table->unsignedBigInteger('project_id');
            $table->unsignedBigInteger('task_id')->nullable();
            $table->string('role', 32)->default('viewer');
            $table->json('abilities');
            $table->timestamp('expires_at')->nullable();
            $table->unsignedBigInteger('granted_by')->nullable();
            $table->timestamps();

            $table->foreign('guest_id')->references('id')->on('project_guests')->cascadeOnDelete();
            $table->foreign('project_id')->references('id')->on('project_projects')->cascadeOnDelete();
            $table->foreign('task_id')->references('id')->on('project_tasks')->cascadeOnDelete();
            $table->unique(['guest_id', 'project_id', 'task_id'], 'project_guest_access_unique');
            $table->index(['event_id', 'org_id', 'project_id', 'expires_at'], 'project_guest_access_project');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_guest_access');
        Schema::dropIfExists('project_guests');
        Schema::dropIfExists('project_saved_filters');
        Schema::dropIfExists('project_templates');
        Schema::dropIfExists('project_recurrence_rules');
        Schema::dropIfExists('project_task_dependencies');
    }
};
