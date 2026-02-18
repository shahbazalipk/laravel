<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('event_urls', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('event_id');
            $table->unsignedBigInteger('organization_id');
            $table->string('name');
            $table->string('slug')->unique();
            $table->enum('type', ['online', 'onsite', 'exhibitors', 'groups']);
            $table->boolean('is_active')->default(true);
            $table->json('enabled_categories')->nullable(); // Array of category IDs
            $table->text('description')->nullable();
            $table->timestamps();
            
            $table->index(['event_id', 'type']);
            $table->index('slug');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_urls');
    }
};
