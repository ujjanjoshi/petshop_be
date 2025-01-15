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
        Schema::connection('mysql_resource_db')->table('certificates', function (Blueprint $table) {
            $table->integer('invoice_id');
            $table->double('price');
            $table->integer('program_id');
            $table->boolean('vip')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('mysql_resource_db')->table('certificates', function (Blueprint $table) {
            $table->dropColumn('invoice_id');
            $table->dropColumn('price');
            $table->dropColumn('program_id');
            $table->dropColumn('vip');
        });
    }
};
