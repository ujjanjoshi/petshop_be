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
        Schema::create('payment_charges', function (Blueprint $table) {
            $table->id();
            $table->string('payment_method'); // e.g., 'credit_card'
            $table->decimal('charges', 5, 2); // e.g., 5.00 for 5%
            $table->enum('status', ['on', 'off'])->default('on'); // 'on' or 'off' status
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_charges');
    }
};
