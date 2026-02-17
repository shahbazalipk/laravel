<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('category_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->nullable();
            $table->string('color')->nullable();
            $table->text('description')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('event_id');
            $table->unsignedBigInteger('org_id');
            $table->timestamps();

            $table->index(['event_id', 'org_id']);
        });

        // Pivot table for registration_category and category_type
        Schema::create('category_type_registration_category', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('registration_category_id');
            $table->unsignedBigInteger('category_type_id');
            $table->timestamps();

            $table->foreign('registration_category_id', 'ct_rc_reg_cat_id_foreign')
                ->references('id')
                ->on('registration_categories')
                ->onDelete('cascade');
            
            $table->foreign('category_type_id', 'ct_rc_cat_type_id_foreign')
                ->references('id')
                ->on('category_types')
                ->onDelete('cascade');

            $table->unique(['registration_category_id', 'category_type_id'], 'ct_rc_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('category_type_registration_category');
        Schema::dropIfExists('category_types');
    }
};
