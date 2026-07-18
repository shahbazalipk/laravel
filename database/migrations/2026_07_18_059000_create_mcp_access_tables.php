<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mcp_access_tokens', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('event_id');
            $table->unsignedBigInteger('org_id');
            $table->string('name');
            $table->string('token_hash', 64)->unique();
            $table->json('abilities');
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('revoked_at')->nullable();
            $table->timestamps();

            $table->index(['event_id', 'org_id', 'revoked_at']);
        });

        Schema::create('mcp_tool_audit_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('mcp_access_token_id')->constrained()->restrictOnDelete();
            $table->unsignedBigInteger('event_id');
            $table->unsignedBigInteger('org_id');
            $table->string('tool');
            $table->string('status', 20);
            $table->unsignedInteger('duration_ms');
            $table->string('request_fingerprint', 64)->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('created_at');

            $table->index(['event_id', 'org_id', 'created_at']);
            $table->index(['mcp_access_token_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mcp_tool_audit_logs');
        Schema::dropIfExists('mcp_access_tokens');
    }
};
