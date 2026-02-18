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
        Schema::table('event_urls', function (Blueprint $table) {
            $table->boolean('enable_barcode_scanner')->default(true)->after('allow_print_from_photo');
            $table->boolean('enable_manual_input')->default(true)->after('enable_barcode_scanner');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('event_urls', function (Blueprint $table) {
            $table->dropColumn(['enable_barcode_scanner', 'enable_manual_input']);
        });
    }
};
