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
        Schema::create('http_sources', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('reference', 16)->unique();
            $table->string('name', 128);
            $table->unsignedBigInteger('operator_id');
            $table->foreign('operator_id')->references('id')->on('operators');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('http_sources');
    }
};
