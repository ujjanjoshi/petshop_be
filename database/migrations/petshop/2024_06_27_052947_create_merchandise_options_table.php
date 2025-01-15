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
        Schema::connection('mysql_resource_db')->create('merchandise_options', function (Blueprint $table) {
            $table->id();
            $table->string('product_id')->nullable();
            $table->string('name')->nullable();
            $table->string('model')->nullable();
            $table->string('upc')->nullable();
            $table->string('status')->nullable();
            $table->string('size')->nullable();
            $table->string('color')->nullable();
            $table->string("label1")->nullable();
            $table->string("value1")->nullable();
            $table->string("label2")->nullable();
            $table->string("value2")->nullable();
            $table->string("label3")->nullable();
            $table->string("value3")->nullable();
            $table->string('image_lo')->nullable();
            $table->string('image_hi')->nullable();
            $table->float('upcharge_cost')->nullable();
            $table->json('resources')->nullable();
            $table->unsignedBigInteger('merchandise_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('mysql_resource_db')->dropIfExists('merchandise_options');
    }
};
