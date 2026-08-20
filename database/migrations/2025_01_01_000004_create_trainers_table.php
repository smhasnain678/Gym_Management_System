<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Creates the trainers table — gym trainers and staff records.
     * Managed by the Gym Owner. No login access in V1.
     */
    public function up(): void
    {
        Schema::create('trainers', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('email', 150)->nullable();
            $table->string('phone', 20);
            $table->enum('gender', ['male', 'female', 'other']);
            $table->date('date_of_birth')->nullable();
            $table->string('specialization', 100)->nullable();
            $table->text('bio')->nullable();
            $table->text('address')->nullable();
            $table->string('profile_photo', 255)->nullable();
            $table->decimal('salary', 10, 2)->nullable();
            $table->date('joining_date');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trainers');
    }
};
