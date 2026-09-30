<?php

namespace App\Console\Commands;

use App\Services\SyncService;
use Illuminate\Console\Command;

class SyncRun extends Command
{
    protected $signature = 'sync:run';

    protected $description = 'Push queued offline changes to the remote server once internet is available';

    public function handle(SyncService $sync): int
    {
        $synced = $sync->run();

        $this->info("Synced {$synced} event(s).");

        return self::SUCCESS;
    }
}