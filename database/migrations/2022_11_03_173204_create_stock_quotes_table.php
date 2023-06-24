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
        Schema::create('stock_quotes', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->date('date');
            $table->string('type', 32);
            $table->string('source', 8);
            $table->unsignedBigInteger('symbol_id');
            $table->foreign('symbol_id')->references('id')->on('iex_symbols')->onUpdate('cascade')->onDelete('cascade');
            $table->unique(['date', 'symbol_id', 'source', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('stock_quotes');
    }
};
