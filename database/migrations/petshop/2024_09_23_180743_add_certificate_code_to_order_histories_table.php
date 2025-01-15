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
        Schema::connection('mysql_pet_shop')->table('order_histories', function (Blueprint $table) {
            $table->renameColumn('cretificate_code', 'certificate_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('mysql_pet_shop')->table('order_histories', function (Blueprint $table) {
        });
    }
};
