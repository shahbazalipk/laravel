<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_wall_likes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->onDelete('cascade');
            $table->foreignId('org_id')->constrained('organizations')->onDelete('cascade');
            $table->foreignId('registration_id')->constrained()->onDelete('cascade');
            $table->string('likeable_type'); // EventWallPost or EventWallComment
            $table->unsignedBigInteger('likeable_id');
            $table->string('reaction_type')->default('like'); // like, love, celebrate, support, insightful
            $table->timestamps();
            
            $table->index(['likeable_type', 'likeable_id']);
            $table->unique(['registration_id', 'likeable_type', 'likeable_id'], 'wall_likes_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_wall_likes');
    }
};
