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
        Schema::connection('mysql_resource_db')->create('ticket_details', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->integer('seller_id');
            $table->string('seller_name');
            $table->integer('ticket_id');

            $table->string('event_id');

            $table->integer('ticket_sale_id');

            $table->integer('seat_details_id');

            $table->integer('face_value_id');

            $table->integer('proceed_price_id');

            $table->integer('restrictions_benefits_id');

            $table->integer('delivery_id');

            $table->integer('face_value_percentage');
           
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('mysql_resource_db')->dropIfExists('ticket_details');
    }
};
