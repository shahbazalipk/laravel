<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tracks', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('color', 7)->nullable();
            $table->integer('sort_order')->default(0);
            $table->foreignId('agenda_id')->constrained()->onDelete('cascade');
            $table->unsignedBigInteger('event_id');
            $table->unsignedBigInteger('org_id');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['event_id', 'org_id']);
            $table->index('agenda_id');
            $table->index('sort_order');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tracks');
    }
};
