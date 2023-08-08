<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAutomationModelsTable extends Migration
{

    public function up(): void
    {
        Schema::create('automation_models', function (Blueprint $table) {
            $table->id();
            $table->string('accountId')->nullable();

            $table->string('entity')->nullable();
            $table->string('status')->nullable();
            $table->string('payment')->nullable();
            $table->string('saleschannel')->nullable();
            $table->string('project')->nullable();

            $table->timestamps();
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('automation_models');
    }
}
