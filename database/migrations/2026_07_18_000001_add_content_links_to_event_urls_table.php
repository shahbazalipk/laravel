<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('event_urls', function (Blueprint $table): void {
            $table->longText('custom_html')->nullable()->after('description');
        });

        Schema::create('event_url_sponsor', function (Blueprint $table): void {
            $table->foreignId('event_url_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sponsor_id')->constrained()->cascadeOnDelete();
            $table->primary(['event_url_id', 'sponsor_id']);
        });

        Schema::create('event_url_partner', function (Blueprint $table): void {
            $table->foreignId('event_url_id')->constrained()->cascadeOnDelete();
            $table->foreignId('partner_id')->constrained()->cascadeOnDelete();
            $table->primary(['event_url_id', 'partner_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_url_partner');
        Schema::dropIfExists('event_url_sponsor');

        Schema::table('event_urls', function (Blueprint $table): void {
            $table->dropColumn('custom_html');
        });
    }
};
