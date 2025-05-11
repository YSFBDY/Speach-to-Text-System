<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Console\Scheduling\Schedule;
use App\Models\Subscription;


class DeactivateExpiredSubscriptions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:deactivate-expired-subscriptions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Deactivate subscriptions whose end_date has passed';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Deactivate expired subscriptions
        Subscription::where('end_date', '<', now()->toDateString())
            ->where('subscription_status', 'active')
            ->update(['subscription_status' => 'inactive']);

        $this->info('Expired subscriptions deactivated.');
    }

    public function schedule(Schedule $schedule): void
    {
        // Run the command daily
        $schedule->command('app:deactivate-expired-subscriptions')->daily();
    }
}
