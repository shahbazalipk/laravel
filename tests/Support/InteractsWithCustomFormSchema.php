<?php

namespace Tests\Support;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

trait InteractsWithCustomFormSchema
{
    protected function createCustomFormTables(): void
    {
        Schema::create('custom_forms', function (Blueprint $table): void {
            $table->id();
            $table->uuid('public_id')->unique();
            $table->unsignedBigInteger('event_id')->index();
            $table->unsignedBigInteger('org_id')->index();
            $table->string('name');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->string('audience', 32);
            $table->string('audience_unique', 32)->nullable();
            $table->boolean('is_active')->default(true);
            $table->json('settings')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['event_id', 'org_id', 'slug']);
            $table->unique(['event_id', 'org_id', 'audience_unique'], 'custom_forms_event_org_audience_unique');
        });

        Schema::create('custom_form_questions', function (Blueprint $table): void {
            $table->id();
            $table->uuid('public_id')->unique();
            $table->unsignedBigInteger('event_id')->index();
            $table->unsignedBigInteger('org_id')->index();
            $table->foreignId('custom_form_id')->constrained()->cascadeOnDelete();
            $table->string('key');
            $table->string('label');
            $table->text('help_text')->nullable();
            $table->string('placeholder')->nullable();
            $table->string('type', 32);
            $table->boolean('is_required')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->json('validation')->nullable();
            $table->json('settings')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['custom_form_id', 'key']);
        });

        Schema::create('custom_form_question_options', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('event_id')->index();
            $table->unsignedBigInteger('org_id')->index();
            $table->foreignId('custom_form_question_id')->constrained()->cascadeOnDelete();
            $table->string('value');
            $table->string('label');
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('custom_form_conditions', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('event_id')->index();
            $table->unsignedBigInteger('org_id')->index();
            $table->foreignId('custom_form_id')->constrained()->cascadeOnDelete();
            $table->foreignId('target_question_id')->constrained('custom_form_questions')->cascadeOnDelete();
            $table->foreignId('source_question_id')->constrained('custom_form_questions')->cascadeOnDelete();
            $table->string('operator', 32);
            $table->json('compare_value')->nullable();
            $table->string('action', 16);
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('custom_form_responses', function (Blueprint $table): void {
            $table->id();
            $table->uuid('public_id')->unique();
            $table->unsignedBigInteger('event_id')->index();
            $table->unsignedBigInteger('org_id')->index();
            $table->foreignId('custom_form_id')->constrained()->cascadeOnDelete();
            $table->string('respondent_type');
            $table->unsignedBigInteger('respondent_id');
            $table->string('status', 32)->default('draft');
            $table->unsignedInteger('form_version')->default(1);
            $table->timestamp('submitted_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('custom_form_answers', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('event_id')->index();
            $table->unsignedBigInteger('org_id')->index();
            $table->foreignId('custom_form_response_id')->constrained()->cascadeOnDelete();
            $table->foreignId('custom_form_question_id')->constrained()->cascadeOnDelete();
            $table->string('question_key');
            $table->string('question_label');
            $table->string('question_type', 32);
            $table->json('value')->nullable();
            $table->timestamps();
            $table->unique(['custom_form_response_id', 'custom_form_question_id']);
        });

        Schema::create('custom_form_answer_files', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('event_id')->index();
            $table->unsignedBigInteger('org_id')->index();
            $table->foreignId('custom_form_answer_id')->constrained()->cascadeOnDelete();
            $table->string('disk');
            $table->string('path');
            $table->string('original_name');
            $table->string('mime')->nullable();
            $table->unsignedBigInteger('size')->default(0);
            $table->timestamps();
        });
    }

    protected function dropCustomFormTables(): void
    {
        Schema::dropIfExists('custom_form_answer_files');
        Schema::dropIfExists('custom_form_answers');
        Schema::dropIfExists('custom_form_responses');
        Schema::dropIfExists('custom_form_conditions');
        Schema::dropIfExists('custom_form_question_options');
        Schema::dropIfExists('custom_form_questions');
        Schema::dropIfExists('custom_forms');
    }
}
