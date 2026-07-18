<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_contracts', function (Blueprint $table): void {
            $table->id();
            $table->uuid('public_id')->unique();
            $table->unsignedBigInteger('event_id');
            $table->unsignedBigInteger('org_id');
            $table->unsignedBigInteger('project_id');
            $table->unsignedBigInteger('task_id')->nullable();
            $table->string('number', 64);
            $table->string('title');
            $table->string('type', 32);
            $table->string('counterparty_name');
            $table->decimal('value', 18, 4);
            $table->string('currency', 3);
            $table->date('starts_on')->nullable();
            $table->date('ends_on')->nullable();
            $table->text('terms')->nullable();
            $table->string('status', 24)->default('draft');
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('signed_at')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('project_id')->references('id')->on('project_projects')->cascadeOnDelete();
            $table->foreign('task_id')->references('id')->on('project_tasks')->nullOnDelete();
            $table->unique(['event_id', 'org_id', 'number'], 'project_contracts_number_unique');
            $table->index(['event_id', 'org_id', 'project_id', 'status'], 'project_contracts_project');
            $table->index(['event_id', 'org_id', 'ends_on', 'status'], 'project_contracts_expiry');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_contracts');
    }
};
