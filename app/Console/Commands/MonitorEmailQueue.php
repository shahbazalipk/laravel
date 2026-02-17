<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Redis;

class MonitorEmailQueue extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'queue:monitor-emails';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Monitor the email queue status and display pending/failed jobs';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Email Queue Status');
        $this->info('==================');
        $this->newLine();
        
        // Get queue connection
        $connection = config('queue.default');
        $this->line("Queue Connection: <fg=cyan>{$connection}</>");
        $this->newLine();
        
        // Display pending jobs
        $this->displayPendingJobs();
        
        // Display failed jobs
        $this->displayFailedJobs();
        
        // Display queue size if using Redis
        if ($connection === 'redis') {
            $this->displayRedisQueueSize();
        }
        
        return Command::SUCCESS;
    }
    
    /**
     * Display pending jobs in the emails queue
     */
    protected function displayPendingJobs(): void
    {
        $connection = config('queue.default');
        
        if ($connection === 'database') {
            $pendingJobs = DB::table('jobs')
                ->where('queue', 'emails')
                ->count();
                
            $this->line("Pending Jobs (emails queue): <fg=yellow>{$pendingJobs}</>");
            
            if ($pendingJobs > 0) {
                $oldestJob = DB::table('jobs')
                    ->where('queue', 'emails')
                    ->orderBy('created_at', 'asc')
                    ->first();
                    
                if ($oldestJob) {
                    $age = now()->diffForHumans($oldestJob->created_at);
                    $this->line("Oldest pending job: <fg=gray>{$age}</>");
                }
            }
        } elseif ($connection === 'redis') {
            try {
                $queueName = 'queues:emails';
                $pendingJobs = Redis::llen($queueName);
                $this->line("Pending Jobs (emails queue): <fg=yellow>{$pendingJobs}</>");
            } catch (\Exception $e) {
                $this->error("Could not connect to Redis: {$e->getMessage()}");
            }
        } else {
            $this->line("Pending Jobs: <fg=gray>N/A (connection type: {$connection})</>");
        }
        
        $this->newLine();
    }
    
    /**
     * Display failed jobs
     */
    protected function displayFailedJobs(): void
    {
        $failedJobs = DB::table('failed_jobs')->count();
        $this->line("Failed Jobs (all queues): <fg=red>{$failedJobs}</>");
        
        if ($failedJobs > 0) {
            $recentFailed = DB::table('failed_jobs')
                ->orderBy('failed_at', 'desc')
                ->limit(5)
                ->get();
                
            $this->newLine();
            $this->line('<fg=red>Recent Failed Jobs:</>');
            
            $headers = ['ID', 'Queue', 'Failed At', 'Exception'];
            $rows = [];
            
            foreach ($recentFailed as $job) {
                $exception = $this->truncateException($job->exception);
                $rows[] = [
                    substr($job->uuid, 0, 8) . '...',
                    $job->queue,
                    \Carbon\Carbon::parse($job->failed_at)->diffForHumans(),
                    $exception,
                ];
            }
            
            $this->table($headers, $rows);
            
            $this->newLine();
            $this->line('<fg=yellow>Tip:</> Use <fg=cyan>php artisan queue:retry all</> to retry failed jobs');
        }
        
        $this->newLine();
    }
    
    /**
     * Display Redis queue size information
     */
    protected function displayRedisQueueSize(): void
    {
        try {
            $this->line('<fg=cyan>Redis Queue Details:</>');
            
            // Check emails queue
            $emailsQueue = Redis::llen('queues:emails');
            $this->line("  - emails queue: {$emailsQueue} jobs");
            
            // Check default queue
            $defaultQueue = Redis::llen('queues:default');
            $this->line("  - default queue: {$defaultQueue} jobs");
            
            // Check delayed jobs
            $delayedJobs = Redis::zcard('queues:emails:delayed');
            $this->line("  - delayed jobs: {$delayedJobs}");
            
            // Check reserved jobs
            $reservedJobs = Redis::zcard('queues:emails:reserved');
            $this->line("  - reserved jobs: {$reservedJobs}");
            
            $this->newLine();
        } catch (\Exception $e) {
            $this->error("Could not retrieve Redis queue details: {$e->getMessage()}");
        }
    }
    
    /**
     * Truncate exception message for display
     */
    protected function truncateException(string $exception): string
    {
        $lines = explode("\n", $exception);
        $firstLine = $lines[0] ?? '';
        
        if (strlen($firstLine) > 60) {
            return substr($firstLine, 0, 57) . '...';
        }
        
        return $firstLine;
    }
}
