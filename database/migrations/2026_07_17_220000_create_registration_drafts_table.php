<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('registration_drafts', function (Blueprint $table) {
            $table->id();
            $table->uuid('public_id')->unique();
            $table->unsignedBigInteger('event_id')->index();
            $table->unsignedBigInteger('org_id')->index();
            $table->unsignedBigInteger('event_url_id')->nullable()->index();
            $table->string('email')->index();
            $table->string('resume_token_hash', 64)->unique();
            $table->text('payload')->nullable();
            $table->string('current_step', 32)->default('email');
            $table->timestamp('email_verified_at')->nullable();
            $table->string('otp_hash', 255)->nullable();
            $table->timestamp('otp_expires_at')->nullable();
            $table->unsignedTinyInteger('otp_attempts')->default(0);
            $table->timestamp('otp_last_sent_at')->nullable();
            $table->unsignedBigInteger('registration_id')->nullable()->index();
            $table->timestamp('expires_at')->index();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['event_id', 'org_id', 'email']);
            $table->index(['event_id', 'org_id', 'current_step']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('registration_drafts');
    }
};
