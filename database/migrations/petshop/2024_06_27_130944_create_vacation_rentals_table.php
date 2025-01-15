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
        Schema::connection('mysql_resource_db')->create('rentals', function (Blueprint $table) {
            $table->bigInteger('id')->primary();
            $table->integer('active')->nullable();
            $table->string('name')->nullable();
            $table->string('headline')->nullable();
            $table->string('summary')->nullable();
            $table->string('description')->nullable();
            $table->string('story')->nullable();
            $table->string('benefits')->nullable();
            $table->string('features')->nullable();
            $table->string('address1')->nullable();
            $table->string('address2')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('country')->nullable();
            $table->string('zip')->nullable();
            $table->string('latitude')->nullable();
            $table->string('longitude')->nullable();
            $table->string('show_exact_location')->nullable();
            $table->string('nearest_places')->nullable();
            $table->string('contact_email')->nullable();
            $table->string('contact_fax')->nullable();
            $table->string('language_spoken')->nullable();
            $table->string('contact_name')->nullable();
            $table->string('contact_phone')->nullable();
            $table->string('contact_phone2')->nullable();
            $table->string('contact_phone3')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('mysql_resource_db')->dropIfExists('rentals');
    }
};
