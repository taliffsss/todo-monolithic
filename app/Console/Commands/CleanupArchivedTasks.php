<?php

namespace App\Console\Commands;

use App\Jobs\DeleteOldArchivedTasks;
use Illuminate\Console\Command;

class CleanupArchivedTasks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:cleanup-archived-tasks';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete archived tasks that are older than one week';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Archived tasks cleanup job has been dispatched.');
        DeleteOldArchivedTasks::dispatch();
    }
}
