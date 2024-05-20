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
        Schema::table('exchange_products', function (Blueprint $table) {
            // Drop the unique index from the 'figi' column
            $table->dropUnique(['figi']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('exchange_products', function (Blueprint $table) {
            // Re-add the unique index to the 'figi' column
            $table->unique('figi');
        });
    }
};
