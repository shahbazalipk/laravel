<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exhibitor_jobs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->onDelete('cascade');
            $table->foreignId('org_id')->constrained('organizations')->onDelete('cascade');
            $table->foreignId('exhibitor_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->text('description');
            $table->string('location')->nullable();
            $table->string('job_type')->nullable(); // Full-time, Part-time, Contract, Internship
            $table->string('experience_level')->nullable(); // Entry, Mid, Senior
            $table->string('salary_range')->nullable();
            $table->text('requirements')->nullable();
            $table->text('responsibilities')->nullable();
            $table->string('application_url')->nullable();
            $table->string('application_email')->nullable();
            $table->date('deadline')->nullable();
            $table->integer('views_count')->default(0);
            $table->integer('applications_count')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['exhibitor_id', 'is_active']);
            $table->index(['event_id', 'org_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exhibitor_jobs');
    }
};
