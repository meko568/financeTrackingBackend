<?php

namespace App\Console;

use App\Jobs\SendWeeklySummaryEmail;
use App\Models\User;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        $schedule->call(function () {
            User::chunk(100, function ($users) {
                foreach ($users as $user) {
                    SendWeeklySummaryEmail::dispatch($user);
                }
            });
        })->weekly()->onOneServer();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
