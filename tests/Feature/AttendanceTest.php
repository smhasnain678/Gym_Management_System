<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\Attendance;
use App\Models\Member;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceTest extends TestCase
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

    protected function createMember(array $overrides = []): Member
    {
        return Member::create(array_merge([
            'name'         => 'Attendance Member',
            'phone'        => '03001234567',
            'gender'       => 'male',
            'joining_date' => now()->toDateString(),
            'status'       => 'active',
        ], $overrides));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 1. Auth: guests cannot access attendance
    // ─────────────────────────────────────────────────────────────────────────
    public function test_guests_cannot_access_attendance_routes(): void
    {
        $this->get('/attendances')->assertRedirect('/login');
        $this->post('/attendances')->assertRedirect('/login');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 2. Attendance views load
    // ─────────────────────────────────────────────────────────────────────────
    public function test_owner_can_view_daily_and_monthly_attendance(): void
    {
        $owner = $this->createOwner();

        // Daily view
        $response = $this->actingAs($owner)->get('/attendances?view=daily');
        $response->assertStatus(200);
        $response->assertSee('Daily View');

        // Monthly view
        $response2 = $this->actingAs($owner)->get('/attendances?view=monthly');
        $response2->assertStatus(200);
        $response2->assertSee('Monthly View');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 3. Mark attendance present
    // ─────────────────────────────────────────────────────────────────────────
    public function test_owner_can_mark_member_present(): void
    {
        $owner = $this->createOwner();
        $member = $this->createMember();
        $date = now()->format('Y-m-d');

        $response = $this->actingAs($owner)->post('/attendances', [
            'member_id' => $member->id,
            'date'      => $date,
            'status'    => 'present',
            'check_in_time' => '09:00',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('attendances', [
            'member_id' => $member->id,
            'date'      => $date . ' 00:00:00',
            'status'    => 'present',
            'check_in_time' => '09:00',
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 4. Mark attendance absent
    // ─────────────────────────────────────────────────────────────────────────
    public function test_owner_can_mark_member_absent(): void
    {
        $owner = $this->createOwner();
        $member = $this->createMember();
        $date = now()->format('Y-m-d');

        $response = $this->actingAs($owner)->post('/attendances', [
            'member_id' => $member->id,
            'date'      => $date,
            'status'    => 'absent',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('attendances', [
            'member_id' => $member->id,
            'date'      => $date . ' 00:00:00',
            'status'    => 'absent',
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 5. Duplicate protection (updateOrCreate)
    // ─────────────────────────────────────────────────────────────────────────
    public function test_duplicate_attendance_submission_updates_safely(): void
    {
        $owner = $this->createOwner();
        $member = $this->createMember();
        $date = now()->format('Y-m-d');

        // Mark present initially
        $this->actingAs($owner)->post('/attendances', [
            'member_id' => $member->id,
            'date'      => $date,
            'status'    => 'present',
            'check_in_time' => '09:00',
        ]);

        $this->assertEquals(1, Attendance::count());

        // Submit again for same member and date, but now as absent
        $response = $this->actingAs($owner)->post('/attendances', [
            'member_id' => $member->id,
            'date'      => $date,
            'status'    => 'absent',
        ]);

        $response->assertRedirect();
        
        // Count should still be 1, but status updated to absent
        $this->assertEquals(1, Attendance::count());
        $this->assertDatabaseHas('attendances', [
            'member_id' => $member->id,
            'date'      => $date . ' 00:00:00',
            'status'    => 'absent',
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 6. Cannot mark suspended member
    // ─────────────────────────────────────────────────────────────────────────
    public function test_cannot_mark_attendance_for_suspended_member(): void
    {
        $owner = $this->createOwner();
        $member = $this->createMember(['status' => 'suspended']);

        $response = $this->actingAs($owner)->post('/attendances', [
            'member_id' => $member->id,
            'date'      => now()->format('Y-m-d'),
            'status'    => 'present',
        ]);

        $response->assertSessionHas('error');
        $this->assertDatabaseEmpty('attendances');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 7. Validation rules
    // ─────────────────────────────────────────────────────────────────────────
    public function test_attendance_validation_rules(): void
    {
        $owner = $this->createOwner();

        $response = $this->actingAs($owner)->post('/attendances', [
            'member_id' => 9999, // invalid member
            'date'      => 'not-a-date',
            'status'    => 'invalid-status',
        ]);

        $response->assertSessionHasErrors(['member_id', 'date', 'status']);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 8. Member check-in history loads
    // ─────────────────────────────────────────────────────────────────────────
    public function test_member_profile_shows_checkin_history(): void
    {
        $owner = $this->createOwner();
        $member = $this->createMember();

        Attendance::create([
            'member_id' => $member->id,
            'date'      => now()->format('Y-m-d'),
            'status'    => 'present',
            'check_in_time' => '10:00:00'
        ]);

        $response = $this->actingAs($owner)->get(route('members.show', $member));
        $response->assertStatus(200);
        $response->assertSee('Check-in History');
        $response->assertSee('Present');
        // '10:00 AM'
        $response->assertSee('10:00 AM');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 9. Activity Log is created on marking attendance
    // ─────────────────────────────────────────────────────────────────────────
    public function test_activity_log_is_created_when_attendance_marked(): void
    {
        $owner = $this->createOwner();
        $member = $this->createMember();
        $date = now()->format('Y-m-d');

        $this->actingAs($owner)->post('/attendances', [
            'member_id' => $member->id,
            'date'      => $date,
            'status'    => 'present',
        ]);

        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $owner->id,
            'action'  => 'Attendance Marked',
            'subject_type' => Attendance::class,
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 10. Checkout action works correctly
    // ─────────────────────────────────────────────────────────────────────────
    public function test_checkout_action_works_correctly(): void
    {
        $owner = $this->createOwner();
        $member = $this->createMember();
        $date = now()->format('Y-m-d');

        $attendance = Attendance::create([
            'member_id' => $member->id,
            'date'      => $date,
            'status'    => 'present',
            'check_in_time' => '10:00:00',
        ]);

        $response = $this->actingAs($owner)->patch(route('attendances.checkout', $attendance));

        $response->assertRedirect();
        
        $this->assertEquals(1, Attendance::count());
        $this->assertDatabaseHas('attendances', [
            'id' => $attendance->id,
            'check_in_time' => '10:00:00',
            'status' => 'present',
        ]);
        
        $attendance->refresh();
        $this->assertNotNull($attendance->check_out_time);
    }
}
