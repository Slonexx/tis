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
        Schema::table('add_setting_models', function (Blueprint $table) {
            $table->string('OperationCash')->after('paymentDocument')->nullable();
            $table->string('OperationCard')->after('paymentDocument')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('add_setting_models', function (Blueprint $table) {
            $table->dropColumn('OperationCash');
            $table->dropColumn('OperationCard');
        });
    }
};
