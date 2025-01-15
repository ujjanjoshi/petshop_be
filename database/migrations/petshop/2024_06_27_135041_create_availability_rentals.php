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
        Schema::connection('mysql_resource_db')->create('rental_availability', function (Blueprint $table) {
            $table->id();
            $table->integer('availability_id');
            $table->unsignedBigInteger("rental_id");
            $table->string("begin_date");
            $table->string("end_date");
            $table->string("availability_total");
            $table->string("change_over");
            $table->string("min_prior_notify");
            $table->string("max_stay");
            $table->string("min_stay");
            $table->string("stay_increment");
            $table->text("minStay");
            $table->text("changeOver");
            $table->text("availability");
            $table->text("minPriorNotify");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('mysql_resource_db')->dropIfExists('rental_availability');
    }
};
