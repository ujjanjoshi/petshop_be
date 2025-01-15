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
        Schema::connection('mysql_resource_db')->table('ticket_productions', function (Blueprint $table) {
            $table->integer('configuration_id', false, true);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('mysql_resource_db')->table('ticket_productions', function (Blueprint $table) {
            $table->dropColumn('configuration_id');
        });
    }
};
