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
        Schema::connection('mysql_resource_db')->create('ticket_productions_performers', function (Blueprint $table) {
            $table->string('event_id')->index();
            $table->string('performer_id')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('mysql_resource_db')->dropIfExists('ticket_productions_performers');
    }
};
