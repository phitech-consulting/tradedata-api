<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        if(config('tda.schedule_retrieve_iex_symbol_set') == 1) {
            $schedule->command('iex:download_symbol_set')->cron(config('tda.frequency_retrieve_iex_symbol_set'));
        }

        if(config('tda.schedule_insert_exchange_products_from_iex') == 1) {
            $schedule->command('exchange_product:insert_iex_symbol_set')->cron(config('tda.frequency_insert_exchange_products_from_iex'));
        }

        if(config('tda.schedule_download_all_quotes') == 1) {
            $schedule->command('iex:download_all_quotes')->cron(config('tda.frequency_download_all_quotes'));
        }

    }

    /**
     * Register the commands for the application.
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
