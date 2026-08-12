<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('event_urls', function (Blueprint $table): void {
            $table->timestamp('expires_at')->nullable()->after('is_active');
            $table->text('registration_closed_message')->nullable()->after('expires_at');
        });
    }

    public function down(): void
    {
        Schema::table('event_urls', function (Blueprint $table): void {
            $table->dropColumn(['expires_at', 'registration_closed_message']);
        });
    }
};
