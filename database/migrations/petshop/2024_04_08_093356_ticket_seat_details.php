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
        Schema::connection('mysql_resource_db')->create('ticket_seat_details', function (Blueprint $table) {
            $table->id();
            $table->string('category');
            $table->string('section');
            $table->string('row');
            $table->string('first_seat');


        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('mysql_resource_db')->dropIfExists('ticket_seat_details');
    }
};
