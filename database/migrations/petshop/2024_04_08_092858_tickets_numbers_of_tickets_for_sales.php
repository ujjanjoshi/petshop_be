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
        Schema::connection('mysql_resource_db')->create('tickets_numbers_of_tickets_for_sales', function (Blueprint $table) {
            $table->id();
            $table->integer('quantity_available');
            $table->integer('quantity_sold');
            $table->integer('display_quantity');
            $table->integer('split_quantity');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('mysql_resource_db')->dropIfExists('tickets_numbers_of_tickets_for_sales');
    }
};
