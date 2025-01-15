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
        Schema::connection('mysql_pet_shop')->create('order_histories', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_id');
            $table->string('product_title');
            $table->string('sku')->nullable();
            $table->integer('quantity');
            $table->decimal('retail_price', 10, 2);
            $table->string('user_id');
            $table->string('ticket_id')->nullable();
            $table->string('session_id');
            $table->decimal('total_price', 10, 2);
            $table->string('type_of_payment');
            $table->string('last_four_digit');
            $table->string('hotel_id')->nullable();
            $table->timestamps();
            $table->string('invoice');
            $table->string('cretificate_code');
            $table->string('product_id')->nullable();
            $table->string('shipping_id')->nullable();
            $table->string('property_id')->nullable();
           
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('mysql_pet_shop')->dropIfExists('order_histories');
    }
};
