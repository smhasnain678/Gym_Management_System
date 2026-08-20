<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Creates the expenses table — gym operational cost records.
     * Linked to a category for grouped reporting.
     * Enables: Net Profit = Monthly Revenue - Monthly Expenses.
     */
    public function up(): void
    {
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();

            $table->foreignId('expense_category_id')
                  ->constrained('expense_categories')
                  ->restrictOnDelete();

            $table->string('title', 150);
            $table->decimal('amount', 10, 2);
            $table->date('expense_date');
            $table->string('paid_to', 150)->nullable();     // vendor or person paid
            $table->string('receipt_image', 255)->nullable(); // path to uploaded receipt image
            $table->text('notes')->nullable();
            $table->timestamps();

            // Indexes for reporting performance
            $table->index('expense_category_id', 'expenses_category_id_index');
            $table->index('expense_date', 'expenses_date_index'); // for monthly reports
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
