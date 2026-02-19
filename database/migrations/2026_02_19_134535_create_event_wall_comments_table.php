<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_wall_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->onDelete('cascade');
            $table->foreignId('org_id')->constrained('organizations')->onDelete('cascade');
            $table->foreignId('event_wall_post_id')->constrained()->onDelete('cascade');
            $table->foreignId('registration_id')->constrained()->onDelete('cascade');
            $table->foreignId('parent_id')->nullable()->constrained('event_wall_comments')->onDelete('cascade'); // For nested replies
            $table->text('content');
            $table->string('image')->nullable(); // Single image per comment
            $table->boolean('is_active')->default(true);
            $table->integer('likes_count')->default(0);
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['event_wall_post_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_wall_comments');
    }
};
