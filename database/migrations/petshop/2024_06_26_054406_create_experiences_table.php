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
        Schema::connection('mysql_resource_db')->create('experiences', function (Blueprint $table) {
                $table->id();
                $table->string('experience_id')->nullable();
                $table->string('sku')->nullable();
                $table->string('name')->nullable();
                $table->text('description')->nullable();
                $table->text('short_desc')->nullable();
                $table->string('image')->nullable();
                $table->string('thumbnail')->nullable();
                $table->decimal('retail_price', 10, 2)->nullable();
                $table->decimal('wholesale_price', 10, 2)->nullable();
                $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('mysql_resource_db')->dropIfExists('experiences');
    }
};
