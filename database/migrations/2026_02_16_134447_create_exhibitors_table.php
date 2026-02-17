<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exhibitors', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('event_id');
            $table->unsignedBigInteger('org_id');
            
            // Required Fields
            $table->string('company_name');
            $table->unsignedBigInteger('exhibitor_type_id');
            $table->unsignedBigInteger('industry_id');
            $table->string('contact_person_name');
            $table->string('contact_email');
            $table->string('contact_phone');
            
            // Company Information
            $table->text('description')->nullable();
            $table->string('logo')->nullable();
            $table->string('website_url')->nullable();
            $table->year('year_established')->nullable();
            $table->string('company_size')->nullable();
            $table->string('registration_number')->nullable();
            
            // Booth Information
            $table->unsignedBigInteger('booth_type_id')->nullable();
            $table->string('booth_number')->nullable();
            $table->decimal('booth_size', 8, 2)->nullable();
            
            // Additional Contact
            $table->string('secondary_contact_name')->nullable();
            $table->string('secondary_contact_email')->nullable();
            $table->string('secondary_contact_phone')->nullable();
            
            // Address
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('country')->nullable();
            
            // Social Media
            $table->string('linkedin_url')->nullable();
            $table->string('twitter_url')->nullable();
            $table->string('facebook_url')->nullable();
            $table->string('instagram_url')->nullable();
            
            // Event Specific
            $table->string('participation_status')->default('pending');
            $table->date('registration_date')->nullable();
            $table->string('payment_status')->default('pending');
            $table->text('special_requirements')->nullable();
            
            // Visibility
            $table->boolean('visible_on_website')->default(true);
            $table->boolean('visible_on_app')->default(true);
            $table->boolean('visible_in_directory')->default(true);
            $table->boolean('is_featured')->default(false);
            
            // Media
            $table->string('banner_image')->nullable();
            $table->string('catalog_file')->nullable();
            $table->string('video_url')->nullable();
            
            // System
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index(['event_id', 'org_id']);
            $table->index('exhibitor_type_id');
            $table->index('industry_id');
            $table->index('booth_type_id');
            $table->index('is_active');
            $table->index('participation_status');
            $table->index('company_name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exhibitors');
    }
};
