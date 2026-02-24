<?php

namespace App\Console\Commands;

use App\Models\Registration;
use App\Models\RegistrationCategory;
use App\Models\HashMapping;
use App\Services\HashService;
use Illuminate\Console\Command;

class GenerateMissingHashes extends Command
{
    protected $signature = 'hash:generate-missing {model?}';
    protected $description = 'Generate missing hash mappings for models';

    public function handle()
    {
        $modelType = $this->argument('model');
        
        if (!$modelType || $modelType === 'Registration') {
            $this->generateForModel(Registration::class, 'Registrations');
        }
        
        if (!$modelType || $modelType === 'RegistrationCategory') {
            $this->generateForModel(RegistrationCategory::class, 'Registration Categories');
        }
        
        $this->info('Done!');
    }
    
    private function generateForModel($modelClass, $displayName)
    {
        $this->info("Processing {$displayName}...");
        
        $hashService = app(HashService::class);
        
        // Get all records without hash mappings
        $records = $modelClass::withoutGlobalScopes()
            ->whereNotIn('id', function($query) use ($modelClass) {
                $query->select('model_id')
                    ->from('hash_mappings')
                    ->where('model_type', $modelClass);
            })
            ->get();
        
        if ($records->isEmpty()) {
            $this->info("No missing hashes for {$displayName}");
            return;
        }
        
        $this->info("Found {$records->count()} records without hashes");
        
        $bar = $this->output->createProgressBar($records->count());
        $bar->start();
        
        foreach ($records as $record) {
            try {
                $hashService->generateHash($record);
                $bar->advance();
            } catch (\Exception $e) {
                $this->error("\nError generating hash for {$displayName} ID {$record->id}: " . $e->getMessage());
            }
        }
        
        $bar->finish();
        $this->newLine();
        $this->info("Generated {$records->count()} hashes for {$displayName}");
    }
}
