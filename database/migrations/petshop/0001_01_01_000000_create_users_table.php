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
        Schema::connection('mysql_pet_shop')->create('users', function (Blueprint $table) {
            $table->id();
            $table->string('user_id');
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('phone_country_code');
            $table->bigInteger('phone');
            $table->string('email_code');
            $table->string('password');
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
            $table->timestamps();
        });

        Schema::connection('mysql_pet_shop')->create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::connection('mysql_pet_shop')->create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('mysql_pet_shop')->dropIfExists('users');
        Schema::connection('mysql_pet_shop')->dropIfExists('password_reset_tokens');
        Schema::connection('mysql_pet_shop')->dropIfExists('sessions');
    }
};
