<?php

namespace App\Console\Commands;

use App\Jobs\ImportMsgVerses as ImportMsgVersesJob;
use Illuminate\Console\Command;

class ImportMsgVerses extends Command
{
    protected $signature = 'msg:import {--limit=75 : Number of chapters to fetch per run}';

    protected $description = 'Import MSG verses from BibleGateway, resuming from where the last run left off';

    public function handle(): void
    {
        $limit = (int) $this->option('limit');

        $this->info("Importing up to {$limit} chapters of MSG...");

        dispatch_sync(new ImportMsgVersesJob($limit));

        $this->info('Done. Run again to continue importing.');
    }
}
