<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('custom_form_answers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('event_id')->index();
            $table->unsignedBigInteger('org_id')->index();
            $table->foreignId('custom_form_response_id')->constrained('custom_form_responses')->cascadeOnDelete();
            $table->foreignId('custom_form_question_id')->constrained('custom_form_questions')->cascadeOnDelete();
            $table->string('question_key');
            $table->string('question_label');
            $table->string('question_type', 32);
            $table->json('value')->nullable();
            $table->timestamps();

            $table->unique(['custom_form_response_id', 'custom_form_question_id'], 'custom_form_answers_response_question_unique');
            $table->index(['event_id', 'org_id', 'custom_form_response_id'], 'cf_answers_tenant_response_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('custom_form_answers');
    }
};
