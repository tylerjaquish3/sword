<?php

namespace App\Console\Commands;

use App\Jobs\GenerateNotifications as GenerateNotificationsJob;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class GenerateNotifications extends Command
{
    protected $signature = 'notifications:generate {--user= : Only generate for a specific user ID}';

    protected $description = 'Generate notifications for all active users (or a specific user)';

    public function handle(): void
    {
        $userId = $this->option('user') ? (int) $this->option('user') : null;

        Log::info('notifications:generate started', ['user_id' => $userId ?? 'all']);

        dispatch_sync(new GenerateNotificationsJob($userId));

        Log::info('notifications:generate completed', ['user_id' => $userId ?? 'all']);
        $this->info('Done.');
    }
}
