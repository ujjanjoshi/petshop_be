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
        Schema::connection('mysql_resource_db')->create('hotels', function (Blueprint $table) {
            $table->id();
            $table->integer('giata_id')->nullable();
            $table->integer('code')->nullable();
            $table->string('name')->nullable();
            $table->longText('description')->nullable();
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('country')->nullable();
            $table->string('zip')->nullable();
            $table->text('phone')->nullable();
            $table->text('phones')->nullable();
            $table->string('url')->nullable();
            $table->string('email')->nullable();
            $table->string('image')->nullable();
            $table->longText('images')->nullable();
            $table->integer('rating')->nullable();
            $table->string('category_code')->nullable();
            $table->string('latitude')->nullable();
            $table->string('longitude')->nullable();
            $table->string('destination_code')->nullable();
            $table->longText('rooms')->nullable();
            $table->text('facilities')->nullable();
            $table->longText('issues')->nullable();
            $table->integer('prefer')->nullable();
            $table->string('comment')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('mysql_resource_db')->dropIfExists('hotels');
    }
};
