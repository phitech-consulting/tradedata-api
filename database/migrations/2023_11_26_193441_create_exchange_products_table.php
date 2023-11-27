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
        Schema::create('exchange_products', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
//            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
//            $table->timestamp('updated_at')->nullable();
            $table->string('symbol', 16)->index();
            $table->string('exchange', 16);
            $table->string('exchange_suffix', 64);
            $table->string('exchange_name', 128);
            $table->string('exchange_segment', 16);
            $table->string('exchange_segment_name', 128);
            $table->string('name', 1024)->index();
            $table->date('date');
            $table->string('type', 16);
            $table->string('iex_id', 64);
            $table->string('region', 16);
            $table->string('currency', 16);
            $table->boolean('is_enabled');
            $table->string('figi', 64)->unique();
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
        Schema::dropIfExists('exchange_products');
    }
};
