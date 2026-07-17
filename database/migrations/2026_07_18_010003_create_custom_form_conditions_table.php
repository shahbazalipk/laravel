<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('custom_form_conditions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('event_id')->index();
            $table->unsignedBigInteger('org_id')->index();
            $table->foreignId('custom_form_id')->constrained('custom_forms')->cascadeOnDelete();
            $table->foreignId('target_question_id')->constrained('custom_form_questions')->cascadeOnDelete();
            $table->foreignId('source_question_id')->constrained('custom_form_questions')->cascadeOnDelete();
            $table->string('operator', 32);
            $table->json('compare_value')->nullable();
            $table->string('action', 16);
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['event_id', 'org_id', 'custom_form_id']);
            $table->index(
                ['custom_form_id', 'target_question_id', 'sort_order'],
                'cf_conditions_form_target_order_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('custom_form_conditions');
    }
};
