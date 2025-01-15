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
        Schema::connection('mysql_resource_db')->create('merchandise_resources', function (Blueprint $table) {
            $table->id();
            $table->longText('brandResourceLink')->nullable();
            $table->string('brandResourceName')->nullable();
            $table->unsignedBigInteger('merchandise_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('mysql_resource_db')->dropIfExists('merchandise_resources');
    }
};
