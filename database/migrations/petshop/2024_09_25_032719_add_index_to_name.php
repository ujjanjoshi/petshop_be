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
        Schema::connection('mysql_resource_db')->table('ticket_performers', function (Blueprint $table) {
            $table->unique('name');
            $table->unique('alt_performer_id');
        });
        Schema::connection('mysql_resource_db')->table('ticket_venues', function (Blueprint $table) {
            $table->unique('name');
            $table->index('city');
            $table->unique('alt_venue_id');
        });
        Schema::connection('mysql_resource_db')->table('ticket_productions', function (Blueprint $table) {
            $table->index('name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('mysql_resource_db')->table('ticket_performers', function (Blueprint $table) {
            $table->dropUnique('ticket_performers_name_unique');
            $table->dropUnique('ticket_performers_alt_performer_id_unique');
        });
        Schema::connection('mysql_resource_db')->table('ticket_venues', function (Blueprint $table) {
            $table->dropUnique('ticket_venues_name_unique');
            $table->dropIndex('ticket_venues_city_index');
            $table->dropUnique('ticket_venues_alt_venue_id_unique');
        });
        Schema::connection('mysql_resource_db')->table('ticket_productions', function (Blueprint $table) {
            $table->dropIndex('ticket_venues_name_index');
        });
    }
};
