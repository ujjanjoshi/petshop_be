<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('mysql_resource_db')->create('viator_destinations_tags', function (Blueprint $table) {
            $table->integer('destination_id')->index();
            $table->integer('tag_id')->index();

            $table->primary(['destination_id', 'tag_id']);
        });
        Schema::connection('mysql_resource_db')->table('viator_schedules', function (Blueprint $table) {
            /** morning - 1, afternoon - 8, evening - 16 **/
            $table->smallInteger('timeofday')->default(0);

            /** unit: hour. potential value 1, 4, 8, 24, 48, 72, ...**/
            $table->smallInteger('duration')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('mysql_resource_db')->dropIfExists('viator_destinations_tags');

        Schema::connection('mysql_resource_db')->table('viator_schedules', function (Blueprint $table) {
            $table->dropColumn(['timeofday', 'duration']);
        });
    }
};
