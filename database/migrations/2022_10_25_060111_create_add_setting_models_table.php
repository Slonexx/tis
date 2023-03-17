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
        Schema::create('add_setting_models', function (Blueprint $table) {
            $table->id();
            $table->string('accountId');
            $table->foreign('accountId')->references('accountId')->on('main_settings')->cascadeOnDelete();
            $table->string('idKassa')->nullable();
            $table->string('idDepartment')->nullable();
            $table->string('paymentDocument')->nullable();
            $table->string('payment_type')->nullable();
            $table->string('OperationCash')->nullable();
            $table->string('OperationCard')->nullable();
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
        Schema::dropIfExists('add_setting_models');
    }
};
