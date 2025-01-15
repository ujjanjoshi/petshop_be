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
        Schema::connection('mysql_resource_db')->create('experience_states', function (Blueprint $table) {
            $table->integer('id');
            $table->string('name')->nullable();
            $table->string('code')->nullable();
            $table->integer('country_id')->nullable();
            $table->string('country_name')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('mysql_resource_db')->dropIfExists('experience_states');
    }
};
