<?php

declare(strict_types=1);

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

final class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */

    protected function schedule(Schedule $schedule): void
    {
//        this is orginal
        /*// Clean activity logs older than 10 days (custom command)
        $schedule->command('logs:clean-requests --days=10')->daily();

        // Daily DB backup at 01:00 AM
        $schedule->command('backup:run --only-db')->dailyAt('01:00');

        // Keep only backups from the last 5 days - clean at 01:10 AM
        $schedule->command('backup:clean')->dailyAt('01:10');

        // Clean spatie activity logs older than 30 days (default)
        $schedule->command('activitylog:clean --days=10')->daily();

        // Monitor backup health daily at 01:20 AM
        // $schedule->command('backup:monitor')->dailyAt('01:20');

        // for test
        $schedule->call(function () {
            \Log::info('✅ Scheduler test at ' . now()->toDateTimeString());
        })->everyMinute();*/


        //for test
        // Test backup command every 5 minutes instead of 01:00 AM
        $schedule->command('backup:run --only-db')
//            ->dailyAt('01:00')
            ->everyFiveMinutes()
            ->onSuccess(function () {
                \Log::info('✅ Backup completed successfully at ' . now());
            })
            ->onFailure(function () {
                \Log::error('❌ Backup failed at ' . now());
            });

        // Test clean command every 5 minutes
        $schedule->command('backup:clean')
//            ->dailyAt('01:10')
            ->everyFiveMinutes()
            ->onSuccess(function () {
                \Log::info('✅ Backup clean successfully at ' . now());
            })
            ->onFailure(function () {
                \Log::error('❌ Backup clean failed at ' . now());
            });

        // Test activity log clean every 5 minutes
        $schedule->command('activitylog:clean --days=10')
            ->everyFiveMinutes()
//            ->dailyAt('01:20')
            ->onSuccess(function () {
                \Log::info('✅ activitylog clean successfully at ' . now());
            })
            ->onFailure(function () {
                \Log::error('❌ activitylog clean failed at ' . now());
            });

        // Test custom command every 5 minutes
        $schedule->command('logs:clean-requests --days=10')
            ->everyFiveMinutes()
//            ->dailyAt('01:30')
            ->onSuccess(function () {
                \Log::info('✅ Manual log clean successfully at ' . now());
            })
            ->onFailure(function () {
                \Log::error('❌ Manual log clean failed at ' . now());
            });

        $schedule->call(function () {
            \Log::info('Environment: ' . app()->environment());
            \Log::info('Timezone: ' . config('app.timezone'));
            \Log::info('Current time: ' . now()->toDateTimeString());
        })->everyMinute();
        // Keep your test callback
        $schedule->call(function () {
            \Log::info('✅ Scheduler test at ' . now()->toDateTimeString());
        })->everyMinute();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        // Automatically load all Artisan commands in app/Console/Commands
        $this->load(__DIR__ . '/Commands');

        // Optionally load specific command files
        // require base_path('routes/console.php');
    }
}
