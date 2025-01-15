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
        Schema::table('users', function (Blueprint $table) {
            $table->string('user_id');
            $table->string('first_name');
            $table->string('last_name');
            $table->string('phone_country_code');
            $table->bigInteger('phone');
            $table->string('email_code');
            $table->string('status');
            $table->string('country_code');
            $table->string('address1');
            $table->string('address2')->nullable();
            $table->string('company_name')->nullable();
            $table->string('company_phone')->nullable();
            $table->string('company_email')->nullable();
            $table->string('company_commission')->nullable();
            $table->string('note')->nullable();
            $table->string('currency_id')->nullable();
            $table->string('balance')->nullable();
            $table->string('user_type');
            $table->string('state');
            $table->string('city');
            $table->string('postal_code');
            $table->string('stripe_id')->nullable();
            $table->string('certificate_code')->nullable();
            $table->timestamp('email_send_datetime')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            //
        });
    }
};
