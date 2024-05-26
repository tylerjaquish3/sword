<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\FetchVerses as FetchVersesJob;

class FetchVerses extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'fetchVerses';

    /**
     * The console command description.
     */
    protected $description = 'Get books, chapters, verses from bible api';

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
        dispatch_sync(new FetchVersesJob);
    }
}
