<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('registration_notes', function (Blueprint $table): void {
            $table->id();
            $table->uuid('public_id')->unique();
            $table->unsignedBigInteger('event_id')->index();
            $table->unsignedBigInteger('org_id')->index();
            $table->unsignedBigInteger('registration_id')->index();
            $table->text('body')->nullable();
            $table->string('image_path')->nullable();
            $table->string('author_name')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('registration_id')
                ->references('id')
                ->on('registrations')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('registration_notes');
    }
};
