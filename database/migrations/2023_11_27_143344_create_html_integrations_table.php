<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('html_integrations', function (Blueprint $table) {
            $table->id();
            $table->string('accountId');
            $table->string('kkm_id');
            $table->mediumText('html');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('html_integrations');
    }
};
