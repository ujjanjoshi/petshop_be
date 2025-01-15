<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::connection('mysql_resource_db')->create('countries', function (Blueprint $table) {
            $table->id();
            $table->string('iso');
            $table->string('name');
            $table->string('nicename');
            $table->string('iso3');
            $table->string('numcode');
            $table->string('phonecode');
            $table->enum('country_status', ['1', '0'])->default('1');
            $table->integer('traffic');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('countries');
    }
};
