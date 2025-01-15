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
        Schema::connection('mysql_resource_db')->create('rental_rates', function (Blueprint $table) {
            $table->id();
            $table->integer('rate_id');
            $table->unsignedBigInteger("rental_id");
            $table->string("begin_date");
            $table->string("end_date");
            $table->string("name");
            $table->string("min_stay");
            $table->string("note");
            $table->string("amount");
            $table->string("type");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('mysql_resource_db')->dropIfExists('rental_rates');
    }
};
