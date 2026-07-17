<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('custom_form_answer_files', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('event_id')->index();
            $table->unsignedBigInteger('org_id')->index();
            $table->foreignId('custom_form_answer_id')->constrained('custom_form_answers')->cascadeOnDelete();
            $table->string('disk');
            $table->string('path');
            $table->string('original_name');
            $table->string('mime')->nullable();
            $table->unsignedBigInteger('size')->default(0);
            $table->timestamps();

            $table->index(['event_id', 'org_id', 'custom_form_answer_id'], 'cf_answer_files_tenant_answer_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('custom_form_answer_files');
    }
};
