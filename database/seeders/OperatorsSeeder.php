<?php

namespace Database\Seeders;

use App\Models\OperatorModel;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class OperatorsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $operators = [
            [
                'reference' => 'iex',
                'name' => 'IEX',
            ],
        ];

        foreach ($operators as $operator) {
            OperatorModel::create($operator);
        }
    }
}
