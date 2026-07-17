<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('custom_forms', 'audience_unique')) {
            Schema::table('custom_forms', function (Blueprint $table) {
                $table->string('audience_unique', 32)->nullable()->after('audience');
            });
        }

        // Soft-deleted rows free the audience slot; live rows keep audience_unique = audience.
        DB::table('custom_forms')
            ->whereNull('deleted_at')
            ->update(['audience_unique' => DB::raw('audience')]);

        DB::table('custom_forms')
            ->whereNotNull('deleted_at')
            ->update(['audience_unique' => null]);

        $this->dedupeLiveAudienceForms();

        try {
            Schema::table('custom_forms', function (Blueprint $table) {
                $table->unique(
                    ['event_id', 'org_id', 'audience_unique'],
                    'custom_forms_event_org_audience_unique'
                );
            });
        } catch (\Throwable) {
            // Index already exists on re-run.
        }
    }

    public function down(): void
    {
        Schema::table('custom_forms', function (Blueprint $table) {
            try {
                $table->dropUnique('custom_forms_event_org_audience_unique');
            } catch (\Throwable) {
                // Index may already be absent.
            }

            if (Schema::hasColumn('custom_forms', 'audience_unique')) {
                $table->dropColumn('audience_unique');
            }
        });
    }

    private function dedupeLiveAudienceForms(): void
    {
        $groups = DB::table('custom_forms')
            ->whereNull('deleted_at')
            ->select('event_id', 'org_id', 'audience')
            ->groupBy('event_id', 'org_id', 'audience')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        foreach ($groups as $group) {
            $forms = DB::table('custom_forms as forms')
                ->leftJoin('custom_form_questions as questions', function ($join) {
                    $join->on('questions.custom_form_id', '=', 'forms.id')
                        ->whereNull('questions.deleted_at');
                })
                ->where('forms.event_id', $group->event_id)
                ->where('forms.org_id', $group->org_id)
                ->where('forms.audience', $group->audience)
                ->whereNull('forms.deleted_at')
                ->groupBy('forms.id', 'forms.is_active', 'forms.updated_at')
                ->orderByDesc('forms.is_active')
                ->orderByDesc(DB::raw('COUNT(questions.id)'))
                ->orderByDesc('forms.updated_at')
                ->orderBy('forms.id')
                ->select('forms.id')
                ->get();

            $losers = $forms->skip(1)->pluck('id');
            if ($losers->isEmpty()) {
                continue;
            }

            DB::table('custom_forms')
                ->whereIn('id', $losers)
                ->update([
                    'audience_unique' => null,
                    'deleted_at' => now(),
                    'updated_at' => now(),
                ]);
        }
    }
};
