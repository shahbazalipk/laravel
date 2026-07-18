<?php

namespace Tests\Support;

use Illuminate\Database\Migrations\Migration;

trait InteractsWithSubmissionSchema
{
    /** @var list<Migration> */
    private array $submissionMigrations = [];

    protected function createSubmissionTables(): void
    {
        $this->submissionMigrations = [
            require database_path('migrations/2026_07_18_040000_create_speaker_submission_core_tables.php'),
            require database_path('migrations/2026_07_18_040100_create_speaker_submission_review_tables.php'),
            require database_path('migrations/2026_07_18_040200_create_speaker_submission_delivery_tables.php'),
        ];

        foreach ($this->submissionMigrations as $migration) {
            $migration->up();
        }
    }

    protected function dropSubmissionTables(): void
    {
        foreach (array_reverse($this->submissionMigrations) as $migration) {
            $migration->down();
        }

        $this->submissionMigrations = [];
    }
}
