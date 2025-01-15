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
        Schema::connection('mysql_resource_db')->create('ticket_performers', function (Blueprint $table) {
            $table->id();
            $table->string('performer_id');
            $table->string('name')->nullable();
            $table->timestamps();
            $table->integer('events_count')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('mysql_resource_db')->dropIfExists('ticket_performers');
    }
};
