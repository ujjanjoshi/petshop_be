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
        // Merchandise
        Schema::connection('mysql_resource_db')->table('merchandises', function (Blueprint $table) {
            $table->unique('product_id');
        });
        Schema::connection('mysql_resource_db')->table('merchandise_options', function (Blueprint $table) {
            $table->index('merchandise_id');
        });
        Schema::connection('mysql_resource_db')->table('merchandise_resources', function (Blueprint $table) {
            $table->index('merchandise_id');
        });
        Schema::connection('mysql_resource_db')->table('merchandise_features', function (Blueprint $table) {
            $table->index('merchandise_id');
        });

        // Ticket
        Schema::connection('mysql_resource_db')->table('ticket_categories', function (Blueprint $table) {
            $table->index('category_id');
            $table->index('parent_id');
        });
        Schema::connection('mysql_resource_db')->table('ticket_categories_childrens', function (Blueprint $table) {
            $table->index('children_id');
            $table->index('parent_id');
        });
        Schema::connection('mysql_resource_db')->table('ticket_performers', function (Blueprint $table) {
            $table->unique('performer_id');
        });
        Schema::connection('mysql_resource_db')->table('ticket_productions', function (Blueprint $table) {
            $table->unique('production_id');
        });
        Schema::connection('mysql_resource_db')->table('ticket_productions_performers', function (Blueprint $table) {
            $table->renameColumn('event_id', 'production_id');
            $table->index(['production_id', 'performer_id']);
        });
        Schema::connection('mysql_resource_db')->table('ticket_venues', function (Blueprint $table) {
            $table->unique('venue_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('mysql_resource_db')->table('merchandises', function (Blueprint $table) {
            $table->dropUnique('merchandises_product_id_unique');
        });
        Schema::connection('mysql_resource_db')->table('merchandise_options', function (Blueprint $table) {
            $table->dropIndex('merchandise_options_merchandise_id_index');
        });
        Schema::connection('mysql_resource_db')->table('merchandise_features', function (Blueprint $table) {
            $table->dropIndex('merchandise_features_merchandise_id_index');
        });
        Schema::connection('mysql_resource_db')->table('merchandise_resources', function (Blueprint $table) {
            $table->dropIndex('merchandise_resources_merchandise_id_index');
        });

        Schema::connection('mysql_resource_db')->table('ticket_performers', function (Blueprint $table) {
            $table->dropUnique('ticket_performers_performer_id_unique');
        });
        Schema::connection('mysql_resource_db')->table('ticket_productions', function (Blueprint $table) {
            $table->dropUnique('ticket_productions_production_id_unique');
        });
        Schema::connection('mysql_resource_db')->table('ticket_productions_performers', function (Blueprint $table) {
            $table->renameColumn('production_id', 'event_id');
            $table->dropIndex('ticket_productions_performerss_production_id_performer_id_index');
        });
        Schema::connection('mysql_resource_db')->table('ticket_venues', function (Blueprint $table) {
            $table->dropUnique('ticket_venues_venue_id_unique');
        });
    }
};
