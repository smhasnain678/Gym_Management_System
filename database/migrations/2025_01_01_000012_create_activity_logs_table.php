<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Creates the activity_logs table — complete audit trail.
     * Records every significant CRUD action performed by the Gym Owner.
     * Uses polymorphic subject columns to reference any entity type.
     */
    public function up(): void
    {
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                  ->constrained('users')
                  ->cascadeOnDelete();

            // Action identifier — e.g. 'member_added', 'fee_received', 'trainer_updated'
            $table->string('action', 100);

            // Human-readable description of the action
            $table->text('description');

            // Polymorphic subject — which entity was affected?
            $table->string('subject_type', 100)->nullable();
            $table->unsignedBigInteger('subject_id')->nullable();

            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();     // browser/OS string for audit context
            $table->timestamps();

            // Indexes for filtering and searching logs
            $table->index('user_id', 'al_user_id_index');
            $table->index('action', 'al_action_index');
            $table->index(['subject_type', 'subject_id'], 'al_subject_index');
            $table->index('created_at', 'al_created_at_index'); // for date-range filtering
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
