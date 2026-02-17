<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('admins')) {
            Schema::create('admins', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('email')->unique();
                $table->string('password')->nullable();
                $table->unsignedBigInteger('organization_id')->nullable();
                $table->unsignedBigInteger('event_id')->nullable();
                $table->timestamp('last_login_at')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->unsignedBigInteger('updated_by')->nullable();
                $table->unsignedBigInteger('deleted_by')->nullable();
                $table->rememberToken();
                $table->timestamps();
                $table->softDeletes();
                
                $table->index(['organization_id', 'event_id']);
                $table->index('email');
            });
        } else {
            // Add new columns if table exists but columns don't
            Schema::table('admins', function (Blueprint $table) {
                if (!Schema::hasColumn('admins', 'organization_id')) {
                    $table->unsignedBigInteger('organization_id')->nullable()->after('password');
                }
                if (!Schema::hasColumn('admins', 'event_id')) {
                    $table->unsignedBigInteger('event_id')->nullable()->after('organization_id');
                }
                if (!Schema::hasColumn('admins', 'last_login_at')) {
                    $table->timestamp('last_login_at')->nullable()->after('event_id');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('admins')) {
            Schema::table('admins', function (Blueprint $table) {
                if (Schema::hasColumn('admins', 'organization_id')) {
                    $table->dropColumn('organization_id');
                }
                if (Schema::hasColumn('admins', 'event_id')) {
                    $table->dropColumn('event_id');
                }
                if (Schema::hasColumn('admins', 'last_login_at')) {
                    $table->dropColumn('last_login_at');
                }
            });
        }
    }
};
