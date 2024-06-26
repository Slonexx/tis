<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('main_settings', function (Blueprint $table) {
            $table->string('accountId')->unique()->primary();
            $table->string('tokenMs');
            $table->string('authtoken')->nullable();

            $table->integer('max');
            $table->boolean('isActivity');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('main_settings');
    }
};
