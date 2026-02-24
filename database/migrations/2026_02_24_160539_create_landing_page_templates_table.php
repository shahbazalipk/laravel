<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('landing_page_templates', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('event_id');
            $table->unsignedBigInteger('org_id');
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('preview_image')->nullable();
            $table->longText('html_content');
            $table->longText('css_content')->nullable();
            $table->longText('js_content')->nullable();
            $table->json('customizable_sections')->nullable(); // Sections that can be edited
            $table->json('default_settings')->nullable(); // Default colors, fonts, etc.
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['event_id', 'org_id']);
            $table->index('slug');
        });

        // Add template_id to events table
        Schema::table('events', function (Blueprint $table) {
            $table->foreignId('landing_page_template_id')->nullable()->after('id')->constrained('landing_page_templates')->nullOnDelete();
            $table->json('template_settings')->nullable(); // Custom settings for this event
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropForeign(['landing_page_template_id']);
            $table->dropColumn(['landing_page_template_id', 'template_settings']);
        });
        
        Schema::dropIfExists('landing_page_templates');
    }
};
