<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Creates the members table — core entity of the system.
     * Includes personal info, emergency contacts, medical notes.
     * Uses soft deletes to preserve attendance, fee, and membership history.
     */
    public function up(): void
    {
        Schema::create('members', function (Blueprint $table) {
            $table->id();

            // Optional assigned trainer
            $table->foreignId('trainer_id')
                  ->nullable()
                  ->constrained('trainers')
                  ->nullOnDelete();

            $table->string('name', 100);
            $table->string('email', 150)->nullable();
            $table->string('phone', 20);
            $table->date('date_of_birth')->nullable();
            $table->enum('gender', ['male', 'female', 'other']);
            $table->string('profile_photo', 255)->nullable();
            $table->text('address')->nullable();

            // Emergency contact info
            $table->string('emergency_contact_name', 100)->nullable();
            $table->string('emergency_contact_phone', 20)->nullable();

            // Medical information
            $table->text('medical_notes')->nullable();
            $table->decimal('height', 5, 2)->nullable();  // in cm
            $table->decimal('weight', 5, 2)->nullable();  // in kg
            $table->string('blood_group', 10)->nullable(); // e.g. A+, B-, O+

            $table->date('joining_date');
            $table->enum('status', ['active', 'expired', 'expiring_soon', 'suspended'])
                  ->default('active');

            $table->timestamps();

            // Soft delete — preserves historical records
            $table->softDeletes();

            // Indexes for performance
            $table->index('trainer_id', 'members_trainer_id_index');
            $table->index('status', 'members_status_index');
            $table->index('phone', 'members_phone_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('members');
    }
};
