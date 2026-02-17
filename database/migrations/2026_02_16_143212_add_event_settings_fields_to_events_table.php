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
        Schema::table('events', function (Blueprint $table) {
            // Basic Event Info
            if (!Schema::hasColumn('events', 'event_name')) {
                $table->string('event_name')->nullable()->after('title');
            }
            if (!Schema::hasColumn('events', 'event_type')) {
                $table->string('event_type')->nullable()->after('event_name');
            }
            if (!Schema::hasColumn('events', 'event_mode')) {
                $table->string('event_mode')->nullable()->after('event_type');
            }
            if (!Schema::hasColumn('events', 'stage')) {
                $table->string('stage')->nullable()->after('event_mode');
            }
            
            // Dates
            if (!Schema::hasColumn('events', 'online_reg_close')) {
                $table->dateTime('online_reg_close')->nullable()->after('end_date');
            }
            
            // Financial
            if (!Schema::hasColumn('events', 'vat_percentage')) {
                $table->decimal('vat_percentage', 5, 2)->default(0)->after('currency');
            }
            if (!Schema::hasColumn('events', 'tax_inclusive')) {
                $table->boolean('tax_inclusive')->default(false)->after('vat_percentage');
            }
            
            // Registration Settings
            if (!Schema::hasColumn('events', 'registration_form_active')) {
                $table->boolean('registration_form_active')->default(true)->after('tax_inclusive');
            }
            if (!Schema::hasColumn('events', 'email_verification_required')) {
                $table->boolean('email_verification_required')->default(false)->after('registration_form_active');
            }
            if (!Schema::hasColumn('events', 'code_verification_required')) {
                $table->boolean('code_verification_required')->default(false)->after('email_verification_required');
            }
            if (!Schema::hasColumn('events', 'bulk_print_enabled')) {
                $table->boolean('bulk_print_enabled')->default(false)->after('code_verification_required');
            }
            if (!Schema::hasColumn('events', 'reprint_enabled')) {
                $table->boolean('reprint_enabled')->default(false)->after('bulk_print_enabled');
            }
            
            // URLs and Links
            if (!Schema::hasColumn('events', 'website_url')) {
                $table->string('website_url')->nullable()->after('reprint_enabled');
            }
            if (!Schema::hasColumn('events', 'terms_url')) {
                $table->string('terms_url')->nullable()->after('website_url');
            }
            if (!Schema::hasColumn('events', 'map_url')) {
                $table->string('map_url')->nullable()->after('terms_url');
            }
            
            // Media
            if (!Schema::hasColumn('events', 'placeholder_type')) {
                $table->string('placeholder_type')->nullable()->after('map_url');
            }
            if (!Schema::hasColumn('events', 'placeholder_url')) {
                $table->text('placeholder_url')->nullable()->after('placeholder_type');
            }
            if (!Schema::hasColumn('events', 'header_image')) {
                $table->string('header_image')->nullable()->after('placeholder_url');
            }
            if (!Schema::hasColumn('events', 'portal_background')) {
                $table->string('portal_background')->nullable()->after('header_image');
            }
            if (!Schema::hasColumn('events', 'main_floor_plan')) {
                $table->string('main_floor_plan')->nullable()->after('portal_background');
            }
            
            // Messages and Content
            if (!Schema::hasColumn('events', 'closed_message')) {
                $table->text('closed_message')->nullable()->after('main_floor_plan');
            }
            if (!Schema::hasColumn('events', 'show_info_on_portal_background')) {
                $table->boolean('show_info_on_portal_background')->default(false)->after('closed_message');
            }
            if (!Schema::hasColumn('events', 'footer_information')) {
                $table->text('footer_information')->nullable()->after('show_info_on_portal_background');
            }
            
            // Manager Info
            if (!Schema::hasColumn('events', 'manager_name')) {
                $table->string('manager_name')->nullable()->after('footer_information');
            }
            if (!Schema::hasColumn('events', 'manager_email')) {
                $table->string('manager_email')->nullable()->after('manager_name');
            }
            if (!Schema::hasColumn('events', 'manager_phone')) {
                $table->string('manager_phone')->nullable()->after('manager_email');
            }
            
            // Address
            if (!Schema::hasColumn('events', 'address_line1')) {
                $table->string('address_line1')->nullable()->after('location');
            }
            if (!Schema::hasColumn('events', 'address_line2')) {
                $table->string('address_line2')->nullable()->after('address_line1');
            }
            if (!Schema::hasColumn('events', 'state')) {
                $table->string('state')->nullable()->after('country');
            }
            if (!Schema::hasColumn('events', 'city')) {
                $table->string('city')->nullable()->after('state');
            }
            
            // Social Media
            if (!Schema::hasColumn('events', 'twitter_mention')) {
                $table->string('twitter_mention')->nullable()->after('social_image');
            }
            if (!Schema::hasColumn('events', 'social_media_description')) {
                $table->text('social_media_description')->nullable()->after('twitter_mention');
            }
            if (!Schema::hasColumn('events', 'social_media_share_banner')) {
                $table->string('social_media_share_banner')->nullable()->after('social_media_description');
            }
            if (!Schema::hasColumn('events', 'social_media_share_font_color')) {
                $table->string('social_media_share_font_color')->nullable()->after('social_media_share_banner');
            }
            
            // Email Settings
            if (!Schema::hasColumn('events', 'smtp_host')) {
                $table->string('smtp_host')->nullable()->after('social_media_share_font_color');
            }
            if (!Schema::hasColumn('events', 'smtp_port')) {
                $table->integer('smtp_port')->nullable()->after('smtp_host');
            }
            if (!Schema::hasColumn('events', 'smtp_username')) {
                $table->string('smtp_username')->nullable()->after('smtp_port');
            }
            if (!Schema::hasColumn('events', 'smtp_password')) {
                $table->string('smtp_password')->nullable()->after('smtp_username');
            }
            if (!Schema::hasColumn('events', 'smtp_encryption')) {
                $table->string('smtp_encryption')->nullable()->after('smtp_password');
            }
            if (!Schema::hasColumn('events', 'from_email')) {
                $table->string('from_email')->nullable()->after('smtp_encryption');
            }
            if (!Schema::hasColumn('events', 'from_name')) {
                $table->string('from_name')->nullable()->after('from_email');
            }
            
            // Captcha Keys
            if (!Schema::hasColumn('events', 'recaptcha_site_key')) {
                $table->string('recaptcha_site_key')->nullable()->after('from_name');
            }
            if (!Schema::hasColumn('events', 'recaptcha_secret_key')) {
                $table->string('recaptcha_secret_key')->nullable()->after('recaptcha_site_key');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn([
                'event_name', 'event_type', 'event_mode', 'stage',
                'online_reg_close', 'vat_percentage', 'tax_inclusive',
                'registration_form_active', 'email_verification_required',
                'code_verification_required', 'bulk_print_enabled', 'reprint_enabled',
                'website_url', 'terms_url', 'map_url',
                'placeholder_type', 'placeholder_url', 'header_image',
                'portal_background', 'main_floor_plan',
                'closed_message', 'show_info_on_portal_background', 'footer_information',
                'manager_name', 'manager_email', 'manager_phone',
                'address_line1', 'address_line2', 'state', 'city',
                'twitter_mention', 'social_media_description',
                'social_media_share_banner', 'social_media_share_font_color',
                'smtp_host', 'smtp_port', 'smtp_username', 'smtp_password',
                'smtp_encryption', 'from_email', 'from_name',
                'recaptcha_site_key', 'recaptcha_secret_key'
            ]);
        });
    }
};
