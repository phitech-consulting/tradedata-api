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
            $table->date('date'); // This should have been indexed. Make new migration and create index.
            $table->string('symbol', 16)->index();
            $table->unsignedBigInteger('http_source_id');
            $table->foreign('http_source_id')->references('id')->on('http_sources');
            $table->unsignedBigInteger('average_total_volume')->index()->nullable();
            $table->unsignedBigInteger('volume')->index()->nullable();
            $table->decimal('change', 13, 3)->index()->nullable();
            $table->decimal('change_percentage', 5, 2)->index()->nullable();
            $table->decimal('change_ytd', 13, 3)->index()->nullable();
            $table->decimal('open', 13, 3)->index()->nullable();
            $table->decimal('close', 13, 3)->index()->nullable();
            $table->string('company_name', 128)->index()->nullable();
            $table->unsignedBigInteger('market_cap')->index()->nullable();
            $table->decimal('pe_ratio', 13, 3)->index()->nullable();
            $table->decimal('week_52_low', 13, 3)->index()->nullable();
            $table->decimal('week_52_high', 13, 3)->index()->nullable();
            $table->jsonb('metadata'); // May be indexed, as it is a jsonb column. However, don't index anything yet. Later on add index if needed, based on functional requirements.
            $table->unique(['date', 'symbol', 'http_source_id']);

            // Testing / Development (today quote)
//            $table->date('close_time')->nullable();
//            $table->date('delayed_price_time')->nullable();
//            $table->date('extended_price_time')->nullable();
//            $table->date('high_time')->nullable();
//            $table->date('iex_close_time')->nullable();
//            $table->date('iex_last_updated')->nullable();
//            $table->date('iex_open_time')->nullable();
//            $table->date('latest_time')->nullable();
//            $table->date('latest_update')->nullable();
//            $table->date('low_time')->nullable();
//            $table->date('open_time')->nullable();
//            $table->date('last_trade_time')->nullable();

            // Testing / Development (historic quote)
//            $table->date('price_date')->nullable();
//            $table->date('date_date')->nullable();
//            $table->date('updated')->nullable();
//            $table->date('label')->nullable();

        });
//        Schema::create('stock_quotes_meta', function (Blueprint $table) {
//            $table->id();
//            $table->timestamps();
//            $table->unsignedBigInteger('stock_quote_id')->index();
//            $table->foreign('stock_quote_id')->references('id')->on('stock_quotes')->onUpdate('cascade')->onDelete('cascade');
//            $table->string('meta_key', 255)->index();
//            $table->longText('meta_value')->nullable();
//            $table->unique(['stock_quote_id', 'meta_key']);
//        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
//        Schema::dropIfExists('stock_quotes_meta');
        Schema::dropIfExists('stock_quotes');
    }
};
