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
        Schema::table('email_templates', function (Blueprint $table) {
            // Drop the global unique constraint on slug
            $table->dropUnique('email_templates_slug_unique');
            
            // Add composite unique constraint for slug per event/org
            $table->unique(['slug', 'event_id', 'org_id'], 'email_templates_slug_event_org_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('email_templates', function (Blueprint $table) {
            // Drop the composite unique constraint
            $table->dropUnique('email_templates_slug_event_org_unique');
            
            // Restore the global unique constraint
            $table->unique('slug');
        });
    }
};
