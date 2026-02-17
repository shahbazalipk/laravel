<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('memberships', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('event_id')->index();
            $table->unsignedBigInteger('org_id')->index();
            $table->string('name');
            $table->string('slug')->nullable();
            $table->enum('verification_type', ['upload_file', 'third_party_api'])->default('upload_file');
            $table->string('api_endpoint')->nullable();
            $table->string('api_key')->nullable();
            $table->text('api_headers')->nullable(); // JSON format
            $table->string('file_path')->nullable();
            $table->string('color', 7)->default('#6366f1');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            
            $table->index(['event_id', 'org_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('memberships');
    }
};
