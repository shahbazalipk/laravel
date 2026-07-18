<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_sequences', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('event_id');
            $table->unsignedBigInteger('org_id');
            $table->string('type', 32);
            $table->unsignedBigInteger('next_value')->default(1);
            $table->timestamps();

            $table->unique(['event_id', 'org_id', 'type'], 'project_sequences_type_unique');
        });

        Schema::create('project_teams', function (Blueprint $table): void {
            $table->id();
            $table->uuid('public_id')->unique();
            $table->unsignedBigInteger('event_id');
            $table->unsignedBigInteger('org_id');
            $table->string('name');
            $table->string('code', 64);
            $table->text('description')->nullable();
            $table->unsignedBigInteger('lead_admin_id')->nullable();
            $table->string('department')->nullable();
            $table->string('default_role', 64)->default('member');
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['event_id', 'org_id', 'code'], 'project_teams_code_unique');
            $table->index(['event_id', 'org_id', 'is_active'], 'project_teams_active');
        });

        Schema::create('project_team_members', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('event_id');
            $table->unsignedBigInteger('org_id');
            $table->unsignedBigInteger('team_id');
            $table->unsignedBigInteger('organization_admin_user_id');
            $table->string('role', 64)->default('member');
            $table->unsignedInteger('weekly_capacity_minutes')->default(2400);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('team_id')->references('id')->on('project_teams')->cascadeOnDelete();
            $table->unique(['team_id', 'organization_admin_user_id'], 'project_team_members_unique');
            $table->index(
                ['event_id', 'org_id', 'organization_admin_user_id', 'is_active'],
                'project_team_members_principal'
            );
        });

        Schema::create('project_projects', function (Blueprint $table): void {
            $table->id();
            $table->uuid('public_id')->unique();
            $table->unsignedBigInteger('event_id');
            $table->unsignedBigInteger('org_id');
            $table->string('number', 64);
            $table->string('key', 16);
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('type', 64)->default('event_operations');
            $table->unsignedBigInteger('owner_admin_id');
            $table->unsignedBigInteger('manager_admin_id')->nullable();
            $table->unsignedBigInteger('team_id')->nullable();
            $table->date('start_date')->nullable();
            $table->date('due_date')->nullable();
            $table->date('completed_at')->nullable();
            $table->string('priority', 16)->default('medium');
            $table->string('status', 32)->default('planned');
            $table->unsignedTinyInteger('progress_percent')->default(0);
            $table->decimal('budget_amount', 18, 4)->nullable();
            $table->string('currency', 3)->nullable();
            $table->json('tags')->nullable();
            $table->string('color', 32)->default('#4f46e5');
            $table->string('visibility', 32)->default('members');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('team_id')->references('id')->on('project_teams')->restrictOnDelete();
            $table->unique(['event_id', 'org_id', 'number'], 'project_projects_number_unique');
            $table->unique(['event_id', 'org_id', 'key'], 'project_projects_key_unique');
            $table->index(['event_id', 'org_id', 'status', 'due_date'], 'project_projects_status_due');
            $table->index(['event_id', 'org_id', 'manager_admin_id'], 'project_projects_manager');
        });

        Schema::create('project_members', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('event_id');
            $table->unsignedBigInteger('org_id');
            $table->unsignedBigInteger('project_id');
            $table->unsignedBigInteger('organization_admin_user_id');
            $table->string('role', 64)->default('member');
            $table->boolean('can_view_financials')->default(false);
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('added_by')->nullable();
            $table->timestamps();

            $table->foreign('project_id')->references('id')->on('project_projects')->cascadeOnDelete();
            $table->unique(['project_id', 'organization_admin_user_id'], 'project_members_unique');
            $table->index(
                ['event_id', 'org_id', 'organization_admin_user_id', 'is_active'],
                'project_members_principal'
            );
        });

        Schema::create('project_boards', function (Blueprint $table): void {
            $table->id();
            $table->uuid('public_id')->unique();
            $table->unsignedBigInteger('event_id');
            $table->unsignedBigInteger('org_id');
            $table->unsignedBigInteger('project_id');
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('is_default')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('project_id')->references('id')->on('project_projects')->cascadeOnDelete();
            $table->unique(['project_id', 'name'], 'project_boards_name_unique');
            $table->index(['event_id', 'org_id', 'project_id', 'sort_order'], 'project_boards_order');
        });

        Schema::create('project_board_columns', function (Blueprint $table): void {
            $table->id();
            $table->uuid('public_id')->unique();
            $table->unsignedBigInteger('event_id');
            $table->unsignedBigInteger('org_id');
            $table->unsignedBigInteger('board_id');
            $table->string('name');
            $table->string('slug', 64);
            $table->string('status', 32);
            $table->string('color', 32)->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->unsignedInteger('work_in_progress_limit')->nullable();
            $table->boolean('is_terminal')->default(false);
            $table->json('transition_rules')->nullable();
            $table->timestamps();

            $table->foreign('board_id')->references('id')->on('project_boards')->cascadeOnDelete();
            $table->unique(['board_id', 'slug'], 'project_board_columns_slug_unique');
            $table->index(['board_id', 'sort_order'], 'project_board_columns_order');
        });

        Schema::create('project_tasks', function (Blueprint $table): void {
            $table->id();
            $table->uuid('public_id')->unique();
            $table->unsignedBigInteger('event_id');
            $table->unsignedBigInteger('org_id');
            $table->unsignedBigInteger('project_id');
            $table->unsignedBigInteger('board_id');
            $table->unsignedBigInteger('column_id');
            $table->unsignedBigInteger('parent_task_id')->nullable();
            $table->string('number', 64);
            $table->string('key', 32);
            $table->string('title');
            $table->longText('description')->nullable();
            $table->string('status', 32);
            $table->string('priority', 16)->default('medium');
            $table->string('type', 32)->default('task');
            $table->unsignedBigInteger('reporter_admin_id');
            $table->unsignedBigInteger('team_id')->nullable();
            $table->date('start_date')->nullable();
            $table->date('due_date')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->unsignedInteger('estimated_minutes')->nullable();
            $table->unsignedInteger('logged_minutes')->default(0);
            $table->unsignedTinyInteger('progress_percent')->default(0);
            $table->json('labels')->nullable();
            $table->string('location')->nullable();
            $table->text('blocking_reason')->nullable();
            $table->unsignedInteger('position')->default(0);
            $table->unsignedInteger('version')->default(1);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('project_id')->references('id')->on('project_projects')->cascadeOnDelete();
            $table->foreign('board_id')->references('id')->on('project_boards')->cascadeOnDelete();
            $table->foreign('column_id')->references('id')->on('project_board_columns')->restrictOnDelete();
            $table->foreign('parent_task_id')->references('id')->on('project_tasks')->restrictOnDelete();
            $table->foreign('team_id')->references('id')->on('project_teams')->restrictOnDelete();
            $table->unique(['event_id', 'org_id', 'number'], 'project_tasks_number_unique');
            $table->unique(['project_id', 'key'], 'project_tasks_key_unique');
            $table->index(['event_id', 'org_id', 'project_id', 'column_id', 'position'], 'project_tasks_board');
            $table->index(['event_id', 'org_id', 'status', 'due_date'], 'project_tasks_status_due');
            $table->index(['parent_task_id', 'position'], 'project_tasks_parent');
        });

        Schema::create('project_task_assignees', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('event_id');
            $table->unsignedBigInteger('org_id');
            $table->unsignedBigInteger('task_id');
            $table->unsignedBigInteger('organization_admin_user_id');
            $table->boolean('is_primary')->default(false);
            $table->timestamp('assigned_at');
            $table->unsignedBigInteger('assigned_by')->nullable();

            $table->foreign('task_id')->references('id')->on('project_tasks')->cascadeOnDelete();
            $table->unique(['task_id', 'organization_admin_user_id'], 'project_task_assignees_unique');
            $table->index(
                ['event_id', 'org_id', 'organization_admin_user_id', 'task_id'],
                'project_task_assignees_principal'
            );
        });

        Schema::create('project_task_checklist_items', function (Blueprint $table): void {
            $table->id();
            $table->uuid('public_id')->unique();
            $table->unsignedBigInteger('event_id');
            $table->unsignedBigInteger('org_id');
            $table->unsignedBigInteger('task_id');
            $table->string('text');
            $table->boolean('is_required')->default(false);
            $table->boolean('is_completed')->default(false);
            $table->unsignedBigInteger('assigned_admin_id')->nullable();
            $table->date('due_date')->nullable();
            $table->unsignedBigInteger('completed_by')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->foreign('task_id')->references('id')->on('project_tasks')->cascadeOnDelete();
            $table->index(['task_id', 'sort_order'], 'project_task_checklist_order');
        });

        Schema::create('project_task_comments', function (Blueprint $table): void {
            $table->id();
            $table->uuid('public_id')->unique();
            $table->unsignedBigInteger('event_id');
            $table->unsignedBigInteger('org_id');
            $table->unsignedBigInteger('task_id');
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->unsignedBigInteger('author_admin_id');
            $table->longText('body');
            $table->json('mentioned_admin_ids')->nullable();
            $table->boolean('is_internal')->default(false);
            $table->boolean('is_pinned')->default(false);
            $table->timestamp('edited_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('task_id')->references('id')->on('project_tasks')->cascadeOnDelete();
            $table->foreign('parent_id')->references('id')->on('project_task_comments')->cascadeOnDelete();
            $table->index(['task_id', 'created_at'], 'project_task_comments_timeline');
        });

        Schema::create('project_task_status_histories', function (Blueprint $table): void {
            $table->id();
            $table->uuid('public_id')->unique();
            $table->unsignedBigInteger('event_id');
            $table->unsignedBigInteger('org_id');
            $table->unsignedBigInteger('task_id');
            $table->unsignedBigInteger('from_column_id')->nullable();
            $table->unsignedBigInteger('to_column_id');
            $table->unsignedBigInteger('actor_admin_id')->nullable();
            $table->text('reason')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('task_id')->references('id')->on('project_tasks')->cascadeOnDelete();
            $table->foreign('from_column_id')->references('id')->on('project_board_columns')->nullOnDelete();
            $table->foreign('to_column_id')->references('id')->on('project_board_columns')->restrictOnDelete();
            $table->index(['task_id', 'created_at'], 'project_task_status_history_timeline');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_task_status_histories');
        Schema::dropIfExists('project_task_comments');
        Schema::dropIfExists('project_task_checklist_items');
        Schema::dropIfExists('project_task_assignees');
        Schema::dropIfExists('project_tasks');
        Schema::dropIfExists('project_board_columns');
        Schema::dropIfExists('project_boards');
        Schema::dropIfExists('project_members');
        Schema::dropIfExists('project_projects');
        Schema::dropIfExists('project_team_members');
        Schema::dropIfExists('project_teams');
        Schema::dropIfExists('project_sequences');
    }
};
