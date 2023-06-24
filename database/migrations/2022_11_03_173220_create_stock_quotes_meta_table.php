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
        Schema::create('stock_quotes_meta', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->unsignedBigInteger('stock_quote_id');
            $table->foreign('stock_quote_id')->references('id')->on('stock_quotes')->onUpdate('cascade')->onDelete('cascade');
            $table->string('meta_key', 255)->nullable();
            $table->longText('meta_value')->nullable();
            $table->unique(['stock_quote_id', 'meta_key']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('stock_quotes_meta');
    }
};
