<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ExpenseTest extends TestCase
{
    use RefreshDatabase;

    protected function owner(): User
    {
        return User::factory()->create(['is_active' => true]);
    }

    protected function setUp(): void
    {
        parent::setUp();
        // Seed some expense categories for tests
        ExpenseCategory::insert([
            ['name' => 'Rent'],
            ['name' => 'Utilities'],
            ['name' => 'Equipment'],
            ['name' => 'Maintenance'],
            ['name' => 'Salaries'],
            ['name' => 'Miscellaneous']
        ]);
    }

    public function test_guest_cannot_access_expenses(): void
    {
        $this->get('/expenses')->assertRedirect('/login');
        $this->get('/expenses/create')->assertRedirect('/login');
        $this->post('/expenses')->assertRedirect('/login');
    }

    public function test_authenticated_owner_can_access_expenses(): void
    {
        $owner = $this->owner();
        $this->actingAs($owner)->get('/expenses')->assertStatus(200);
    }

    public function test_expense_index_renders_correctly(): void
    {
        $owner = $this->owner();
        $response = $this->actingAs($owner)->get('/expenses');
        $response->assertStatus(200);
        $response->assertSee('Expenses');
        $response->assertSee('Total Expenses');
        $response->assertSee('Category Breakdown');
    }

    public function test_add_expense_works(): void
    {
        $owner = $this->owner();
        $category = ExpenseCategory::first();

        $response = $this->actingAs($owner)->post('/expenses', [
            'title'               => 'November Rent',
            'expense_category_id' => $category->id,
            'amount'              => 5000.50,
            'expense_date'        => now()->format('Y-m-d'),
            'paid_to'             => 'Landlord LLC',
            'notes'               => 'Paid via bank transfer',
        ]);

        $response->assertRedirect('/expenses');
        $this->assertDatabaseHas('expenses', [
            'title'  => 'November Rent',
            'amount' => 5000.50,
        ]);
    }

    public function test_expense_validation_works(): void
    {
        $owner = $this->owner();
        $response = $this->actingAs($owner)->post('/expenses', []);
        $response->assertSessionHasErrors(['title', 'expense_category_id', 'amount', 'expense_date']);
    }

    public function test_zero_or_negative_amount_is_rejected(): void
    {
        $owner = $this->owner();
        $category = ExpenseCategory::first();

        $response = $this->actingAs($owner)->post('/expenses', [
            'title'               => 'Invalid Amount',
            'expense_category_id' => $category->id,
            'amount'              => 0,
            'expense_date'        => now()->format('Y-m-d'),
        ]);
        $response->assertSessionHasErrors('amount');

        $response = $this->actingAs($owner)->post('/expenses', [
            'title'               => 'Negative Amount',
            'expense_category_id' => $category->id,
            'amount'              => -50,
            'expense_date'        => now()->format('Y-m-d'),
        ]);
        $response->assertSessionHasErrors('amount');
    }

    public function test_invalid_category_is_rejected(): void
    {
        $owner = $this->owner();
        $response = $this->actingAs($owner)->post('/expenses', [
            'title'               => 'Invalid Category',
            'expense_category_id' => 9999, // Does not exist
            'amount'              => 100,
            'expense_date'        => now()->format('Y-m-d'),
        ]);
        $response->assertSessionHasErrors('expense_category_id');
    }

    public function test_edit_expense_works(): void
    {
        $owner = $this->owner();
        $category = ExpenseCategory::first();
        
        $expense = Expense::create([
            'title'               => 'Old Title',
            'expense_category_id' => $category->id,
            'amount'              => 100.00,
            'expense_date'        => now()->format('Y-m-d'),
        ]);

        $response = $this->actingAs($owner)->put("/expenses/{$expense->id}", [
            'title'               => 'New Title',
            'expense_category_id' => $category->id,
            'amount'              => 150.00,
            'expense_date'        => now()->format('Y-m-d'),
        ]);

        $response->assertRedirect('/expenses');
        $this->assertDatabaseHas('expenses', [
            'id'     => $expense->id,
            'title'  => 'New Title',
            'amount' => 150.00,
        ]);
    }

    public function test_delete_expense_works(): void
    {
        $owner = $this->owner();
        $category = ExpenseCategory::first();
        
        $expense = Expense::create([
            'title'               => 'To be deleted',
            'expense_category_id' => $category->id,
            'amount'              => 100.00,
            'expense_date'        => now()->format('Y-m-d'),
        ]);

        $response = $this->actingAs($owner)->delete("/expenses/{$expense->id}");
        $response->assertRedirect('/expenses');
        $this->assertDatabaseMissing('expenses', ['id' => $expense->id]);
    }

    public function test_monthly_expense_calculation_is_correct(): void
    {
        $owner = $this->owner();
        $category = ExpenseCategory::first();
        
        // This month
        Expense::create(['title' => 'E1', 'expense_category_id' => $category->id, 'amount' => 100, 'expense_date' => now()->format('Y-m-d')]);
        Expense::create(['title' => 'E2', 'expense_category_id' => $category->id, 'amount' => 250, 'expense_date' => now()->format('Y-m-d')]);
        
        // Last month
        Expense::create(['title' => 'E3', 'expense_category_id' => $category->id, 'amount' => 500, 'expense_date' => now()->subMonth()->format('Y-m-d')]);

        $response = $this->actingAs($owner)->get('/expenses');
        $response->assertStatus(200);
        // Ensure the sum of this month (350) is visible, not last month's (500)
        $response->assertSee('350.00');
    }

    public function test_expense_history_displays_records(): void
    {
        $owner = $this->owner();
        $category = ExpenseCategory::first();
        
        Expense::create([
            'title'               => 'Unique Expense Title ABC',
            'expense_category_id' => $category->id,
            'amount'              => 123.45,
            'expense_date'        => now()->format('Y-m-d'),
        ]);

        $response = $this->actingAs($owner)->get('/expenses');
        $response->assertSee('Unique Expense Title ABC');
        $response->assertSee('123.45');
    }

    public function test_activity_log_created_on_add(): void
    {
        $owner = $this->owner();
        $category = ExpenseCategory::first();

        $this->actingAs($owner)->post('/expenses', [
            'title'               => 'Test Log',
            'expense_category_id' => $category->id,
            'amount'              => 50,
            'expense_date'        => now()->format('Y-m-d'),
        ]);

        $this->assertDatabaseHas('activity_logs', [
            'action' => 'Expense Added',
            'subject_type' => Expense::class,
        ]);
    }

    public function test_activity_log_created_on_update(): void
    {
        $owner = $this->owner();
        $category = ExpenseCategory::first();
        
        $expense = Expense::create([
            'title'               => 'Old',
            'expense_category_id' => $category->id,
            'amount'              => 10,
            'expense_date'        => now()->format('Y-m-d'),
        ]);

        $this->actingAs($owner)->put("/expenses/{$expense->id}", [
            'title'               => 'New',
            'expense_category_id' => $category->id,
            'amount'              => 20,
            'expense_date'        => now()->format('Y-m-d'),
        ]);

        $this->assertDatabaseHas('activity_logs', [
            'action' => 'Expense Updated',
            'subject_type' => Expense::class,
        ]);
    }

    public function test_activity_log_created_on_delete(): void
    {
        $owner = $this->owner();
        $category = ExpenseCategory::first();
        
        $expense = Expense::create([
            'title'               => 'Delete Me',
            'expense_category_id' => $category->id,
            'amount'              => 10,
            'expense_date'        => now()->format('Y-m-d'),
        ]);

        $this->actingAs($owner)->delete("/expenses/{$expense->id}");

        $this->assertDatabaseHas('activity_logs', [
            'action' => 'Expense Deleted',
            'subject_type' => Expense::class,
        ]);
    }
}
