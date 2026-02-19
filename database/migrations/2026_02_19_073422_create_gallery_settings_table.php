<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gallery_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->onDelete('cascade');
            $table->foreignId('org_id')->constrained('organizations')->onDelete('cascade');
            
            // Store settings
            $table->boolean('enable_store')->default(false);
            $table->string('store_url')->nullable();
            
            // Photo sizes
            $table->json('photo_sizes')->nullable();
            
            // Presets
            $table->json('presets')->nullable();
            
            // Branding
            $table->string('logo')->nullable();
            $table->string('primary_color')->default('#4F46E5');
            $table->string('secondary_color')->default('#10B981');
            $table->text('custom_css')->nullable();
            
            // Default form behavior
            $table->foreignId('default_form_id')->nullable()->constrained('gallery_forms')->onDelete('set null');
            $table->enum('default_form_trigger', ['on_open', 'delayed', 'on_download'])->default('on_download');
            $table->integer('default_form_delay_seconds')->default(10);
            $table->enum('default_form_requirement', ['mandatory', 'optional'])->default('mandatory');
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gallery_settings');
    }
};
