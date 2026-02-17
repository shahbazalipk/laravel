<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('registrations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('event_id');
            $table->unsignedBigInteger('org_id');
            
            // Category & Type
            $table->unsignedBigInteger('registration_category_id');
            $table->unsignedBigInteger('registration_status_id')->nullable();
            $table->enum('registration_type', ['individual', 'exhibitor', 'group'])->default('individual');
            $table->unsignedBigInteger('exhibitor_id')->nullable();
            $table->unsignedBigInteger('group_id')->nullable();
            
            // Personal Information
            $table->string('salutation', 10)->nullable();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email');
            $table->string('phone', 50);
            $table->string('mobile_phone', 50)->nullable();
            $table->string('job_title')->nullable();
            $table->string('department')->nullable();
            
            // Category-specific fields
            $table->string('professional_student_id')->nullable();
            $table->string('membership_id')->nullable();
            $table->boolean('membership_validated')->default(false);
            
            // Company Information
            $table->string('company_name');
            $table->unsignedBigInteger('industry_id')->nullable();
            $table->unsignedBigInteger('business_activity_id')->nullable();
            $table->string('company_size', 50)->nullable();
            $table->string('company_website', 500)->nullable();
            $table->text('company_address')->nullable();
            $table->string('city', 100)->nullable();
            $table->string('state', 100)->nullable();
            $table->string('postal_code', 20)->nullable();
            $table->string('country', 100)->nullable();
            $table->string('tax_registration_number', 100)->nullable();
            
            // Additional Information
            $table->text('dietary_requirements')->nullable();
            $table->text('special_needs')->nullable();
            $table->string('tshirt_size', 10)->nullable();
            $table->string('how_did_you_hear')->nullable();
            $table->json('areas_of_interest')->nullable();
            $table->boolean('marketing_consent')->default(false);
            $table->boolean('terms_accepted')->default(false);
            $table->timestamp('terms_accepted_at')->nullable();
            
            // Pricing
            $table->decimal('base_price', 10, 2);
            $table->decimal('tax_amount', 10, 2);
            $table->decimal('total_amount', 10, 2);
            $table->string('currency', 10)->nullable();
            
            // Payment
            $table->enum('payment_status', ['pending', 'paid', 'failed', 'refunded'])->default('pending');
            $table->string('payment_method', 50)->nullable();
            $table->string('payment_reference')->nullable();
            $table->timestamp('payment_date')->nullable();
            
            // Badge & QR
            $table->string('badge_number', 50)->unique()->nullable();
            $table->text('qr_code')->nullable();
            $table->boolean('badge_printed')->default(false);
            $table->timestamp('badge_printed_at')->nullable();
            
            // Check-in
            $table->boolean('checked_in')->default(false);
            $table->timestamp('checked_in_at')->nullable();
            $table->string('checked_in_by')->nullable();
            
            // Email Verification
            $table->boolean('email_verified')->default(false);
            $table->timestamp('email_verified_at')->nullable();
            $table->string('email_verification_token')->nullable();
            
            // Code Verification
            $table->string('verification_code', 10)->nullable();
            $table->boolean('code_verified')->default(false);
            $table->timestamp('code_verified_at')->nullable();
            
            // System
            $table->string('registration_number', 50)->unique();
            $table->string('registration_source', 50)->default('web');
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index(['event_id', 'org_id']);
            $table->index('registration_category_id');
            $table->index('email');
            $table->index('registration_status_id');
            $table->index('payment_status');
            $table->index('exhibitor_id');
            $table->index('group_id');
            $table->index('badge_number');
            $table->index('registration_number');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('registrations');
    }
};
