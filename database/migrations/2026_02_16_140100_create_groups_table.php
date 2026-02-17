<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained('events')->onDelete('cascade');
            $table->foreignId('org_id')->constrained('organizations')->onDelete('cascade');
            
            // Basic Information
            $table->string('group_name');
            $table->foreignId('group_type_id')->constrained('group_types')->onDelete('restrict');
            $table->string('organization_name')->nullable();
            $table->foreignId('industry_id')->nullable()->constrained('industries')->onDelete('set null');
            $table->text('description')->nullable();
            $table->string('website_url')->nullable();
            $table->integer('allowed_attendees'); // Changed from expected_attendees
            $table->string('invoice_number')->nullable(); // Added
            
            // Primary Contact
            $table->string('primary_contact_name');
            $table->string('primary_contact_email');
            $table->string('primary_contact_phone', 50);
            
            // Secondary Contact
            $table->string('secondary_contact_name')->nullable();
            $table->string('secondary_contact_email')->nullable();
            $table->string('secondary_contact_phone', 50)->nullable();
            
            // Address
            $table->string('address')->nullable();
            $table->string('city', 100)->nullable();
            $table->string('state', 100)->nullable();
            $table->string('postal_code', 20)->nullable();
            $table->string('country', 100)->nullable();
            
            // Event Preferences
            $table->text('special_requirements')->nullable();
            
            // System Fields
            $table->boolean('is_active')->default(true);
            $table->boolean('is_vip')->default(false);
            $table->integer('sort_order')->default(0);
            
            $table->timestamps();
            $table->softDeletes();

            $table->index(['event_id', 'org_id']);
            $table->index('group_type_id');
            $table->index('industry_id');
        });

        // Pivot table for group tags
        Schema::create('event_group_tag', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_group_id')->constrained('event_groups')->onDelete('cascade');
            $table->foreignId('exhibitor_tag_id')->constrained('exhibitor_tags')->onDelete('cascade');
            $table->timestamps();

            $table->unique(['event_group_id', 'exhibitor_tag_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_group_tag');
        Schema::dropIfExists('event_groups');
    }
};
