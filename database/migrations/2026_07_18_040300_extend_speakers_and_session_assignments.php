<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('speakers', function (Blueprint $table): void {
            $table->uuid('public_id')->nullable()->unique()->after('id');
            $table->string('onboarding_status', 24)->default('not_started')->after('job_title');
            $table->json('profile')->nullable()->after('onboarding_status');
            $table->index(['event_id', 'org_id', 'email'], 'speakers_tenant_email_idx');
        });

        Schema::table('session_speaker', function (Blueprint $table): void {
            $table->unsignedBigInteger('event_id')->nullable()->after('id');
            $table->unsignedBigInteger('org_id')->nullable()->after('event_id');
            $table->unsignedBigInteger('submission_id')->nullable()->after('speaker_id');
            $table->unsignedInteger('presentation_order')->nullable()->after('role');
            $table->unsignedInteger('duration_minutes')->nullable()->after('presentation_order');
            $table->string('status', 32)->default('proposed')->after('duration_minutes');
            $table->timestamp('confirmed_at')->nullable()->after('status');
            $table->index(['event_id', 'org_id'], 'session_speaker_tenant_idx');
            $table->index('submission_id', 'session_speaker_submission_idx');
        });
    }

    public function down(): void
    {
        Schema::table('session_speaker', function (Blueprint $table): void {
            $table->dropIndex('session_speaker_tenant_idx');
            $table->dropIndex('session_speaker_submission_idx');
            $table->dropColumn(['event_id', 'org_id', 'submission_id', 'presentation_order', 'duration_minutes', 'status', 'confirmed_at']);
        });
        Schema::table('speakers', function (Blueprint $table): void {
            $table->dropIndex('speakers_tenant_email_idx');
            $table->dropUnique(['public_id']);
            $table->dropColumn(['public_id', 'onboarding_status', 'profile']);
        });
    }
};
