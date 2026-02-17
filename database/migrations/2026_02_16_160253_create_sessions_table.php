<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agenda_sessions', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('type', ['talk', 'panel', 'workshop', 'break', 'networking', 'keynote'])->default('talk');
            $table->dateTime('start_time');
            $table->dateTime('end_time');
            $table->foreignId('agenda_id')->constrained()->onDelete('cascade');
            $table->foreignId('track_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('location_id')->constrained()->onDelete('restrict');
            $table->boolean('requires_speakers')->default(true);
            $table->integer('max_attendees')->nullable();
            $table->unsignedBigInteger('event_id');
            $table->unsignedBigInteger('org_id');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['event_id', 'org_id']);
            $table->index('agenda_id');
            $table->index('track_id');
            $table->index('location_id');
            $table->index('type');
            $table->index(['start_time', 'end_time']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agenda_sessions');
    }
};
