<?php

namespace Database\Seeders;

use App\Models\HttpSourceModel;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class HttpSourcesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $http_sources = [
            [
                'reference' => 'iex',
                'name' => 'IEX Cloud API',
                'operator_id' => 1,
            ],
        ];

        foreach ($http_sources as $http_source) {
            HttpSourceModel::create($http_source);
        }
    }
}
