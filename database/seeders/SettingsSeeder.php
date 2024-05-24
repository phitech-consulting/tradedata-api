<?php

namespace Database\Seeders;

use App\Models\SettingModel;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $settings = [
            [
                'key' => 'settings_seeded_timestamp',
                'value' => now(),
                'description' => 'The settings were seeded at this date and time.',
                'active' => true,
            ],
            [
                'key' => 'example_setting',
                'value' => 'example_setting_value',
                'description' => 'Just to show the settings functionality.',
                'active' => true,
            ],
            [
                'key' => 'schedule_retrieve_iex_symbol_set',
                'value' => 1,
                'description' => 'Activate the cron for automatic daily download of IEX symbol set.',
                'active' => true,
            ],
            [
                'key' => 'frequency_retrieve_iex_symbol_set',
                'value' => '0 9 * * *',
                'description' => 'Crontab entry to determine frequency for automatic daily download of IEX symbol set.',
                'active' => true,

            ],
            [
                'key' => 'schedule_insert_exchange_products_from_iex',
                'value' => 1,
                'description' => 'Activate the cron for automatic inserting of ExchangeProducts from latest IexSymbolSet.',
                'active' => true,
            ],
            [
                'key' => 'frequency_insert_exchange_products_from_iex',
                'value' => '15 9 * * *',
                'description' => 'Crontab entry to determine frequency for automatic inserting of ExchangeProducts from latest IexSymbolSet.',
                'active' => true,
            ],
            [
                'key' => 'iex_max_requests_per_second',
                'value' => 5,
                'description' => 'Sets the maximum number of requests per second for IEX API.',
                'active' => true,
            ],
            [
                'key' => 'iex_max_quotes_if_appdebug',
                'value' => 10,
                'description' => 'Sets the maximum number of StockQuotes to fetch in IexApi->download_all_quotes() when APP_DEBUG is enabled.',
                'active' => true,
            ],
            [
                'key' => 'schedule_download_all_quotes',
                'value' => 1,
                'description' => 'Activate the cron for automatic downloading quotes for approx. 11000 US based ExchangeProducts of today from IEX API.',
                'active' => true,
            ],
            [
                'key' => 'frequency_download_all_quotes',
                'value' => "30 21 * * 1-5",
                'description' => 'Crontab entry to determine frequency for automatic downloading quotes for approx. 11000 US based ExchangeProducts of today from IEX API.',
                'active' => true,
            ],
        ];

        foreach ($settings as $setting) {
            SettingModel::create($setting);
        }
    }
}
