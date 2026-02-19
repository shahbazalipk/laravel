<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendee_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->onDelete('cascade');
            $table->foreignId('org_id')->constrained('organizations')->onDelete('cascade');
            $table->foreignId('sender_id')->constrained('registrations')->onDelete('cascade');
            $table->foreignId('receiver_id')->constrained('registrations')->onDelete('cascade');
            $table->text('message');
            $table->string('attachment')->nullable(); // File attachment path
            $table->string('attachment_type')->nullable(); // image, document, etc.
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->boolean('is_deleted_by_sender')->default(false);
            $table->boolean('is_deleted_by_receiver')->default(false);
            $table->timestamps();
            
            $table->index(['event_id', 'org_id']);
            $table->index(['sender_id', 'receiver_id', 'created_at']);
            $table->index(['is_read']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendee_messages');
    }
};
