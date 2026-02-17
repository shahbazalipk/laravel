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
        if (!Schema::hasTable('sso_tokens')) {
            Schema::create('sso_tokens', function (Blueprint $table) {
                $table->id();
                $table->string('token', 100)->unique();
                $table->unsignedBigInteger('user_id');
                $table->unsignedBigInteger('organization_id');
                $table->unsignedBigInteger('event_id');
                $table->timestamp('expires_at');
                $table->timestamp('used_at')->nullable();
                $table->timestamps();
                
                $table->index(['token', 'expires_at']);
                $table->index('user_id');
                $table->index(['organization_id', 'event_id']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sso_tokens');
    }
};
