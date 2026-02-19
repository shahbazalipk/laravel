<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendee_connections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->onDelete('cascade');
            $table->foreignId('org_id')->constrained('organizations')->onDelete('cascade');
            $table->foreignId('sender_id')->constrained('registrations')->onDelete('cascade');
            $table->foreignId('receiver_id')->constrained('registrations')->onDelete('cascade');
            $table->enum('status', ['pending', 'accepted', 'rejected', 'blocked'])->default('pending');
            $table->text('message')->nullable(); // Optional message with connection request
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->timestamps();
            
            $table->index(['event_id', 'org_id']);
            $table->index(['sender_id', 'status']);
            $table->index(['receiver_id', 'status']);
            $table->unique(['sender_id', 'receiver_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendee_connections');
    }
};
