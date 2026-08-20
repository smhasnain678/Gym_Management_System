<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Creates the attendances table — daily check-in records per member.
     * Critical constraint: UNIQUE index on (member_id, date) prevents duplicate check-ins.
     */
    public function up(): void
    {
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();

            $table->foreignId('member_id')
                  ->constrained('members')
                  ->cascadeOnDelete();

            $table->date('date');
            $table->time('check_in_time')->nullable();
            $table->time('check_out_time')->nullable(); // optional session end time
            $table->enum('status', ['present', 'absent'])->default('present');
            $table->timestamps();

            // CRITICAL: Prevents duplicate attendance for same member on same day
            $table->unique(['member_id', 'date'], 'attendances_member_date_unique');

            // Index for fast daily attendance queries
            $table->index('date', 'attendances_date_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
