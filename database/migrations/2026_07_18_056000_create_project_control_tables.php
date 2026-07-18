<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_time_logs', function (Blueprint $table): void {
            $table->id();
            $table->uuid('public_id')->unique();
            $table->unsignedBigInteger('event_id');
            $table->unsignedBigInteger('org_id');
            $table->unsignedBigInteger('task_id');
            $table->unsignedBigInteger('organization_admin_user_id');
            $table->date('logged_on');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->unsignedInteger('duration_minutes')->default(0);
            $table->text('description')->nullable();
            $table->boolean('is_billable')->default(false);
            $table->string('approval_status', 16)->default('not_required');
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('task_id')->references('id')->on('project_tasks')->cascadeOnDelete();
            $table->index(['event_id', 'org_id', 'organization_admin_user_id', 'logged_on'], 'project_time_logs_timesheet');
            $table->index(['task_id', 'approval_status'], 'project_time_logs_task');
        });

        Schema::create('project_task_approval_requests', function (Blueprint $table): void {
            $table->id();
            $table->uuid('public_id')->unique();
            $table->unsignedBigInteger('event_id');
            $table->unsignedBigInteger('org_id');
            $table->unsignedBigInteger('task_id');
            $table->string('mode', 16);
            $table->string('status', 24)->default('pending');
            $table->text('instructions')->nullable();
            $table->unsignedBigInteger('requested_by');
            $table->timestamp('requested_at');
            $table->timestamp('completed_at')->nullable();
            $table->unsignedInteger('current_sequence')->default(1);
            $table->timestamps();

            $table->foreign('task_id')->references('id')->on('project_tasks')->cascadeOnDelete();
            $table->index(['event_id', 'org_id', 'status', 'requested_at'], 'project_task_approvals_queue');
        });

        Schema::create('project_task_approval_steps', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('event_id');
            $table->unsignedBigInteger('org_id');
            $table->unsignedBigInteger('approval_request_id');
            $table->unsignedBigInteger('approver_admin_id');
            $table->unsignedInteger('sequence')->default(1);
            $table->string('status', 24)->default('pending');
            $table->text('comments')->nullable();
            $table->timestamp('decided_at')->nullable();
            $table->timestamps();

            $table->foreign('approval_request_id')->references('id')->on('project_task_approval_requests')->cascadeOnDelete();
            $table->unique(['approval_request_id', 'approver_admin_id'], 'project_task_approval_steps_unique');
            $table->index(['event_id', 'org_id', 'approver_admin_id', 'status'], 'project_task_approval_steps_queue');
        });

        Schema::create('project_user_availability', function (Blueprint $table): void {
            $table->id();
            $table->uuid('public_id')->unique();
            $table->unsignedBigInteger('event_id');
            $table->unsignedBigInteger('org_id');
            $table->unsignedBigInteger('organization_admin_user_id');
            $table->date('starts_on');
            $table->date('ends_on');
            $table->boolean('is_available')->default(false);
            $table->unsignedInteger('available_minutes')->nullable();
            $table->string('reason')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(
                ['event_id', 'org_id', 'organization_admin_user_id', 'starts_on', 'ends_on'],
                'project_user_availability_range'
            );
        });

        Schema::create('project_risks', function (Blueprint $table): void {
            $table->id();
            $table->uuid('public_id')->unique();
            $table->unsignedBigInteger('event_id');
            $table->unsignedBigInteger('org_id');
            $table->unsignedBigInteger('project_id');
            $table->string('title');
            $table->text('description')->nullable();
            $table->unsignedTinyInteger('probability');
            $table->unsignedTinyInteger('impact');
            $table->unsignedTinyInteger('score');
            $table->unsignedBigInteger('owner_admin_id')->nullable();
            $table->text('mitigation_plan')->nullable();
            $table->date('due_date')->nullable();
            $table->string('status', 24)->default('open');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('project_id')->references('id')->on('project_projects')->cascadeOnDelete();
            $table->index(['event_id', 'org_id', 'status', 'score'], 'project_risks_register');
        });

        Schema::create('project_issues', function (Blueprint $table): void {
            $table->id();
            $table->uuid('public_id')->unique();
            $table->unsignedBigInteger('event_id');
            $table->unsignedBigInteger('org_id');
            $table->unsignedBigInteger('project_id');
            $table->unsignedBigInteger('task_id')->nullable();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('severity', 16);
            $table->unsignedBigInteger('owner_admin_id')->nullable();
            $table->text('resolution')->nullable();
            $table->date('due_date')->nullable();
            $table->string('status', 24)->default('open');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('project_id')->references('id')->on('project_projects')->cascadeOnDelete();
            $table->foreign('task_id')->references('id')->on('project_tasks')->nullOnDelete();
            $table->index(['event_id', 'org_id', 'status', 'severity'], 'project_issues_register');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_issues');
        Schema::dropIfExists('project_risks');
        Schema::dropIfExists('project_user_availability');
        Schema::dropIfExists('project_task_approval_steps');
        Schema::dropIfExists('project_task_approval_requests');
        Schema::dropIfExists('project_time_logs');
    }
};
