<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('backup:run --only-db --only-to-disk=dropbox')->daily();
        // $schedule->command('backup:run')->monthly();
        $schedule->call('App\Http\Controllers\AutomatedJobsController@revertMonthlySavings')->dailyAt('01:00');
        // $schedule->call('App\Http\Controllers\InitialImportController@doInitialImport')->everyMinute();
        $schedule->call('App\Http\Controllers\IppisDeductionsExportController@generateIPPIDDeductionFile')->everyMinute();
        $schedule->call('App\Http\Controllers\IppisDeductionsImportController@reconcileIppisImport')->everyMinute();
        $schedule->call('App\Http\Controllers\LedgerSnapShotController@generateLedgerSnapShot')->dailyAt('01:00');
        // $schedule->call('App\Http\Controllers\TempActivityLogController@moveFromTempToActual')->everyMinute();
        $schedule->command('queue:work --tries=3')->everyMinute()->withoutOverlapping();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
