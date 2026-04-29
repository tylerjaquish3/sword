<?php

namespace App\Console\Commands;

use App\Jobs\GenerateNotifications as GenerateNotificationsJob;
use Illuminate\Console\Command;

class GenerateNotifications extends Command
{
    protected $signature = 'notifications:generate {--user= : Only generate for a specific user ID}';

    protected $description = 'Generate notifications for all active users (or a specific user)';

    public function handle(): void
    {
        $userId = $this->option('user') ? (int) $this->option('user') : null;

        dispatch_sync(new GenerateNotificationsJob($userId));
    }
}
