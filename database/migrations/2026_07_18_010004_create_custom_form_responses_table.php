<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('custom_form_responses', function (Blueprint $table) {
            $table->id();
            $table->uuid('public_id')->unique();
            $table->unsignedBigInteger('event_id')->index();
            $table->unsignedBigInteger('org_id')->index();
            $table->foreignId('custom_form_id')->constrained('custom_forms')->cascadeOnDelete();
            $table->string('respondent_type');
            $table->unsignedBigInteger('respondent_id');
            $table->string('status', 32)->default('draft');
            $table->unsignedInteger('form_version')->default(1);
            $table->timestamp('submitted_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['event_id', 'org_id', 'custom_form_id']);
            $table->index(['respondent_type', 'respondent_id']);
            $table->index(['custom_form_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('custom_form_responses');
    }
};
