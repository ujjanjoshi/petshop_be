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
        Schema::connection('mysql_resource_db')->table('ticket_categories', function (Blueprint $table) {
            $table->string('alt_category_id')->after('category_id')->nullable();
        });
        Schema::connection('mysql_resource_db')->table('ticket_performers', function (Blueprint $table) {
            $table->string('alt_performer_id')->after('performer_id')->nullable();
        });
        Schema::connection('mysql_resource_db')->table('ticket_venues', function (Blueprint $table) {
            $table->string('alt_venue_id')->after('venue_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('mysql_resource_db')->table('ticket_categories', function (Blueprint $table) {
            $table->dropColumn('alt_category_id');
        });
        Schema::connection('mysql_resource_db')->table('ticket_performers', function (Blueprint $table) {
            $table->dropColumn('alt_performer_id');
        });
        Schema::connection('mysql_resource_db')->table('ticket_venues', function (Blueprint $table) {
            $table->dropColumn('alt_venue_id');
        });
    }
};
