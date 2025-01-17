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
        Schema::connection('mysql_pet_shop')->table('payment_charges', function (Blueprint $table) {
            $table->string('type')->nullable();
            $table->boolean('status')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('mysql_pet_shop')->table('payment_charges', function (Blueprint $table) {
            $table->dropColumn('type');
            $table->string('status')->change();
        });
    }
};
