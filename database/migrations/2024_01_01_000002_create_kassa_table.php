<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('info_list', function (Blueprint $table) {
            $table->string('accountId')->unique()->primary();
            $table->string('tokenMs');
            $table->string('authtoken')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('main_settings');
    }
};
