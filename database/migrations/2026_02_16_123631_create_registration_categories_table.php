<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('registration_categories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('event_id')->index();
            $table->unsignedBigInteger('org_id')->index();
            
            // Basic Info
            $table->unsignedBigInteger('registration_status_id')->nullable();
            $table->string('name');
            $table->unsignedBigInteger('mobile_persona_id')->nullable();
            $table->string('mobile_persona_color', 7)->nullable();
            $table->string('badge_name')->nullable();
            $table->text('instructions_text')->nullable();
            $table->text('instruction_description')->nullable();
            
            // Validity & Pricing
            $table->date('valid_from')->nullable();
            $table->date('valid_to')->nullable();
            $table->decimal('price', 10, 2)->default(0.00);
            $table->string('currency', 3)->default('AED');
            $table->decimal('vat_percentage', 5, 2)->default(5.00);
            $table->boolean('show_trn')->default(false);
            
            // Visibility & Limits
            $table->boolean('visible')->default(true);
            $table->integer('minimum_items')->default(0);
            $table->integer('maximum_items')->nullable();
            $table->integer('minimum_options')->default(0);
            $table->integer('maximum_options')->nullable();
            
            // ID Requirements
            $table->boolean('need_professional_student_id')->default(false);
            $table->text('professional_student_id_message')->nullable();
            
            // Membership Requirements
            $table->boolean('need_membership_id')->default(false);
            $table->unsignedBigInteger('membership_id')->nullable();
            $table->text('membership_not_found_message')->nullable();
            $table->text('membership_invalid_message')->nullable();
            
            // Password & Other
            $table->boolean('needs_password')->default(false);
            $table->string('password')->nullable();
            $table->boolean('sponsored')->default(false);
            $table->boolean('send_to_dtcm')->default(false);
            $table->text('pipelines')->nullable();
            $table->integer('capacity')->nullable();
            
            // Standard Fields
            $table->string('color', 7)->default('#3B82F6');
            $table->text('description')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            
            $table->timestamps();
            
            $table->index(['event_id', 'org_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('registration_categories');
    }
};
