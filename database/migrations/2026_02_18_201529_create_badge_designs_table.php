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
        Schema::create('badge_designs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('event_id');
            $table->unsignedBigInteger('org_id');
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
            
            // Layout settings
            $table->string('size')->default('4x6'); // 4x6, 3x4, custom
            $table->string('orientation')->default('portrait'); // portrait, landscape
            
            // Header settings
            $table->boolean('show_header')->default(true);
            $table->string('header_bg_color')->default('#4F46E5');
            $table->string('header_text_color')->default('#FFFFFF');
            $table->boolean('show_event_logo')->default(true);
            $table->boolean('show_event_name')->default(true);
            $table->boolean('show_event_dates')->default(true);
            
            // Content settings
            $table->boolean('show_profile_picture')->default(true);
            $table->string('profile_picture_shape')->default('circle'); // circle, square, rounded
            $table->boolean('show_name')->default(true);
            $table->string('name_font_size')->default('3xl');
            $table->boolean('show_job_title')->default(true);
            $table->boolean('show_company')->default(true);
            $table->boolean('show_category')->default(true);
            $table->string('category_style')->default('badge'); // badge, text
            
            // QR Code settings
            $table->boolean('show_qr_code')->default(true);
            $table->string('qr_code_size')->default('32'); // in pixels (w-32 = 128px)
            $table->string('qr_code_position')->default('center'); // center, left, right
            
            // Footer settings
            $table->boolean('show_footer')->default(true);
            $table->string('footer_bg_color')->default('#F3F4F6');
            $table->boolean('show_registration_number')->default(true);
            $table->boolean('show_location')->default(true);
            $table->boolean('show_website')->default(true);
            
            // Border settings
            $table->boolean('show_border')->default(true);
            $table->string('border_color')->default('#4F46E5');
            $table->string('border_width')->default('4'); // in pixels
            
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['event_id', 'org_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('badge_designs');
    }
};
