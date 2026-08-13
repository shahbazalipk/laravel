<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('registration_saved_views', function (Blueprint $table): void {
            $table->id();
            $table->uuid('public_id')->unique();
            $table->unsignedBigInteger('event_id')->index();
            $table->unsignedBigInteger('org_id')->index();
            $table->unsignedBigInteger('organization_admin_user_id')->index();
            $table->string('name');
            $table->string('visibility', 20)->default('private');
            $table->boolean('is_default')->default(false);
            $table->json('columns');
            $table->timestamps();
            $table->softDeletes();

            $table->unique(
                ['event_id', 'org_id', 'organization_admin_user_id', 'name'],
                'registration_saved_views_owner_name_unique'
            );
            $table->index(['event_id', 'org_id', 'visibility']);
        });

        Schema::create('registration_saved_view_shares', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('registration_saved_view_id');
            $table->unsignedBigInteger('organization_admin_user_id');
            $table->timestamps();

            $table->unique(
                ['registration_saved_view_id', 'organization_admin_user_id'],
                'registration_saved_view_shares_unique'
            );
            $table->foreign('registration_saved_view_id', 'rsv_shares_view_fk')
                ->references('id')
                ->on('registration_saved_views')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('registration_saved_view_shares');
        Schema::dropIfExists('registration_saved_views');
    }
};
