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
        Schema::connection('mysql_resource_db')->create('experience_cities', function (Blueprint $table) {
            $table->integer('id');
            $table->string('name')->nullable();
            $table->string('state')->nullable();
            $table->integer('state_id')->nullable();
            $table->string('country')->nullable();
            $table->integer('country_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('mysql_resource_db')->dropIfExists('experience_cities');
    }
};
