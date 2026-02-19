<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('galleries', function (Blueprint $table) {
            $table->boolean('apply_watermark')->default(false)->after('edit_settings');
            $table->string('watermark_position')->default('bottom-right')->after('apply_watermark');
            $table->integer('watermark_opacity')->default(50)->after('watermark_position');
            $table->string('frame_style')->nullable()->after('watermark_opacity');
        });
    }

    public function down(): void
    {
        Schema::table('galleries', function (Blueprint $table) {
            $table->dropColumn(['apply_watermark', 'watermark_position', 'watermark_opacity', 'frame_style']);
        });
    }
};
