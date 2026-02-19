<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('galleries', function (Blueprint $table) {
            $table->foreignId('gallery_album_id')->nullable()->after('org_id')->constrained()->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('galleries', function (Blueprint $table) {
            $table->dropForeign(['gallery_album_id']);
            $table->dropColumn('gallery_album_id');
        });
    }
};
