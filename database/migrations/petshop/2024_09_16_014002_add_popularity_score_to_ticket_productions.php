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
            $table->dateTime('occurred_at')->index()->default('1970-01-01 00:00:01')->change();
            $table->decimal('popularity_score', 14, 6)->default(0)->index();
            $table->index('production_id');
            $table->index('category_id');
            $table->index('venue_id');
        });
        Schema::connection('mysql_resource_db')->table('ticket_performers', function (Blueprint $table) {
            $table->decimal('popularity_score', 14, 6)->default(0)->index();
            $table->index('performer_id');
        });
        Schema::connection('mysql_resource_db')->table('ticket_venues', function (Blueprint $table) {
            $table->decimal('popularity_score', 14, 6)->default(0)->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('mysql_resource_db')->table('ticket_productions', function (Blueprint $table) {
            $table->dropColumn('popularity_score');
        });
        Schema::connection('mysql_resource_db')->table('ticket_performers', function (Blueprint $table) {
            $table->dropColumn('popularity_score');
        });
        Schema::connection('mysql_resource_db')->table('ticket_venues', function (Blueprint $table) {
            $table->dropColumn('popularity_score');
        });
    }
};
