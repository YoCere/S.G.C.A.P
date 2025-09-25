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
        \App\Console\Commands\GenerateMonthlyDebts::class,
        \App\Console\Commands\UpdateDebtsStatus::class, // ← AÑADIR ESTA LÍNEA
    ];

    protected function schedule(Schedule $schedule)
    {
        $schedule->command('debts:generate-monthly')->monthlyOn(1, '02:00');
        $schedule->command('debts:update-status')->dailyAt('01:00');
    }

    protected function commands()
    {
        $this->load(__DIR__.'/Commands');
        require base_path('routes/console.php');
    }
}