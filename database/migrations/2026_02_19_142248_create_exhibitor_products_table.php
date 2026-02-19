<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exhibitor_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->onDelete('cascade');
            $table->foreignId('org_id')->constrained('organizations')->onDelete('cascade');
            $table->foreignId('exhibitor_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->text('description');
            $table->string('category')->nullable();
            $table->string('image')->nullable();
            $table->json('images')->nullable(); // Multiple images
            $table->decimal('price', 10, 2)->nullable();
            $table->string('price_text')->nullable(); // "Starting from $99" or "Contact for pricing"
            $table->text('features')->nullable();
            $table->text('specifications')->nullable();
            $table->string('brochure_url')->nullable();
            $table->string('video_url')->nullable();
            $table->string('demo_url')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_new')->default(false);
            $table->integer('order')->default(0);
            $table->integer('views_count')->default(0);
            $table->integer('inquiries_count')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['exhibitor_id', 'is_active']);
            $table->index(['event_id', 'org_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exhibitor_products');
    }
};
