<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('custom_forms', function (Blueprint $table) {
            $table->id();
            $table->uuid('public_id')->unique();
            $table->unsignedBigInteger('event_id')->index();
            $table->unsignedBigInteger('org_id')->index();
            $table->string('name');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->string('audience', 32);
            $table->string('audience_unique', 32)->nullable();
            $table->boolean('is_active')->default(true);
            $table->json('settings')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['event_id', 'org_id', 'slug']);
            $table->unique(['event_id', 'org_id', 'audience_unique'], 'custom_forms_event_org_audience_unique');
            $table->index(['event_id', 'org_id', 'audience', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('custom_forms');
    }
};
