<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agendas', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->date('start_date');
            $table->date('end_date');
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft');
            $table->unsignedBigInteger('event_id');
            $table->unsignedBigInteger('org_id');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['event_id', 'org_id']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agendas');
    }
};
