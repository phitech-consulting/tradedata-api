<?php

namespace Database\Seeders;

use App\Models\ImportPlanModel;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ImportPlansSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $import_plans = [
            [
                'date' => 'iex',
                'callback' => 'IEX',
                'done' => 'IEX',
                'symbol_set' => 'IEX',
            ],
        ];

        foreach ($import_plans as $import_plan) {
            ImportPlanModel::create($import_plan);
        }
    }
}
