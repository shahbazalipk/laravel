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
        Schema::create('email_campaign_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('event_id')->index();
            $table->unsignedBigInteger('org_id')->index();
            $table->unsignedBigInteger('email_campaign_id')->index();
            $table->unsignedBigInteger('email_campaign_recipient_id')->nullable()->index();
            $table->string('event_type')->index();
            $table->string('message_id')->nullable()->index();
            $table->string('email')->index();
            $table->string('url')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('occurred_at');
            $table->timestamps();
            
            $table->index(['event_id', 'org_id']);
            $table->foreign('email_campaign_id')->references('id')->on('email_campaigns')->onDelete('cascade');
            $table->foreign('email_campaign_recipient_id')->references('id')->on('email_campaign_recipients')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('email_campaign_logs');
    }
};
