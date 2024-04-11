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
        Schema::create('iex_historic_stock_quotes', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->date('date'); // This should have been indexed. Make new migration and create index.
            $table->string('symbol', 16)->index();
            $table->jsonb('quote_data'); // May be indexed, as it is a jsonb column. However, don't index anything yet. Later on add index if needed, based on functional requirements.
            $table->unique(['date', 'symbol']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('iex_historic_stock_quotes');
    }
};
