<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->onDelete('cascade');
            $table->unsignedBigInteger('org_id');
            $table->string('title');
            $table->string('type'); // banner, sidebar, popup, footer
            $table->string('placement'); // home, exhibitors, sessions, etc.
            $table->string('image')->nullable();
            $table->text('content')->nullable(); // HTML content for text ads
            $table->string('link_url')->nullable();
            $table->string('link_text')->nullable();
            $table->boolean('open_new_tab')->default(true);
            $table->integer('display_order')->default(0);
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->integer('max_impressions')->nullable();
            $table->integer('impressions_count')->default(0);
            $table->integer('clicks_count')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['event_id', 'placement', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ads');
    }
};
