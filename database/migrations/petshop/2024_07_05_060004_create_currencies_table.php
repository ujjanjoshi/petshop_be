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
        Schema::connection('mysql_resource_db')->create('currencies', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('country');
            $table->enum('default', ['1', '0'])->default('0');
            $table->enum('status', ['1', '0'])->default('0');
            $table->string('rate');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('currencies');
    }
};
