<?php

namespace App\Console\Commands;

use App\Jobs\ImportEsvVerses as ImportEsvVersesJob;
use Illuminate\Console\Command;

class ImportEsvVerses extends Command
{
    protected $signature = 'esv:import {--limit=75 : Number of chapters to fetch per run}';

    protected $description = 'Import ESV verses from BibleGateway, resuming from where the last run left off';

    public function handle(): void
    {
        $limit = (int) $this->option('limit');

        $this->info("Importing up to {$limit} chapters of ESV...");

        dispatch_sync(new ImportEsvVersesJob($limit));

        $this->info('Done. Run again to continue importing.');
    }
}
