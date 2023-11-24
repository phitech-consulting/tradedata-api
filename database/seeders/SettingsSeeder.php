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
        ];

        foreach ($settings as $setting) {
            SettingModel::create($setting);
        }
    }
}
