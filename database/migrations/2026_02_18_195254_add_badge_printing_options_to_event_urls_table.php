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
            // Add 'badge' to the type enum
            $table->enum('type', ['online', 'onsite', 'exhibitors', 'groups', 'badge'])->change();
            
            // Badge printing options
            $table->boolean('allow_reprint')->default(false)->after('enabled_categories');
            $table->boolean('allow_print_from_photo')->default(false)->after('allow_reprint');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('event_urls', function (Blueprint $table) {
            $table->dropColumn(['allow_reprint', 'allow_print_from_photo']);
            $table->enum('type', ['online', 'onsite', 'exhibitors', 'groups'])->change();
        });
    }
};
