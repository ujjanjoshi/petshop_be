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
        Schema::connection('mysql_pet_shop')->table('brandings', function (Blueprint $table) {
            $table->string('linkedin_url');
            $table->string('twitter_url');
            $table->string('facebook_url');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('mysql_pet_shop')->table('brandings', function (Blueprint $table) {
            //
        });
    }
};
