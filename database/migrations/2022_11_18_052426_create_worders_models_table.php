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
        Schema::create('worders_models', function (Blueprint $table) {
            $table->string('id')->unique()->primary();
            $table->string('accountId');
            $table->foreign('accountId')->references('accountId')->on('main_settings')->cascadeOnDelete();
            $table->boolean('access');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('worders_models');
    }
};
