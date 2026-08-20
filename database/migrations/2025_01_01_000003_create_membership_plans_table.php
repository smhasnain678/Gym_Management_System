<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Creates the membership_plans table — reusable plan templates.
     * Examples: Monthly (30 days), Quarterly (90 days), Yearly (365 days).
     */
    public function up(): void
    {
        Schema::create('membership_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->smallInteger('duration_days')->unsigned();
            $table->decimal('price', 10, 2);
            $table->text('description')->nullable();
            $table->string('color', 20)->nullable();   // e.g. #22C55E — used for plan badges in UI
            $table->integer('sort_order')->default(0);  // controls display order in dropdowns
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('membership_plans');
    }
};
