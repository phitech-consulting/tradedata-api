<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('import_plans', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->date('date')->unique();
            $table->string('callback')->nullable(); // ['get_srv1_quote', 'get_iex_historic_quote']
            $table->tinyInteger('done')->default(0); // [0,1]
            $table->date('symbol_set'); // Date in YYYY-MM-DD
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('import_plans');
    }
};
