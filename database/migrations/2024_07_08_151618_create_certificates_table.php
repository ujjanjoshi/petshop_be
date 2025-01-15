<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCertificatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('mysql_resource_db')->create('certificates', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->string('code', 32)->unique();
            $table->string('sku', 24)->nullable();

            $table->integer('redeemer_id');

            // new/open/close/cancel/autoredeem
            $table->integer('status_id');

            $table->integer('order_id');

            $table->timestamp('start_date')->nullable();
            $table->timestamp('end_date')->nullable();
            $table->timestamp('expire')->nullable();

            $table->timestamp('closed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();

            $table->timestamps();

            $table->index('code');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('certificates');
    }
}
