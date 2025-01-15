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
        Schema::connection('mysql_pet_shop')->create('brandings', function (Blueprint $table) {
            $table->id();
            $table->string("header_logo");
            $table->string("footer_logo");
            $table->string("address");
            $table->string("phone_number");
            $table->string("trade_mark");
            $table->string("term_policy");
            $table->string("header_color");
            $table->string("footer_color");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('mysql_pet_shop')->dropIfExists('brandings');
    }
};
