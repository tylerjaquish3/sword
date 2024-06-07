<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\DetermineKeyWords as DetermineKeyWordsJob;

class DetermineKeyWords extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'keyWords';

    /**
     * The console command description.
     */
    protected $description = 'Sift through verses to determine key words';

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
        dispatch_sync(new DetermineKeyWordsJob);
    }
}
