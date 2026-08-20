<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Creates the member_memberships table — junction/transactional table.
     * Each row = one membership period for a member.
     * Tracks plan assignment, start/end dates, payment status, and renewal.
     */
    public function up(): void
    {
        Schema::create('member_memberships', function (Blueprint $table) {
            $table->id();

            $table->foreignId('member_id')
                  ->constrained('members')
                  ->cascadeOnDelete();

            $table->foreignId('membership_plan_id')
                  ->constrained('membership_plans')
                  ->restrictOnDelete();

            $table->date('start_date');
            $table->date('end_date');

            // Fee amounts — total copied from plan price at time of assignment
            $table->decimal('total_amount', 10, 2);
            $table->decimal('paid_amount', 10, 2)->default(0.00);
            $table->decimal('remaining_amount', 10, 2)->default(0.00);

            $table->enum('status', ['active', 'expired', 'expiring_soon', 'suspended'])
                  ->default('active');

            $table->text('notes')->nullable();

            // Set when this membership was renewed from a previous one
            $table->timestamp('renewed_at')->nullable();

            $table->timestamps();

            // Indexes
            $table->index('member_id', 'mm_member_id_index');
            $table->index('membership_plan_id', 'mm_plan_id_index');
            $table->index('status', 'mm_status_index');
            $table->index('end_date', 'mm_end_date_index'); // for expiry queries
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('member_memberships');
    }
};
