<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('partners', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('event_id');
            $table->unsignedBigInteger('org_id');
            
            // Visibility flags
            $table->boolean('is_active')->default(true);
            $table->boolean('visible_on_ebadge')->default(false);
            $table->boolean('visible_on_exhibitor_portal')->default(false);
            $table->boolean('visible_on_group_portal')->default(false);
            $table->boolean('visible_online')->default(true);
            $table->boolean('visible_onsite')->default(true);
            
            // Basic Information
            $table->string('name');
            $table->string('sponsorship_label');
            $table->string('type'); // e.g., 'Strategic', 'Technology', 'Media', 'Community'
            $table->text('description')->nullable();
            
            // Logo fields
            $table->string('logo_thumbnail')->nullable();
            $table->string('logo_defined_size')->nullable();
            
            // Additional fields
            $table->string('website_url')->nullable();
            $table->string('contact_email')->nullable();
            $table->string('contact_phone')->nullable();
            $table->integer('sort_order')->default(0);
            
            $table->timestamps();
            $table->softDeletes();

            $table->index(['event_id', 'org_id']);
            $table->index('type');
            $table->index('sort_order');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('partners');
    }
};
