<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendee_favorites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('registration_id')->constrained('registrations')->onDelete('cascade');
            $table->string('favoritable_type'); // Model type (Exhibitor, Speaker, Session, etc.)
            $table->unsignedBigInteger('favoritable_id'); // Model ID
            $table->timestamps();

            // Composite unique index to prevent duplicate favorites
            $table->unique(['registration_id', 'favoritable_type', 'favoritable_id'], 'unique_favorite');
            
            // Index for faster queries
            $table->index(['favoritable_type', 'favoritable_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendee_favorites');
    }
};
