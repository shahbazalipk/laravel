<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_url_visits', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('event_id');
            $table->unsignedBigInteger('org_id');
            $table->unsignedBigInteger('event_url_id')->index();
            $table->uuid('visitor_uuid')->index();
            $table->string('session_key', 64)->index();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 512)->nullable();
            $table->string('browser', 64)->nullable()->index();
            $table->string('browser_version', 32)->nullable();
            $table->string('platform', 64)->nullable()->index();
            $table->string('device_type', 24)->nullable()->index();
            $table->string('referrer', 1000)->nullable();
            $table->string('landing_path', 500)->nullable();
            $table->string('landing_query', 1000)->nullable();
            $table->string('utm_source', 255)->nullable()->index();
            $table->string('utm_medium', 255)->nullable();
            $table->string('utm_campaign', 255)->nullable()->index();
            $table->string('utm_term', 255)->nullable();
            $table->string('utm_content', 255)->nullable();
            $table->string('country_code', 8)->nullable()->index();
            $table->string('language', 32)->nullable();
            $table->string('screen_size', 32)->nullable();
            $table->unsignedInteger('pageview_count')->default(0);
            $table->unsignedInteger('duration_seconds')->default(0);
            $table->string('farthest_step', 64)->nullable()->index();
            $table->string('last_step', 64)->nullable()->index();
            $table->boolean('registered')->default(false)->index();
            $table->unsignedBigInteger('registration_id')->nullable()->index();
            $table->unsignedBigInteger('registration_draft_id')->nullable()->index();
            $table->boolean('is_bounce')->default(false);
            $table->timestamp('started_at')->nullable()->index();
            $table->timestamp('last_seen_at')->nullable()->index();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['event_url_id', 'started_at']);
            $table->index(['event_id', 'org_id', 'started_at']);
            $table->unique(['event_url_id', 'session_key']);
        });

        Schema::create('event_url_visit_events', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('event_id');
            $table->unsignedBigInteger('org_id');
            $table->unsignedBigInteger('event_url_id')->index();
            $table->unsignedBigInteger('event_url_visit_id')->index();
            $table->string('event_type', 64)->index();
            $table->string('step', 64)->nullable()->index();
            $table->string('path', 500)->nullable();
            $table->json('meta')->nullable();
            $table->timestamp('occurred_at')->index();
            $table->timestamps();

            $table->index(['event_url_id', 'event_type', 'occurred_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_url_visit_events');
        Schema::dropIfExists('event_url_visits');
    }
};
