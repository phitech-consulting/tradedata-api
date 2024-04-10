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
        Schema::create('historic_references', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('symbol', 32);
            $table->date('date')->index();
            $table->double('diff_perc_close')->nullable();
            $table->double('diff_perc_open')->nullable();
            $table->double('diff_perc_change')->nullable();
            $table->double('diff_change_perc')->nullable();
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
        Schema::dropIfExists('historic_references');
    }
};
