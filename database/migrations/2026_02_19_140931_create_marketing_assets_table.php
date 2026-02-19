<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('marketing_assets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->onDelete('cascade');
            $table->foreignId('org_id')->constrained('organizations')->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('type', ['poster', 'social_post', 'hashtag', 'caption', 'story', 'banner', 'email_signature'])->default('poster');
            $table->string('category')->nullable(); // pre-event, during-event, post-event
            $table->json('content')->nullable(); // Flexible content storage
            $table->string('image_path')->nullable(); // For posters, banners
            $table->json('dimensions')->nullable(); // Image dimensions
            $table->json('hashtags')->nullable(); // Array of hashtags
            $table->text('caption_text')->nullable(); // Pre-written captions
            $table->json('social_platforms')->nullable(); // Facebook, Twitter, LinkedIn, Instagram
            $table->integer('download_count')->default(0);
            $table->integer('share_count')->default(0);
            $table->integer('order')->default(0);
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['event_id', 'org_id', 'type', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('marketing_assets');
    }
};
