<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\FeePayment;
use App\Models\GymSetting;
use App\Models\Member;
use App\Models\MemberMembership;
use App\Models\MembershipPlan;
use App\Models\Trainer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OfflineSyncTest extends TestCase
{
    use RefreshDatabase;

    // ─── Helpers ───────────────────────────────────────────────────────────────

    private function createOwner(): User
    {
        return User::factory()->create([
            'name'     => 'Test Owner',
            'email'    => 'owner@warmup.test',
            'password' => bcrypt('password123'),
        ]);
    }

    // ─── Auth Guard Tests ──────────────────────────────────────────────────────

    public function test_guests_cannot_access_offline_sync_endpoint(): void
    {
        $response = $this->postJson(route('api.offline.sync'), []);
        $response->assertUnauthorized();
    }

    public function test_sync_endpoint_requires_actions_array(): void
    {
        $user     = $this->createOwner();
        $response = $this->actingAs($user)->postJson(route('api.offline.sync'), []);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['actions']);
    }

    public function test_sync_endpoint_rejects_empty_actions(): void
    {
        $user     = $this->createOwner();
        $response = $this->actingAs($user)->postJson(route('api.offline.sync'), [
            'actions' => [],
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['actions']);
    }

    public function test_sync_endpoint_rejects_actions_without_type(): void
    {
        $user     = $this->createOwner();
        $response = $this->actingAs($user)->postJson(route('api.offline.sync'), [
            'actions' => [
                ['data' => ['name' => 'test']],
            ],
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['actions.0.type']);
    }

    // ─── member_create ─────────────────────────────────────────────────────────

    public function test_sync_can_create_member(): void
    {
        $user     = $this->createOwner();
        $response = $this->actingAs($user)->postJson(route('api.offline.sync'), [
            'actions' => [
                [
                    'type' => 'member_create',
                    'data' => [
                        'name'         => 'Offline Member',
                        'phone'        => '03009999999',
                        'gender'       => 'male',
                        'joining_date' => now()->toDateString(),
                        'status'       => 'active',
                    ],
                ],
            ],
        ]);

        $response->assertOk();
        $body = $response->json();

        $this->assertEquals(1, $body['synced']);
        $this->assertEquals(0, $body['failed']);
        $this->assertEquals('success', $body['results'][0]['status']);

        $this->assertDatabaseHas('members', ['name' => 'Offline Member', 'phone' => '03009999999']);
    }

    public function test_sync_member_create_fails_with_missing_required_fields(): void
    {
        $user     = $this->createOwner();
        $response = $this->actingAs($user)->postJson(route('api.offline.sync'), [
            'actions' => [
                [
                    'type' => 'member_create',
                    'data' => ['email' => 'no-required-fields@test.com'],
                ],
            ],
        ]);

        // Should return 422 (all failed)
        $response->assertStatus(422);
        $body = $response->json();
        $this->assertEquals(0, $body['synced']);
        $this->assertEquals(1, $body['failed']);
        $this->assertEquals('error', $body['results'][0]['status']);
    }

    // ─── member_update ─────────────────────────────────────────────────────────

    public function test_sync_can_update_member(): void
    {
        $user = $this->createOwner();
        $member = Member::create([
            'name'         => 'Old Name',
            'phone'        => '03001234567',
            'gender'       => 'male',
            'joining_date' => now()->toDateString(),
            'status'       => 'active',
        ]);

        $response = $this->actingAs($user)->postJson(route('api.offline.sync'), [
            'actions' => [
                [
                    'type' => 'member_update',
                    'data' => [
                        'id'               => $member->id,
                        'client_updated_at' => $member->updated_at->toIso8601String(),
                        'name'             => 'Updated Name',
                        'phone'            => '03009876543',
                    ],
                ],
            ],
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('members', ['id' => $member->id, 'name' => 'Updated Name']);
    }

    public function test_sync_member_update_fails_without_id(): void
    {
        $user     = $this->createOwner();
        $response = $this->actingAs($user)->postJson(route('api.offline.sync'), [
            'actions' => [
                [
                    'type' => 'member_update',
                    'data' => ['name' => 'No ID Provided'],
                ],
            ],
        ]);

        $response->assertStatus(422);
        $body = $response->json();
        $this->assertEquals(0, $body['synced']);
        $this->assertEquals(1, $body['failed']);
    }

    // ─── attendance_create ─────────────────────────────────────────────────────

    public function test_sync_can_create_attendance(): void
    {
        $user   = $this->createOwner();
        $member = Member::create([
            'name'         => 'Attend Member',
            'phone'        => '03001111111',
            'gender'       => 'male',
            'joining_date' => now()->toDateString(),
            'status'       => 'active',
        ]);

        $response = $this->actingAs($user)->postJson(route('api.offline.sync'), [
            'actions' => [
                [
                    'type' => 'attendance_create',
                    'data' => [
                        'member_id'     => $member->id,
                        'date'          => now()->toDateString(),
                        'status'        => 'present',
                        'check_in_time' => '09:00:00',
                    ],
                ],
            ],
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('attendances', [
            'member_id' => $member->id,
            'status'    => 'present',
        ]);
    }

    public function test_sync_attendance_is_idempotent_for_same_member_date(): void
    {
        $user   = $this->createOwner();
        $member = Member::create([
            'name'         => 'Idem Member',
            'phone'        => '03002222222',
            'gender'       => 'female',
            'joining_date' => now()->toDateString(),
            'status'       => 'active',
        ]);

        $date = now()->toDateString();

        // Create once
        $response1 = $this->actingAs($user)->postJson(route('api.offline.sync'), [
            'actions' => [[
                'type' => 'attendance_create',
                'data' => ['member_id' => $member->id, 'date' => $date, 'status' => 'present'],
            ]],
        ]);
        
        $response1->assertOk();

        // Create again (should not fail / not duplicate)
        $response = $this->actingAs($user)->postJson(route('api.offline.sync'), [
            'actions' => [[
                'type' => 'attendance_create',
                'data' => ['member_id' => $member->id, 'date' => $date, 'status' => 'present'],
            ]],
        ]);

        if ($response->status() !== 200) {
            dump($response->json());
        }
        $response->assertOk();
        $this->assertEquals(1, Attendance::where('member_id', $member->id)->count());
    }

    // ─── expense_create ────────────────────────────────────────────────────────

    public function test_sync_can_create_expense(): void
    {
        $user     = $this->createOwner();
        $category = ExpenseCategory::create(['name' => 'Rent']);

        $response = $this->actingAs($user)->postJson(route('api.offline.sync'), [
            'actions' => [
                [
                    'type' => 'expense_create',
                    'data' => [
                        'expense_category_id' => $category->id,
                        'title'               => 'Monthly Rent',
                        'amount'              => 25000,
                        'expense_date'        => now()->toDateString(),
                    ],
                ],
            ],
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('expenses', ['title' => 'Monthly Rent', 'amount' => 25000]);
    }

    // ─── fee_payment_create ────────────────────────────────────────────────────

    public function test_sync_can_create_fee_payment(): void
    {
        $user   = $this->createOwner();
        $plan   = MembershipPlan::create(['name' => 'Monthly', 'duration_days' => 30, 'price' => 3000]);
        $member = Member::create([
            'name'         => 'Fee Member',
            'phone'        => '03003333333',
            'gender'       => 'male',
            'joining_date' => now()->toDateString(),
            'status'       => 'active',
        ]);
        $membership = MemberMembership::create([
            'member_id'          => $member->id,
            'membership_plan_id' => $plan->id,
            'start_date'         => now()->toDateString(),
            'end_date'           => now()->addDays(30)->toDateString(),
            'total_amount'       => 3000,
            'paid_amount'        => 0,
            'remaining_amount'   => 3000,
            'status'             => 'active',
        ]);

        $response = $this->actingAs($user)->postJson(route('api.offline.sync'), [
            'actions' => [
                [
                    'type' => 'fee_payment_create',
                    'data' => [
                        'member_id'           => $member->id,
                        'member_membership_id' => $membership->id,
                        'amount_paid'          => 3000,
                        'payment_date'         => now()->toDateString(),
                        'payment_method'       => 'cash',
                    ],
                ],
            ],
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('fee_payments', [
            'member_id'   => $member->id,
            'amount_paid' => 3000,
        ]);
    }

    // ─── Unknown action type ───────────────────────────────────────────────────

    public function test_sync_handles_unknown_action_type_gracefully(): void
    {
        $user     = $this->createOwner();
        $response = $this->actingAs($user)->postJson(route('api.offline.sync'), [
            'actions' => [
                [
                    'type' => 'unknown_action_xyz',
                    'data' => ['dummy' => 'data'],
                ],
            ],
        ]);

        $response->assertStatus(422);
        $body = $response->json();
        $this->assertEquals(0, $body['synced']);
        $this->assertEquals(1, $body['failed']);
        $this->assertEquals('error', $body['results'][0]['status']);
    }

    // ─── Mixed batch ───────────────────────────────────────────────────────────

    public function test_sync_handles_mixed_success_and_failure_batch(): void
    {
        $user     = $this->createOwner();
        $category = ExpenseCategory::create(['name' => 'Utilities']);

        $response = $this->actingAs($user)->postJson(route('api.offline.sync'), [
            'actions' => [
                // Valid action
                [
                    'type' => 'member_create',
                    'data' => [
                        'name'         => 'Valid Member',
                        'phone'        => '03004444444',
                        'gender'       => 'male',
                        'joining_date' => now()->toDateString(),
                        'status'       => 'active',
                    ],
                ],
                // Invalid action (missing required fields)
                [
                    'type' => 'member_create',
                    'data' => ['email' => 'nomandatory@test.com'],
                ],
            ],
        ]);

        // Partial success: 1 synced, 1 failed → returns 200
        $response->assertOk();
        $body = $response->json();
        $this->assertEquals(1, $body['synced']);
        $this->assertEquals(1, $body['failed']);

        $this->assertDatabaseHas('members', ['name' => 'Valid Member']);
    }

    // ─── Activity Log ─────────────────────────────────────────────────────────

    public function test_successful_sync_logs_activity(): void
    {
        $user     = $this->createOwner();
        $response = $this->actingAs($user)->postJson(route('api.offline.sync'), [
            'actions' => [
                [
                    'type' => 'member_create',
                    'data' => [
                        'name'         => 'Log Test Member',
                        'phone'        => '03005555555',
                        'gender'       => 'female',
                        'joining_date' => now()->toDateString(),
                        'status'       => 'active',
                    ],
                ],
            ],
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $user->id,
            'action'  => 'Offline Sync',
        ]);
    }

    // ═══════════════════════════════════════════════════════════════════════════
    // Phase 15 Tests
    // ═══════════════════════════════════════════════════════════════════════════

    // ─── member_delete ────────────────────────────────────────────────────────

    public function test_guest_cannot_sync_member_delete(): void
    {
        $response = $this->postJson(route('api.offline.sync'), [
            'actions' => [['type' => 'member_delete', 'data' => ['id' => 1]]],
        ]);
        $response->assertUnauthorized();
    }

    public function test_sync_can_delete_member(): void
    {
        $user   = $this->createOwner();
        $member = Member::create([
            'name'         => 'Delete Me',
            'phone'        => '03010000001',
            'gender'       => 'male',
            'joining_date' => now()->toDateString(),
            'status'       => 'active',
        ]);

        $response = $this->actingAs($user)->postJson(route('api.offline.sync'), [
            'actions' => [
                ['type' => 'member_delete', 'data' => ['id' => $member->id]],
            ],
        ]);

        $response->assertOk();
        $body = $response->json();
        $this->assertEquals(1, $body['synced']);
        $this->assertEquals('success', $body['results'][0]['status']);

        // Member should be soft-deleted
        $this->assertSoftDeleted('members', ['id' => $member->id]);
    }

    public function test_sync_member_delete_is_idempotent_for_nonexistent_member(): void
    {
        $user = $this->createOwner();

        $response = $this->actingAs($user)->postJson(route('api.offline.sync'), [
            'actions' => [
                ['type' => 'member_delete', 'data' => ['id' => 99999]],
            ],
        ]);

        // Non-existent member treated as already deleted — success
        $response->assertOk();
        $body = $response->json();
        $this->assertEquals(1, $body['synced']);
        $this->assertEquals('success', $body['results'][0]['status']);
    }

    public function test_sync_member_delete_fails_without_id(): void
    {
        $user = $this->createOwner();

        $response = $this->actingAs($user)->postJson(route('api.offline.sync'), [
            'actions' => [
                // Provide non-empty data (passes outer validation) but without 'id'
                ['type' => 'member_delete', 'data' => ['note' => 'no id provided']],
            ],
        ]);

        $response->assertStatus(422);
        $body = $response->json();
        $this->assertEquals(0, $body['synced']);
        $this->assertEquals(1, $body['failed']);
        $this->assertEquals('error', $body['results'][0]['status']);
    }

    // ─── trainer_create ───────────────────────────────────────────────────────

    public function test_sync_can_create_trainer(): void
    {
        $user = $this->createOwner();

        $response = $this->actingAs($user)->postJson(route('api.offline.sync'), [
            'actions' => [
                [
                    'type' => 'trainer_create',
                    'data' => [
                        'name'         => 'New Offline Trainer',
                        'phone'        => '03020000001',
                        'gender'       => 'male',
                        'joining_date' => now()->toDateString(),
                    ],
                ],
            ],
        ]);

        $response->assertOk();
        $body = $response->json();
        $this->assertEquals(1, $body['synced']);
        $this->assertEquals('success', $body['results'][0]['status']);
        $this->assertDatabaseHas('trainers', ['name' => 'New Offline Trainer']);
    }

    public function test_sync_trainer_create_fails_with_missing_required_fields(): void
    {
        $user = $this->createOwner();

        $response = $this->actingAs($user)->postJson(route('api.offline.sync'), [
            'actions' => [
                [
                    'type' => 'trainer_create',
                    'data' => ['email' => 'no-required@test.com'],
                ],
            ],
        ]);

        $response->assertStatus(422);
        $body = $response->json();
        $this->assertEquals(0, $body['synced']);
        $this->assertEquals(1, $body['failed']);
    }

    public function test_sync_trainer_create_rejects_invalid_gender(): void
    {
        $user = $this->createOwner();

        $response = $this->actingAs($user)->postJson(route('api.offline.sync'), [
            'actions' => [
                [
                    'type' => 'trainer_create',
                    'data' => [
                        'name'         => 'Bad Gender Trainer',
                        'phone'        => '03020000002',
                        'gender'       => 'robot',
                        'joining_date' => now()->toDateString(),
                    ],
                ],
            ],
        ]);

        $response->assertStatus(422);
        $body = $response->json();
        $this->assertEquals(1, $body['failed']);
        $this->assertEquals('error', $body['results'][0]['status']);
    }

    public function test_sync_trainer_create_rejects_negative_salary(): void
    {
        $user = $this->createOwner();

        $response = $this->actingAs($user)->postJson(route('api.offline.sync'), [
            'actions' => [
                [
                    'type' => 'trainer_create',
                    'data' => [
                        'name'         => 'Neg Salary Trainer',
                        'phone'        => '03020000003',
                        'gender'       => 'female',
                        'joining_date' => now()->toDateString(),
                        'salary'       => -500,
                    ],
                ],
            ],
        ]);

        $response->assertStatus(422);
        $body = $response->json();
        $this->assertEquals(1, $body['failed']);
    }

    public function test_guest_cannot_sync_trainer_create(): void
    {
        $response = $this->postJson(route('api.offline.sync'), [
            'actions' => [
                [
                    'type' => 'trainer_create',
                    'data' => ['name' => 'Ghost Trainer', 'phone' => '0300', 'gender' => 'male', 'joining_date' => now()->toDateString()],
                ],
            ],
        ]);
        $response->assertUnauthorized();
    }

    // ─── trainer_update ───────────────────────────────────────────────────────

    public function test_sync_can_update_trainer_with_fresh_timestamp(): void
    {
        $user    = $this->createOwner();
        $trainer = Trainer::create([
            'name'         => 'Trainer Original',
            'phone'        => '03030000001',
            'gender'       => 'male',
            'joining_date' => now()->toDateString(),
            'is_active'    => true,
        ]);

        $response = $this->actingAs($user)->postJson(route('api.offline.sync'), [
            'actions' => [
                [
                    'type' => 'trainer_update',
                    'data' => [
                        'id'               => $trainer->id,
                        'client_updated_at' => $trainer->updated_at->toIso8601String(),
                        'name'             => 'Trainer Updated',
                    ],
                ],
            ],
        ]);

        $response->assertOk();
        $body = $response->json();
        $this->assertEquals(1, $body['synced']);
        $this->assertEquals('success', $body['results'][0]['status']);
        $this->assertDatabaseHas('trainers', ['id' => $trainer->id, 'name' => 'Trainer Updated']);
    }

    public function test_sync_trainer_update_rejected_when_server_is_newer(): void
    {
        $user    = $this->createOwner();
        $trainer = Trainer::create([
            'name'         => 'Server Fresh Trainer',
            'phone'        => '03030000002',
            'gender'       => 'female',
            'joining_date' => now()->toDateString(),
            'is_active'    => true,
        ]);

        // Simulate server being updated AFTER the client captured their timestamp
        $staleClientTimestamp = now()->subHour()->toIso8601String();

        $response = $this->actingAs($user)->postJson(route('api.offline.sync'), [
            'actions' => [
                [
                    'type' => 'trainer_update',
                    'data' => [
                        'id'               => $trainer->id,
                        'client_updated_at' => $staleClientTimestamp,
                        'name'             => 'Should Not Be Saved',
                    ],
                ],
            ],
        ]);

        $response->assertOk();
        $body = $response->json();
        $this->assertEquals(0, $body['synced']);
        $this->assertEquals(1, $body['conflicts']);
        $this->assertEquals('conflict', $body['results'][0]['status']);

        // DB should remain unchanged
        $this->assertDatabaseHas('trainers', ['id' => $trainer->id, 'name' => 'Server Fresh Trainer']);
    }

    public function test_sync_trainer_update_fails_without_id(): void
    {
        $user = $this->createOwner();

        $response = $this->actingAs($user)->postJson(route('api.offline.sync'), [
            'actions' => [
                ['type' => 'trainer_update', 'data' => ['name' => 'No ID']],
            ],
        ]);

        $response->assertStatus(422);
        $body = $response->json();
        $this->assertEquals(1, $body['failed']);
    }

    public function test_sync_trainer_update_fails_without_client_timestamp(): void
    {
        $user    = $this->createOwner();
        $trainer = Trainer::create([
            'name'         => 'No TS Trainer',
            'phone'        => '03030000003',
            'gender'       => 'male',
            'joining_date' => now()->toDateString(),
            'is_active'    => true,
        ]);

        // No client_updated_at provided → should return conflict (cannot verify freshness)
        $response = $this->actingAs($user)->postJson(route('api.offline.sync'), [
            'actions' => [
                [
                    'type' => 'trainer_update',
                    'data' => ['id' => $trainer->id, 'name' => 'No Timestamp'],
                ],
            ],
        ]);

        $response->assertOk();
        $body = $response->json();
        $this->assertEquals(1, $body['conflicts']);
        $this->assertEquals('conflict', $body['results'][0]['status']);
    }

    // ─── settings_update ──────────────────────────────────────────────────────

    public function test_sync_can_update_settings_with_fresh_timestamp(): void
    {
        $user     = $this->createOwner();
        $settings = GymSetting::create([
            'gym_name'        => 'Original Gym',
            'owner_name'      => 'Owner',
            'currency'        => 'PKR',
            'currency_symbol' => 'Rs',
            'timezone'        => 'Asia/Karachi',
            'language'        => 'en',
            'theme'           => 'light',
            'date_format'     => 'd/m/Y',
            'time_format'     => '12h',
        ]);

        $response = $this->actingAs($user)->postJson(route('api.offline.sync'), [
            'actions' => [
                [
                    'type' => 'settings_update',
                    'data' => [
                        'client_updated_at' => $settings->updated_at->toIso8601String(),
                        'gym_name'          => 'Updated Gym Name',
                        'language'          => 'ur',
                    ],
                ],
            ],
        ]);

        $response->assertOk();
        $body = $response->json();
        $this->assertEquals(1, $body['synced']);
        $this->assertEquals('success', $body['results'][0]['status']);
        $this->assertDatabaseHas('gym_settings', ['gym_name' => 'Updated Gym Name', 'language' => 'ur']);
    }

    public function test_sync_settings_update_rejected_when_server_is_newer(): void
    {
        $user     = $this->createOwner();
        $settings = GymSetting::create([
            'gym_name'        => 'Fresh Gym',
            'owner_name'      => 'Owner',
            'currency'        => 'PKR',
            'currency_symbol' => 'Rs',
            'timezone'        => 'Asia/Karachi',
            'language'        => 'en',
            'theme'           => 'light',
            'date_format'     => 'd/m/Y',
            'time_format'     => '12h',
        ]);

        $staleClientTimestamp = now()->subHour()->toIso8601String();

        $response = $this->actingAs($user)->postJson(route('api.offline.sync'), [
            'actions' => [
                [
                    'type' => 'settings_update',
                    'data' => [
                        'client_updated_at' => $staleClientTimestamp,
                        'gym_name'          => 'Should Not Save',
                    ],
                ],
            ],
        ]);

        $response->assertOk();
        $body = $response->json();
        $this->assertEquals(1, $body['conflicts']);
        $this->assertEquals('conflict', $body['results'][0]['status']);
        $this->assertDatabaseHas('gym_settings', ['gym_name' => 'Fresh Gym']);
    }

    public function test_sync_settings_update_rejects_invalid_language(): void
    {
        $user     = $this->createOwner();
        $settings = GymSetting::create([
            'gym_name'        => 'Test Gym',
            'owner_name'      => 'Owner',
            'currency'        => 'PKR',
            'currency_symbol' => 'Rs',
            'timezone'        => 'Asia/Karachi',
            'language'        => 'en',
            'theme'           => 'light',
            'date_format'     => 'd/m/Y',
            'time_format'     => '12h',
        ]);

        $response = $this->actingAs($user)->postJson(route('api.offline.sync'), [
            'actions' => [
                [
                    'type' => 'settings_update',
                    'data' => [
                        'client_updated_at' => $settings->updated_at->toIso8601String(),
                        'language'          => 'fr', // not supported
                    ],
                ],
            ],
        ]);

        $response->assertStatus(422);
        $body = $response->json();
        $this->assertEquals(1, $body['failed']);
        $this->assertEquals('error', $body['results'][0]['status']);
    }

    public function test_sync_settings_update_rejects_invalid_theme(): void
    {
        $user     = $this->createOwner();
        $settings = GymSetting::create([
            'gym_name'        => 'Theme Test Gym',
            'owner_name'      => 'Owner',
            'currency'        => 'PKR',
            'currency_symbol' => 'Rs',
            'timezone'        => 'Asia/Karachi',
            'language'        => 'en',
            'theme'           => 'light',
            'date_format'     => 'd/m/Y',
            'time_format'     => '12h',
        ]);

        $response = $this->actingAs($user)->postJson(route('api.offline.sync'), [
            'actions' => [
                [
                    'type' => 'settings_update',
                    'data' => [
                        'client_updated_at' => $settings->updated_at->toIso8601String(),
                        'theme'             => 'rainbow', // invalid
                    ],
                ],
            ],
        ]);

        $response->assertStatus(422);
        $body = $response->json();
        $this->assertEquals(1, $body['failed']);
    }

    // ─── LWW: member_update conflict resolution ───────────────────────────────

    public function test_sync_member_update_rejected_when_server_is_newer(): void
    {
        $user   = $this->createOwner();
        $member = Member::create([
            'name'         => 'LWW Member',
            'phone'        => '03040000001',
            'gender'       => 'male',
            'joining_date' => now()->toDateString(),
            'status'       => 'active',
        ]);

        // Client uses a timestamp older than the server record's updated_at
        $staleTimestamp = now()->subHour()->toIso8601String();

        $response = $this->actingAs($user)->postJson(route('api.offline.sync'), [
            'actions' => [
                [
                    'type' => 'member_update',
                    'data' => [
                        'id'               => $member->id,
                        'client_updated_at' => $staleTimestamp,
                        'name'             => 'Should Not Be Saved',
                    ],
                ],
            ],
        ]);

        $response->assertOk();
        $body = $response->json();
        $this->assertEquals(0, $body['synced']);
        $this->assertEquals(1, $body['conflicts']);
        $this->assertEquals('conflict', $body['results'][0]['status']);
        $this->assertDatabaseHas('members', ['id' => $member->id, 'name' => 'LWW Member']);
    }

    public function test_sync_member_update_succeeds_with_fresh_timestamp(): void
    {
        $user   = $this->createOwner();
        $member = Member::create([
            'name'         => 'Fresh TS Member',
            'phone'        => '03040000002',
            'gender'       => 'female',
            'joining_date' => now()->toDateString(),
            'status'       => 'active',
        ]);

        $response = $this->actingAs($user)->postJson(route('api.offline.sync'), [
            'actions' => [
                [
                    'type' => 'member_update',
                    'data' => [
                        'id'               => $member->id,
                        'client_updated_at' => $member->updated_at->toIso8601String(),
                        'name'             => 'LWW Updated Name',
                    ],
                ],
            ],
        ]);

        $response->assertOk();
        $body = $response->json();
        $this->assertEquals(1, $body['synced']);
        $this->assertEquals('success', $body['results'][0]['status']);
        $this->assertDatabaseHas('members', ['id' => $member->id, 'name' => 'LWW Updated Name']);
    }

    public function test_sync_member_update_without_timestamp_is_rejected_as_conflict(): void
    {
        $user   = $this->createOwner();
        $member = Member::create([
            'name'         => 'No TS Member',
            'phone'        => '03040000003',
            'gender'       => 'male',
            'joining_date' => now()->toDateString(),
            'status'       => 'active',
        ]);

        $response = $this->actingAs($user)->postJson(route('api.offline.sync'), [
            'actions' => [
                [
                    'type' => 'member_update',
                    'data' => [
                        'id'   => $member->id,
                        'name' => 'No Timestamp Update',
                        // no client_updated_at
                    ],
                ],
            ],
        ]);

        $response->assertOk();
        $body = $response->json();
        $this->assertEquals(1, $body['conflicts']);
        $this->assertEquals('conflict', $body['results'][0]['status']);
        $this->assertDatabaseHas('members', ['id' => $member->id, 'name' => 'No TS Member']);
    }

    // ─── Conflict response structure ──────────────────────────────────────────

    public function test_conflict_response_has_correct_structure(): void
    {
        $user   = $this->createOwner();
        $member = Member::create([
            'name'         => 'Struct Member',
            'phone'        => '03050000001',
            'gender'       => 'male',
            'joining_date' => now()->toDateString(),
            'status'       => 'active',
        ]);

        $response = $this->actingAs($user)->postJson(route('api.offline.sync'), [
            'actions' => [
                [
                    'type' => 'member_update',
                    'data' => [
                        'id'               => $member->id,
                        'client_updated_at' => now()->subHour()->toIso8601String(),
                        'name'             => 'Conflict Test',
                    ],
                ],
            ],
        ]);

        $response->assertOk();
        $body = $response->json();

        $this->assertArrayHasKey('synced', $body);
        $this->assertArrayHasKey('failed', $body);
        $this->assertArrayHasKey('conflicts', $body);
        $this->assertArrayHasKey('results', $body);
        $this->assertEquals('conflict', $body['results'][0]['status']);
        $this->assertArrayHasKey('message', $body['results'][0]);
    }

    public function test_conflict_does_not_corrupt_unrelated_successful_actions(): void
    {
        $user   = $this->createOwner();
        $member = Member::create([
            'name'         => 'Conflict Isolation',
            'phone'        => '03050000002',
            'gender'       => 'male',
            'joining_date' => now()->toDateString(),
            'status'       => 'active',
        ]);

        $response = $this->actingAs($user)->postJson(route('api.offline.sync'), [
            'actions' => [
                // Conflicted update
                [
                    'type' => 'member_update',
                    'data' => [
                        'id'               => $member->id,
                        'client_updated_at' => now()->subHour()->toIso8601String(),
                        'name'             => 'Should Not Save',
                    ],
                ],
                // Valid unrelated creation
                [
                    'type' => 'member_create',
                    'data' => [
                        'name'         => 'New From Batch',
                        'phone'        => '03050000003',
                        'gender'       => 'female',
                        'joining_date' => now()->toDateString(),
                        'status'       => 'active',
                    ],
                ],
            ],
        ]);

        $response->assertOk();
        $body = $response->json();
        $this->assertEquals(1, $body['synced']);
        $this->assertEquals(1, $body['conflicts']);
        $this->assertEquals(0, $body['failed']);

        // Conflicted member unchanged
        $this->assertDatabaseHas('members', ['id' => $member->id, 'name' => 'Conflict Isolation']);
        // New member created successfully
        $this->assertDatabaseHas('members', ['name' => 'New From Batch']);
    }
}
