<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('custom_form_question_options', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('event_id')->index();
            $table->unsignedBigInteger('org_id')->index();
            $table->foreignId('custom_form_question_id')->constrained('custom_form_questions')->cascadeOnDelete();
            $table->string('value');
            $table->string('label');
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['event_id', 'org_id', 'custom_form_question_id'], 'cfq_options_tenant_question_idx');
            $table->index(['custom_form_question_id', 'sort_order'], 'cfq_options_question_order_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('custom_form_question_options');
    }
};
