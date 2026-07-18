<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('module_access_grants', function (Blueprint $table): void {
            $table->id();
            $table->uuid('public_id')->unique();
            $table->unsignedBigInteger('event_id');
            $table->unsignedBigInteger('org_id');
            $table->unsignedBigInteger('organization_admin_user_id');
            $table->string('module', 32);
            $table->string('role', 64);
            $table->json('abilities');
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('granted_by')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(
                ['event_id', 'org_id', 'organization_admin_user_id', 'module'],
                'module_access_grants_principal_unique'
            );
            $table->index(['event_id', 'org_id', 'module', 'is_active'], 'module_access_grants_lookup');
        });

        Schema::create('platform_audit_entries', function (Blueprint $table): void {
            $table->id();
            $table->uuid('public_id')->unique();
            $table->unsignedBigInteger('event_id');
            $table->unsignedBigInteger('org_id');
            $table->string('module', 32);
            $table->string('action', 100);
            $table->string('subject_type', 100);
            $table->string('subject_public_id')->nullable();
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->string('actor_type', 100)->nullable();
            $table->unsignedBigInteger('actor_id')->nullable();
            $table->string('actor_name')->nullable();
            $table->string('actor_email')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->text('reason')->nullable();
            $table->json('before_values')->nullable();
            $table->json('after_values')->nullable();
            $table->json('metadata')->nullable();
            $table->uuid('correlation_id')->nullable();
            $table->string('idempotency_key')->nullable();
            $table->timestamp('occurred_at');

            $table->index(['event_id', 'org_id', 'module', 'occurred_at'], 'platform_audit_timeline');
            $table->index(['subject_type', 'subject_id', 'occurred_at'], 'platform_audit_subject');
            $table->unique(
                ['event_id', 'org_id', 'module', 'idempotency_key'],
                'platform_audit_idempotency_unique'
            );
        });

        Schema::create('platform_attachments', function (Blueprint $table): void {
            $table->id();
            $table->uuid('public_id')->unique();
            $table->unsignedBigInteger('event_id');
            $table->unsignedBigInteger('org_id');
            $table->string('module', 32);
            $table->string('resource_type', 100);
            $table->string('resource_public_id');
            $table->string('disk', 64)->default('local');
            $table->string('path');
            $table->string('original_name');
            $table->string('mime_type', 150);
            $table->unsignedBigInteger('size_bytes');
            $table->string('checksum', 64)->nullable();
            $table->string('category', 64)->nullable();
            $table->string('visibility', 32)->default('internal');
            $table->boolean('is_sensitive')->default(false);
            $table->unsignedInteger('version')->default(1);
            $table->string('uploaded_by_type', 100)->nullable();
            $table->unsignedBigInteger('uploaded_by_id')->nullable();
            $table->text('description')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(
                ['event_id', 'org_id', 'module', 'resource_type', 'resource_public_id'],
                'platform_attachments_resource'
            );
            $table->index(['event_id', 'org_id', 'is_sensitive'], 'platform_attachments_sensitive');
        });

        Schema::create('platform_notifications', function (Blueprint $table): void {
            $table->id();
            $table->uuid('public_id')->unique();
            $table->unsignedBigInteger('event_id');
            $table->unsignedBigInteger('org_id');
            $table->unsignedBigInteger('recipient_admin_id');
            $table->string('module', 32);
            $table->string('type', 100);
            $table->string('title');
            $table->text('body')->nullable();
            $table->string('resource_type', 100)->nullable();
            $table->string('resource_public_id')->nullable();
            $table->json('data')->nullable();
            $table->string('deduplication_key')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamp('emailed_at')->nullable();
            $table->timestamps();

            $table->index(
                ['event_id', 'org_id', 'recipient_admin_id', 'read_at'],
                'platform_notifications_inbox'
            );
            $table->unique(
                ['event_id', 'org_id', 'recipient_admin_id', 'deduplication_key'],
                'platform_notifications_deduplication_unique'
            );
        });

        Schema::create('platform_resource_links', function (Blueprint $table): void {
            $table->id();
            $table->uuid('public_id')->unique();
            $table->unsignedBigInteger('event_id');
            $table->unsignedBigInteger('org_id');
            $table->string('source_module', 32);
            $table->string('source_type', 64);
            $table->string('source_public_id', 64);
            $table->string('target_module', 32);
            $table->string('target_type', 64);
            $table->string('target_public_id', 64);
            $table->string('relationship', 64);
            $table->json('metadata')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->unique(
                [
                    'event_id', 'org_id', 'source_module', 'source_type', 'source_public_id',
                    'target_module', 'target_type', 'target_public_id', 'relationship',
                ],
                'platform_resource_links_unique'
            );
            $table->index(
                ['event_id', 'org_id', 'target_module', 'target_type', 'target_public_id'],
                'platform_resource_links_target'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('platform_resource_links');
        Schema::dropIfExists('platform_notifications');
        Schema::dropIfExists('platform_attachments');
        Schema::dropIfExists('platform_audit_entries');
        Schema::dropIfExists('module_access_grants');
    }
};
