<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\KeplinVerses as KeplinVersesJob;

class KeplinVerses extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'keplinVerses';

    /**
     * The console command description.
     */
    protected $description = 'Get books, chapters, verses from Keplin api';

    /**
     * Create a new command instance.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        dispatch_sync(new KeplinVersesJob);
    }
}
