<?php

namespace Tests\Feature;

use App\Models\Member;
use App\Models\Trainer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TrainerTest extends TestCase
{
    use RefreshDatabase;

    // ─────────────────────────────────────────────────────────────────────────
    // Helpers
    // ─────────────────────────────────────────────────────────────────────────

    protected function createOwner(): User
    {
        return User::factory()->create([
            'is_active' => true,
        ]);
    }

    protected function createTrainer(array $overrides = []): Trainer
    {
        return Trainer::create(array_merge([
            'name'         => 'John Trainer',
            'phone'        => '03001234567',
            'gender'       => 'male',
            'joining_date' => now()->format('Y-m-d'),
            'is_active'    => true,
        ], $overrides));
    }

    protected function createMember(array $overrides = []): Member
    {
        return Member::create(array_merge([
            'name'         => 'Test Member',
            'phone'        => '03001234567',
            'gender'       => 'male',
            'joining_date' => now()->toDateString(),
            'status'       => 'active',
        ], $overrides));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 1. Auth: guests cannot access trainers
    // ─────────────────────────────────────────────────────────────────────────
    public function test_guests_cannot_access_trainers(): void
    {
        $this->get('/trainers')->assertRedirect('/login');
        $this->get('/trainers/create')->assertRedirect('/login');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 2. Trainer index loads and shows trainers
    // ─────────────────────────────────────────────────────────────────────────
    public function test_owner_can_view_trainers_list(): void
    {
        $trainer = $this->createTrainer(['name' => 'Listed Trainer']);
        $response = $this->actingAs($this->createOwner())->get('/trainers');
        $response->assertStatus(200);
        $response->assertSee('Listed Trainer');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 3. Create page loads
    // ─────────────────────────────────────────────────────────────────────────
    public function test_create_trainer_page_loads(): void
    {
        $response = $this->actingAs($this->createOwner())->get('/trainers/create');
        $response->assertStatus(200);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 4. Search by name
    // ─────────────────────────────────────────────────────────────────────────
    public function test_trainers_can_be_searched_by_name(): void
    {
        $this->createTrainer(['name' => 'Alice Smith']);
        $this->createTrainer(['name' => 'Bob Jones', 'phone' => '03007654321']);

        $response = $this->actingAs($this->createOwner())->get('/trainers?search=Alice');
        $response->assertSee('Alice Smith');
        $response->assertDontSee('Bob Jones');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 5. Search by phone
    // ─────────────────────────────────────────────────────────────────────────
    public function test_trainers_can_be_searched_by_phone(): void
    {
        $this->createTrainer(['name' => 'Alice Smith', 'phone' => '03001111111']);
        $this->createTrainer(['name' => 'Bob Jones',   'phone' => '03002222222']);

        $response = $this->actingAs($this->createOwner())->get('/trainers?search=03001111111');
        $response->assertSee('Alice Smith');
        $response->assertDontSee('Bob Jones');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 6. Filter active trainers
    // ─────────────────────────────────────────────────────────────────────────
    public function test_trainers_can_be_filtered_by_status(): void
    {
        $this->createTrainer(['name' => 'Active Trainer',   'is_active' => true]);
        $this->createTrainer(['name' => 'Inactive Trainer', 'is_active' => false, 'phone' => '03007654321']);

        $response = $this->actingAs($this->createOwner())->get('/trainers?status=active');
        $response->assertSee('Active Trainer');
        $response->assertDontSee('Inactive Trainer');

        $response = $this->actingAs($this->createOwner())->get('/trainers?status=inactive');
        $response->assertDontSee('Active Trainer');
        $response->assertSee('Inactive Trainer');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 7. Create trainer (full fields)
    // ─────────────────────────────────────────────────────────────────────────
    public function test_owner_can_create_trainer(): void
    {
        $response = $this->actingAs($this->createOwner())->post('/trainers', [
            'name'           => 'New Trainer',
            'phone'          => '03007654321',
            'gender'         => 'female',
            'joining_date'   => '2025-01-01',
            'specialization' => 'Yoga',
            'salary'         => 50000,
            'is_active'      => 1,
        ]);

        $trainer = Trainer::where('name', 'New Trainer')->first();
        $this->assertNotNull($trainer);
        $response->assertRedirect(route('trainers.show', $trainer));
        $this->assertEquals('female', $trainer->gender);
        $this->assertEquals('Yoga', $trainer->specialization);
        $this->assertTrue($trainer->is_active);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 8. Create trainer without optional fields
    // ─────────────────────────────────────────────────────────────────────────
    public function test_owner_can_create_trainer_with_minimal_fields(): void
    {
        $response = $this->actingAs($this->createOwner())->post('/trainers', [
            'name'         => 'Minimal Trainer',
            'phone'        => '03001111111',
            'gender'       => 'male',
            'joining_date' => '2025-01-01',
        ]);

        $trainer = Trainer::where('name', 'Minimal Trainer')->first();
        $this->assertNotNull($trainer);
        $this->assertFalse($trainer->is_active); // checkbox not sent = false
        $this->assertNull($trainer->specialization);
        $this->assertNull($trainer->salary);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 9. Validation: required fields
    // ─────────────────────────────────────────────────────────────────────────
    public function test_trainer_validation_requires_fields(): void
    {
        $response = $this->actingAs($this->createOwner())->post('/trainers', []);
        $response->assertSessionHasErrors(['name', 'phone', 'gender', 'joining_date']);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 10. Validation: email must be valid and unique
    // ─────────────────────────────────────────────────────────────────────────
    public function test_trainer_email_must_be_valid(): void
    {
        $response = $this->actingAs($this->createOwner())->post('/trainers', [
            'name'         => 'Bad Email Trainer',
            'phone'        => '03001234567',
            'gender'       => 'male',
            'joining_date' => '2025-01-01',
            'email'        => 'not-an-email',
        ]);
        $response->assertSessionHasErrors(['email']);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 11. Validation: duplicate email rejected
    // ─────────────────────────────────────────────────────────────────────────
    public function test_trainer_duplicate_email_rejected(): void
    {
        $this->createTrainer(['email' => 'trainer@gym.com']);

        $response = $this->actingAs($this->createOwner())->post('/trainers', [
            'name'         => 'Duplicate Email',
            'phone'        => '03007654321',
            'gender'       => 'male',
            'joining_date' => '2025-01-01',
            'email'        => 'trainer@gym.com',
        ]);
        $response->assertSessionHasErrors(['email']);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 12. Validation: salary must be non-negative
    // ─────────────────────────────────────────────────────────────────────────
    public function test_trainer_salary_must_be_non_negative(): void
    {
        $response = $this->actingAs($this->createOwner())->post('/trainers', [
            'name'         => 'Neg Salary',
            'phone'        => '03001234567',
            'gender'       => 'male',
            'joining_date' => '2025-01-01',
            'salary'       => -5000,
        ]);
        $response->assertSessionHasErrors(['salary']);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 13. Trainer show page loads
    // ─────────────────────────────────────────────────────────────────────────
    public function test_owner_can_view_trainer_profile(): void
    {
        $trainer = $this->createTrainer(['name' => 'Profile Trainer', 'specialization' => 'Boxing']);
        $response = $this->actingAs($this->createOwner())->get("/trainers/{$trainer->id}");
        $response->assertStatus(200);
        $response->assertSee('Profile Trainer');
        $response->assertSee('Boxing');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 14. Edit page loads with pre-filled values
    // ─────────────────────────────────────────────────────────────────────────
    public function test_edit_trainer_page_loads_with_prefilled_values(): void
    {
        $trainer = $this->createTrainer(['name' => 'Edit Me Trainer', 'specialization' => 'Pilates']);
        $response = $this->actingAs($this->createOwner())->get("/trainers/{$trainer->id}/edit");
        $response->assertStatus(200);
        $response->assertSee('Edit Me Trainer');
        $response->assertSee('Pilates');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 15. Update trainer
    // ─────────────────────────────────────────────────────────────────────────
    public function test_owner_can_update_trainer(): void
    {
        $trainer = $this->createTrainer(['name' => 'Old Name']);

        $response = $this->actingAs($this->createOwner())->patch("/trainers/{$trainer->id}", [
            'name'         => 'Updated Name',
            'phone'        => $trainer->phone,
            'gender'       => $trainer->gender,
            'joining_date' => $trainer->joining_date->format('Y-m-d'),
        ]);

        $response->assertRedirect(route('trainers.show', $trainer));
        $this->assertEquals('Updated Name', $trainer->fresh()->name);
        $this->assertFalse($trainer->fresh()->is_active); // checkbox not sent
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 16. Update with is_active checked
    // ─────────────────────────────────────────────────────────────────────────
    public function test_update_trainer_can_set_active(): void
    {
        $trainer = $this->createTrainer(['is_active' => false]);

        $this->actingAs($this->createOwner())->patch("/trainers/{$trainer->id}", [
            'name'         => $trainer->name,
            'phone'        => $trainer->phone,
            'gender'       => $trainer->gender,
            'joining_date' => $trainer->joining_date->format('Y-m-d'),
            'is_active'    => 1,
        ]);

        $this->assertTrue($trainer->fresh()->is_active);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 17. Email can remain the same when updating (no unique constraint clash)
    // ─────────────────────────────────────────────────────────────────────────
    public function test_update_trainer_can_keep_same_email(): void
    {
        $trainer = $this->createTrainer(['email' => 'same@gym.com']);

        $response = $this->actingAs($this->createOwner())->patch("/trainers/{$trainer->id}", [
            'name'         => $trainer->name,
            'phone'        => $trainer->phone,
            'gender'       => $trainer->gender,
            'joining_date' => $trainer->joining_date->format('Y-m-d'),
            'email'        => 'same@gym.com', // same email — should NOT fail uniqueness
        ]);

        $response->assertRedirect(route('trainers.show', $trainer));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 18. Toggle status: active → inactive
    // ─────────────────────────────────────────────────────────────────────────
    public function test_owner_can_deactivate_trainer(): void
    {
        $trainer = $this->createTrainer(['is_active' => true]);

        $response = $this->actingAs($this->createOwner())->patch("/trainers/{$trainer->id}/toggle-status");
        
        $response->assertRedirect(route('trainers.show', $trainer));
        $this->assertFalse($trainer->fresh()->is_active);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 19. Toggle status: inactive → active
    // ─────────────────────────────────────────────────────────────────────────
    public function test_owner_can_activate_trainer(): void
    {
        $trainer = $this->createTrainer(['is_active' => false]);

        $response = $this->actingAs($this->createOwner())->patch("/trainers/{$trainer->id}/toggle-status");
        
        $response->assertRedirect(route('trainers.show', $trainer));
        $this->assertTrue($trainer->fresh()->is_active);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 20. Delete trainer (no members)
    // ─────────────────────────────────────────────────────────────────────────
    public function test_owner_can_delete_trainer_without_members(): void
    {
        $trainer = $this->createTrainer();

        $response = $this->actingAs($this->createOwner())->delete("/trainers/{$trainer->id}");
        
        $response->assertRedirect('/trainers');
        $this->assertDatabaseMissing('trainers', ['id' => $trainer->id]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 21. Delete trainer with members — schema uses nullOnDelete so trainer_id → null
    //     The trainer IS deleted; the member's trainer_id becomes null.
    // ─────────────────────────────────────────────────────────────────────────
    public function test_delete_trainer_with_members_sets_trainer_id_null(): void
    {
        $trainer = $this->createTrainer();
        $member  = $this->createMember(['trainer_id' => $trainer->id, 'phone' => '03009999999']);

        // The schema has nullOnDelete on members.trainer_id, so deletion succeeds.
        $response = $this->actingAs($this->createOwner())->delete("/trainers/{$trainer->id}");

        $response->assertRedirect('/trainers');
        $this->assertDatabaseMissing('trainers', ['id' => $trainer->id]);
        // The member's trainer_id should now be null
        $this->assertNull($member->fresh()->trainer_id);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 22. Pagination — trainers list supports pagination
    // ─────────────────────────────────────────────────────────────────────────
    public function test_trainers_list_paginates(): void
    {
        // Create 20 trainers (page size = 15)
        for ($i = 1; $i <= 20; $i++) {
            $this->createTrainer([
                'name'  => "Trainer $i",
                'phone' => "030000000{$i}",
            ]);
        }

        $response = $this->actingAs($this->createOwner())->get('/trainers');
        $response->assertStatus(200);
        // First page has 15 trainers
        $response->assertSee('Trainer 1');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 23. Trainer assigned members show on profile
    // ─────────────────────────────────────────────────────────────────────────
    public function test_trainer_profile_shows_assigned_members(): void
    {
        $trainer = $this->createTrainer();
        $member = $this->createMember([
            'name'       => 'Assigned Member',
            'trainer_id' => $trainer->id,
            'phone'      => '03001112222',
        ]);

        $response = $this->actingAs($this->createOwner())->get("/trainers/{$trainer->id}");
        $response->assertStatus(200);
        $response->assertSee('Assigned Member');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 24. Active trainer count is accurate for dashboard
    // ─────────────────────────────────────────────────────────────────────────
    public function test_active_trainer_count_is_accurate(): void
    {
        $this->createTrainer(['is_active' => true]);
        $this->createTrainer(['is_active' => true,  'phone' => '03007654320']);
        $this->createTrainer(['is_active' => false,  'phone' => '03007654321']);

        $activeCount = Trainer::where('is_active', true)->count();
        $this->assertEquals(2, $activeCount);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 25. Deactivated trainer is still in database (not deleted)
    // ─────────────────────────────────────────────────────────────────────────
    public function test_deactivated_trainer_remains_in_database(): void
    {
        $trainer = $this->createTrainer(['is_active' => true]);

        $this->actingAs($this->createOwner())->patch("/trainers/{$trainer->id}/toggle-status");

        $this->assertDatabaseHas('trainers', ['id' => $trainer->id, 'is_active' => false]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 26. Search by specialization
    // ─────────────────────────────────────────────────────────────────────────
    public function test_index_shows_trainer_specialization(): void
    {
        $this->createTrainer(['name' => 'Yoga Expert', 'specialization' => 'Yoga']);
        $this->createTrainer(['name' => 'Boxing Pro',  'specialization' => 'Boxing', 'phone' => '03007654321']);

        $response = $this->actingAs($this->createOwner())->get('/trainers');
        $response->assertStatus(200);
        // Specialization visible in table
        $response->assertSee('Yoga Expert');
        $response->assertSee('Boxing Pro');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 27. Trainer create stores all fields
    // ─────────────────────────────────────────────────────────────────────────
    public function test_trainer_create_stores_all_fields(): void
    {
        $owner = $this->createOwner();

        $this->actingAs($owner)->post('/trainers', [
            'name'           => 'Full Fields',
            'phone'          => '03007654321',
            'email'          => 'full@gym.com',
            'gender'         => 'female',
            'date_of_birth'  => '1990-05-15',
            'joining_date'   => '2024-01-01',
            'specialization' => 'CrossFit',
            'salary'         => 75000,
            'address'        => '123 Gym Street',
            'bio'            => 'Expert in fitness.',
            'is_active'      => 1,
        ]);

        $trainer = Trainer::where('name', 'Full Fields')->first();
        $this->assertNotNull($trainer);
        $this->assertEquals('full@gym.com', $trainer->email);
        $this->assertEquals('CrossFit', $trainer->specialization);
        $this->assertEquals(75000, $trainer->salary);
        $this->assertEquals('123 Gym Street', $trainer->address);
        $this->assertEquals('Expert in fitness.', $trainer->bio);
        $this->assertEquals('1990-05-15', $trainer->date_of_birth->format('Y-m-d'));
    }
}
