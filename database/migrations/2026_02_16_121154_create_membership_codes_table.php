<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('membership_codes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('membership_id')->index();
            $table->unsignedBigInteger('event_id')->index();
            $table->unsignedBigInteger('org_id')->index();
            $table->string('code')->index();
            $table->integer('allowed_usage')->default(1);
            $table->integer('used')->default(0);
            $table->enum('status', ['active', 'inactive', 'expired'])->default('active');
            $table->timestamps();
            
            $table->index(['event_id', 'org_id']);
            $table->index(['membership_id', 'code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('membership_codes');
    }
};
