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
        Schema::connection('mysql_resource_db')->create('merchandises', function (Blueprint $table) {
            $table->id();
            $table->string('product_id')->nullable();
            $table->string('name')->nullable();
            $table->longText('description')->nullable();
            $table->string('brand')->nullable();
            $table->string('model')->nullable();
            $table->string('upc')->nullable();
            $table->float('weight')->nullable();
            $table->unsignedBigInteger('dimension_id')->nullable();
            $table->string('image_lo')->nullable();
            $table->string('image_hi')->nullable();
            $table->float('selling_price')->nullable();
            $table->string('ship_in_days')->nullable();
            $table->char('prop65')->nullable();
            $table->text('prop65_message')->nullable();
            $table->unsignedBigInteger('category_id')->nullable();  
            $table->string('updated_at_date');      
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('mysql_resource_db')->dropIfExists('merchandises');
    }
};
