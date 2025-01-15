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
        Schema::connection('mysql_resource_db')->create('experience_categories', function (Blueprint $table) {
            $table->id();
            $table->string('category_id')->nullable();
            $table->string('name')->nullable();
            $table->string('image')->nullable();
            $table->integer('parent_id')->nullable();
            $table->integer('count')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('mysql_resource_db')->dropIfExists('experience_categories');
    }
};
