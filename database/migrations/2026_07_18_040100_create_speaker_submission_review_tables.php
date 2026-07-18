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

        Schema::create('submission_reviewers', function (Blueprint $table) use ($tenant, $tenantIndex) {
            $tenant($table);
            $table->unsignedBigInteger('admin_id')->nullable();
            $table->foreignId('portal_user_id')->nullable()->constrained('submission_portal_users')->nullOnDelete();
            $table->string('name');
            $table->string('email');
            $table->string('status', 24)->default('invited');
            $table->unsignedInteger('capacity')->nullable();
            $table->json('settings')->nullable();
            $table->timestamp('invited_at')->nullable();
            $table->timestamp('accepted_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['event_id', 'org_id', 'email'], 'sub_reviewers_tenant_email_uq');
            $tenantIndex($table, 'reviewers');
        });

        Schema::create('submission_reviewer_expertise', function (Blueprint $table) use ($tenant, $tenantIndex) {
            $tenant($table);
            $table->foreignId('reviewer_id')->constrained('submission_reviewers')->cascadeOnDelete();
            $table->string('topic');
            $table->unsignedTinyInteger('level')->default(1);
            $table->json('settings')->nullable();
            $table->timestamps();
            $table->unique(['reviewer_id', 'topic'], 'sub_expertise_reviewer_topic_uq');
            $tenantIndex($table, 'expertise');
        });

        Schema::create('submission_reviewer_conflicts', function (Blueprint $table) use ($tenant, $tenantIndex) {
            $tenant($table);
            $table->foreignId('reviewer_id')->constrained('submission_reviewers')->cascadeOnDelete();
            $table->foreignId('submission_id')->constrained('submissions')->cascadeOnDelete();
            $table->string('type', 32)->default('declared');
            $table->text('reason')->nullable();
            $table->string('status', 24)->default('active');
            $table->json('settings')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['reviewer_id', 'submission_id'], 'sub_conflicts_reviewer_submission_uq');
            $tenantIndex($table, 'conflicts');
        });

        Schema::create('submission_scorecards', function (Blueprint $table) use ($tenant, $tenantIndex) {
            $tenant($table);
            $table->foreignId('submission_type_id')->constrained('submission_types')->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('status', 24)->default('draft');
            $table->decimal('passing_score', 8, 2)->nullable();
            $table->json('settings')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['submission_type_id', 'status'], 'sub_scorecards_type_status_idx');
            $tenantIndex($table, 'scorecards');
        });

        Schema::create('submission_review_criteria', function (Blueprint $table) use ($tenant, $tenantIndex) {
            $tenant($table);
            $table->foreignId('scorecard_id')->constrained('submission_scorecards')->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('type', 24)->default('rating');
            $table->decimal('weight', 8, 3)->default(1);
            $table->decimal('min_score', 8, 2)->default(0);
            $table->decimal('max_score', 8, 2)->default(5);
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_required')->default(true);
            $table->json('settings')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['scorecard_id', 'sort_order'], 'sub_criteria_scorecard_sort_idx');
            $tenantIndex($table, 'criteria');
        });

        Schema::create('submission_review_assignments', function (Blueprint $table) use ($tenant, $tenantIndex) {
            $tenant($table);
            $table->foreignId('submission_id')->constrained('submissions')->cascadeOnDelete();
            $table->foreignId('reviewer_id')->constrained('submission_reviewers')->cascadeOnDelete();
            $table->foreignId('scorecard_id')->nullable()->constrained('submission_scorecards')->nullOnDelete();
            $table->string('status', 24)->default('assigned');
            $table->timestamp('due_at')->nullable();
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->json('settings')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['submission_id', 'reviewer_id'], 'sub_assignments_submission_reviewer_uq');
            $table->index(['reviewer_id', 'status', 'due_at'], 'sub_assignments_reviewer_queue_idx');
            $tenantIndex($table, 'assignments');
        });

        Schema::create('submission_reviews', function (Blueprint $table) use ($tenant, $tenantIndex) {
            $tenant($table);
            $table->foreignId('review_assignment_id')->constrained('submission_review_assignments')->cascadeOnDelete();
            $table->string('status', 24)->default('draft');
            $table->decimal('total_score', 10, 3)->nullable();
            $table->string('recommendation', 32)->nullable();
            $table->text('summary')->nullable();
            $table->json('settings')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['review_assignment_id', 'status'], 'sub_reviews_assignment_status_idx');
            $tenantIndex($table, 'reviews');
        });

        Schema::create('submission_review_answers', function (Blueprint $table) use ($tenant, $tenantIndex) {
            $tenant($table);
            $table->foreignId('review_id')->constrained('submission_reviews')->cascadeOnDelete();
            $table->foreignId('criterion_id')->constrained('submission_review_criteria')->restrictOnDelete();
            $table->decimal('score', 8, 2)->nullable();
            $table->json('value')->nullable();
            $table->text('comment')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['review_id', 'criterion_id'], 'sub_review_answers_review_criterion_uq');
            $tenantIndex($table, 'review_answers');
        });

        Schema::create('submission_review_comments', function (Blueprint $table) use ($tenant, $tenantIndex) {
            $tenant($table);
            $table->foreignId('review_id')->constrained('submission_reviews')->cascadeOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('submission_review_comments')->cascadeOnDelete();
            $table->string('author_type', 32);
            $table->unsignedBigInteger('author_id');
            $table->text('body');
            $table->boolean('is_private')->default(true);
            $table->json('mentions')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['review_id', 'created_at'], 'sub_review_comments_timeline_idx');
            $tenantIndex($table, 'review_comments');
        });

        Schema::create('submission_decisions', function (Blueprint $table) use ($tenant, $tenantIndex) {
            $tenant($table);
            $table->foreignId('submission_id')->constrained('submissions')->cascadeOnDelete();
            $table->string('decision', 32);
            $table->string('status', 24)->default('draft');
            $table->text('reason')->nullable();
            $table->unsignedBigInteger('decided_by')->nullable();
            $table->timestamp('decided_at')->nullable();
            $table->timestamp('notified_at')->nullable();
            $table->json('settings')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['submission_id', 'status'], 'sub_decisions_submission_status_idx');
            $tenantIndex($table, 'decisions');
        });

        Schema::create('submission_revision_requests', function (Blueprint $table) use ($tenant, $tenantIndex) {
            $tenant($table);
            $table->foreignId('submission_id')->constrained('submissions')->cascadeOnDelete();
            $table->foreignId('decision_id')->nullable()->constrained('submission_decisions')->nullOnDelete();
            $table->string('status', 24)->default('requested');
            $table->text('instructions');
            $table->json('required_question_ids')->nullable();
            $table->timestamp('due_at')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->unsignedInteger('revision_number')->default(1);
            $table->json('answer_snapshot')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['submission_id', 'status'], 'sub_revisions_submission_status_idx');
            $tenantIndex($table, 'revisions');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('submission_revision_requests');
        Schema::dropIfExists('submission_decisions');
        Schema::dropIfExists('submission_review_comments');
        Schema::dropIfExists('submission_review_answers');
        Schema::dropIfExists('submission_reviews');
        Schema::dropIfExists('submission_review_assignments');
        Schema::dropIfExists('submission_review_criteria');
        Schema::dropIfExists('submission_scorecards');
        Schema::dropIfExists('submission_reviewer_conflicts');
        Schema::dropIfExists('submission_reviewer_expertise');
        Schema::dropIfExists('submission_reviewers');
    }
};
