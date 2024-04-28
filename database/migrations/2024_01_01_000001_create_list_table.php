<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('info_list', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('(UUID())'));
            $table->string("accountId", 64);

            $table->string("email", 255);
            $table->string("pass", 255);
            $table->string("auth", 64);

            $table->string("companyName", 64)->nullable();
            $table->string("kassaName", 255)->nullable();
            $table->string("kassaId", 20)->nullable();
            $table->string("departmentId", 20)->nullable();
            $table->string("factory", 20)->nullable();
            $table->string("full_name", 255)->nullable();



            $table->boolean("isActivity")->nullable();
            $table->boolean("addKassa")->nullable();

            $table->foreign('accountId')->references('accountId')->on('main_settings')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('main_settings');
    }
};
