<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('session_speaker', function (Blueprint $table) {
            $table->id();
            $table->foreignId('session_id')->constrained('agenda_sessions')->onDelete('cascade');
            $table->foreignId('speaker_id')->constrained()->onDelete('cascade');
            $table->string('role')->nullable(); // e.g., "Moderator", "Panelist"
            $table->timestamps();

            $table->unique(['session_id', 'speaker_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('session_speaker');
    }
};
