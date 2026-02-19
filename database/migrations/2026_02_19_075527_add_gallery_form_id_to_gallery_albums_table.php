<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('gallery_albums', function (Blueprint $table) {
            $table->foreignId('gallery_form_id')->nullable()->after('enable_form')->constrained('gallery_forms')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('gallery_albums', function (Blueprint $table) {
            $table->dropForeign(['gallery_form_id']);
            $table->dropColumn('gallery_form_id');
        });
    }
};
