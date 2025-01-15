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
        Schema::connection('mysql_pet_shop')->create('not_allowed_merchandises', function (Blueprint $table) {
            $table->id();
            $table->integer('merchandise_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('mysql_pet_shop')->dropIfExists('not_allowed_merchandises');
    }
};
