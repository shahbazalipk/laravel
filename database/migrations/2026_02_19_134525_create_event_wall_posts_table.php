<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_wall_posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->onDelete('cascade');
            $table->foreignId('org_id')->constrained('organizations')->onDelete('cascade');
            $table->foreignId('registration_id')->constrained()->onDelete('cascade');
            $table->text('content')->nullable();
            $table->string('post_type')->default('text'); // text, image, link
            $table->json('images')->nullable(); // Array of image paths
            $table->string('link_url')->nullable();
            $table->string('link_title')->nullable();
            $table->text('link_description')->nullable();
            $table->string('link_image')->nullable();
            $table->json('hashtags')->nullable(); // Array of hashtags
            $table->json('mentions')->nullable(); // Array of mentioned user IDs
            $table->boolean('is_pinned')->default(false);
            $table->boolean('is_approved')->default(true); // For moderation
            $table->boolean('is_active')->default(true);
            $table->integer('likes_count')->default(0);
            $table->integer('comments_count')->default(0);
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['event_id', 'org_id', 'is_active']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_wall_posts');
    }
};
