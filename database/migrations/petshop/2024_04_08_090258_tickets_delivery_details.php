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
        Schema::connection('mysql_resource_db')->create('ticket_delivery_details', function (Blueprint $table) {
            $table->id();
            $table->string('type');
            $table->integer('hand_delivered');
            $table->date('shipped_date_or_date_in_hand');
           

           
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('mysql_resource_db')->dropIfExists('ticket_delivery_details');
    }
};
