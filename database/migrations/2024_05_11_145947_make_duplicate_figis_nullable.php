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
        Schema::table('iex_historic_symbol_sets', function (Blueprint $table) {
            $table->longText('duplicate_figis')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('iex_historic_symbol_sets', function (Blueprint $table) {
            $table->longText('duplicate_figis')->nullable(false)->change();
        });
    }
};
