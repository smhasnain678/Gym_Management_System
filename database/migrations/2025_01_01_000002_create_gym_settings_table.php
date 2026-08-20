<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Creates the gym_settings table — single-row config for the gym.
     * Stores branding, locale, and display preferences.
     * V2 multi-tenant: this table becomes `gyms` with gym_id FK on all entities.
     */
    public function up(): void
    {
        Schema::create('gym_settings', function (Blueprint $table) {
            $table->id();
            $table->string('gym_name', 150);
            $table->string('gym_logo', 255)->nullable();
            $table->string('owner_name', 100);
            $table->string('contact_email', 150)->nullable();
            $table->string('contact_phone', 20)->nullable();
            $table->text('address')->nullable();
            $table->string('country', 100)->default('Pakistan');
            $table->string('city', 100)->nullable();
            $table->string('currency', 10)->default('PKR');
            $table->string('currency_symbol', 10)->default('Rs');
            $table->string('timezone', 60)->default('Asia/Karachi');
            $table->string('language', 10)->default('en');
            $table->string('theme', 20)->default('light');
            $table->string('date_format', 20)->default('d/m/Y');
            $table->string('time_format', 10)->default('12h');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gym_settings');
    }
};
