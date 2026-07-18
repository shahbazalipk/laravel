<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->swapSteps();
    }

    public function down(): void
    {
        $this->swapSteps();
    }

    /**
     * Preserve the meaning of current_step for registrations already in progress.
     *
     * Before this migration:
     * information = email completed; category = information completed.
     *
     * After this migration:
     * category = email completed; information = category completed.
     */
    private function swapSteps(): void
    {
        if (! Schema::hasTable('registration_drafts')
            || ! Schema::hasColumn('registration_drafts', 'current_step')) {
            return;
        }

        DB::transaction(function (): void {
            DB::table('registration_drafts')
                ->where('current_step', 'information')
                ->update(['current_step' => '__swap_information__']);

            DB::table('registration_drafts')
                ->where('current_step', 'category')
                ->update(['current_step' => 'information']);

            DB::table('registration_drafts')
                ->where('current_step', '__swap_information__')
                ->update(['current_step' => 'category']);
        });
    }
};
