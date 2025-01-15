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
        Schema::connection('mysql_resource_db')->create('tickets', function (Blueprint $table) {
            $table->id();
            $table->string('general_admission')->nullable();
            $table->string('type')->nullable();
            $table->string('allow_last_minute_sales')->nullable();
            $table->string('split_type')->nullable();
            $table->integer('etickets')->nullable();
            $table->string('upload_later')->nullable();
            $table->string('instant_download')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('mysql_resource_db')->dropIfExists('tickets');
    }
};
