<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;
use App\Models\SettingModel;

class TdaServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {

    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Load TDA config via config file.
        Config::set('tda', require config_path('tda.php'));

        // Load other TDA settings via settings table in database (such as schedule_retrieve_iex_symbol_set).
        if (Schema::hasTable('settings')) {
            $settings = SettingModel::all();
            foreach ($settings as $setting) {
                Config::set('tda.' . $setting->key, $setting->value);
            }
        }
    }
}
