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
        Schema::connection('mysql_resource_db')->create('tour_destinations', function (Blueprint $table) {
            $table->unsignedInteger('id')->primary();
            $table->unsignedBigInteger('parent_id');
            $table->string('lookup_id');
            $table->string('type');
            $table->string('name');
            $table->string('latitude');
            $table->string('longitude');
            $table->string('timezone');
            $table->string('iata_code');
            $table->string('currency_code');
        
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('mysql_resource_db')->dropIfExists('tour_destinations');
    }
};
