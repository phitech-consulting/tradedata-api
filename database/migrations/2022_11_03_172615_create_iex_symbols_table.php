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
        Schema::create('iex_symbols', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('symbol', 64);
            $table->string('exchange', 64);
            $table->string('exchange_suffix', 64);
            $table->string('exchange_name', 64);
            $table->string('exchange_segment', 64);
            $table->string('exchange_segment_name', 64);
            $table->string('name', 1024);
            $table->date('date');
            $table->string('type', 64);
            $table->string('iex_id', 64);
            $table->string('region', 64);
            $table->string('currency', 64);
            $table->string('is_enabled', 64);
            $table->string('figi', 64);
            $table->string('cik', 64);
            $table->string('lei', 64);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('iex_symbols');
    }
};
