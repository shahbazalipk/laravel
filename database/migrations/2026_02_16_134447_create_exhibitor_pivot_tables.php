<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Exhibitor - Business Activity (Many-to-Many)
        Schema::create('exhibitor_business_activity', function (Blueprint $table) {
            $table->unsignedBigInteger('exhibitor_id');
            $table->unsignedBigInteger('business_activity_id');
            $table->primary(['exhibitor_id', 'business_activity_id'], 'exhibitor_business_activity_primary');
            $table->index('exhibitor_id');
            $table->index('business_activity_id');
        });

        // Exhibitor - Product Type (Many-to-Many)
        Schema::create('exhibitor_product_type', function (Blueprint $table) {
            $table->unsignedBigInteger('exhibitor_id');
            $table->unsignedBigInteger('product_type_id');
            $table->primary(['exhibitor_id', 'product_type_id'], 'exhibitor_product_type_primary');
            $table->index('exhibitor_id');
            $table->index('product_type_id');
        });

        // Exhibitor - Tag (Many-to-Many)
        Schema::create('exhibitor_tag', function (Blueprint $table) {
            $table->unsignedBigInteger('exhibitor_id');
            $table->unsignedBigInteger('exhibitor_tag_id');
            $table->primary(['exhibitor_id', 'exhibitor_tag_id'], 'exhibitor_tag_primary');
            $table->index('exhibitor_id');
            $table->index('exhibitor_tag_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exhibitor_business_activity');
        Schema::dropIfExists('exhibitor_product_type');
        Schema::dropIfExists('exhibitor_tag');
    }
};
