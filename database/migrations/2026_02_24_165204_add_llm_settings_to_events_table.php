<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->string('llm_provider')->nullable()->after('template_settings'); // openai, anthropic, google, etc.
            $table->string('llm_model')->nullable(); // gpt-4, claude-3, gemini-pro, etc.
            $table->text('llm_api_key')->nullable(); // Encrypted API key
            $table->json('llm_settings')->nullable(); // Additional settings (temperature, max_tokens, etc.)
            $table->boolean('llm_enabled')->default(false); // Enable/disable LLM features
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn(['llm_provider', 'llm_model', 'llm_api_key', 'llm_settings', 'llm_enabled']);
        });
    }
};
