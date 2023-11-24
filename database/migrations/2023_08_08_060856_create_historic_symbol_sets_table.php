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
        Schema::create('historic_symbol_sets', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->date('date')->unique();
            $table->longText('metadata');
            $table->longText('duplicate_figis'); // Stores a gzcompress() of the found FIGI-duplicates in JSON format.
            $table->longText('symbols'); // Stores a gzcompress() of all symbols in JSON format.
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('historic_symbol_sets');
    }
};
