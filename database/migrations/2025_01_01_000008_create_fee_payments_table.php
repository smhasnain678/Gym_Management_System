<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Creates the fee_payments table — payment transaction records.
     * Each row = one payment made by a member toward a specific membership period.
     * Supports partial payments. Updates paid/remaining amounts on member_memberships.
     */
    public function up(): void
    {
        Schema::create('fee_payments', function (Blueprint $table) {
            $table->id();

            // Direct member reference for quick member-level fee queries
            $table->foreignId('member_id')
                  ->constrained('members')
                  ->cascadeOnDelete();

            // The specific membership period this payment is for
            $table->foreignId('member_membership_id')
                  ->constrained('member_memberships')
                  ->cascadeOnDelete();

            $table->decimal('amount_paid', 10, 2);
            $table->date('payment_date');
            $table->date('due_date')->nullable();

            // Strict enum — manual record keeping only (no gateway integration in V1)
            $table->enum('payment_method', ['cash', 'bank_transfer', 'easypaisa', 'jazzcash', 'card'])
                  ->default('cash');

            // Auto-generated unique receipt number
            $table->string('receipt_number', 50)->nullable()->unique();

            $table->text('notes')->nullable();
            $table->timestamps();

            // Indexes for reporting performance
            $table->index('member_id', 'fp_member_id_index');
            $table->index('member_membership_id', 'fp_membership_id_index');
            $table->index('payment_date', 'fp_payment_date_index'); // for monthly revenue reports
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fee_payments');
    }
};
