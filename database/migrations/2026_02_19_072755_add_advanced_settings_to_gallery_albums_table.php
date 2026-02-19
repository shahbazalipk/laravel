<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('gallery_albums', function (Blueprint $table) {
            $table->string('cover_photo')->nullable()->after('cover_image');
            $table->boolean('show_cover_at_top')->default(true)->after('cover_photo');
            $table->enum('default_photo_status', ['published', 'unpublished'])->default('published')->after('show_cover_at_top');
            $table->enum('upload_size', ['high_resolution', 'web_size'])->default('high_resolution')->after('default_photo_status');
            $table->enum('download_size', ['high_resolution', 'web_size'])->default('high_resolution')->after('upload_size');
            $table->string('preset')->nullable()->after('download_size');
            $table->boolean('enable_download')->default(true)->after('preset');
            $table->boolean('enable_form')->default(false)->after('enable_download');
            $table->enum('form_trigger', ['on_open', 'delayed', 'on_download'])->default('on_open')->after('enable_form');
            $table->integer('form_delay_seconds')->default(0)->after('form_trigger');
            $table->enum('form_requirement', ['mandatory', 'optional'])->default('optional')->after('form_delay_seconds');
        });
    }

    public function down(): void
    {
        Schema::table('gallery_albums', function (Blueprint $table) {
            $table->dropColumn([
                'cover_photo',
                'show_cover_at_top',
                'default_photo_status',
                'upload_size',
                'download_size',
                'preset',
                'enable_download',
                'enable_form',
                'form_trigger',
                'form_delay_seconds',
                'form_requirement',
            ]);
        });
    }
};
