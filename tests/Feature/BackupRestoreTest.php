<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\GymSetting;
use App\Models\Member;
use App\Models\MemberMembership;
use App\Models\MembershipPlan;
use App\Models\Trainer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class BackupRestoreTest extends TestCase
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

    private function createSettings(): GymSetting
    {
        return GymSetting::create([
            'gym_name'   => 'Test Gym',
            'owner_name' => 'Test Owner',
            'currency'   => 'PKR',
            'currency_symbol' => 'Rs',
            'timezone'   => 'Asia/Karachi',
            'language'   => 'en',
            'theme'      => 'light',
            'date_format' => 'd/m/Y',
            'time_format' => '12h',
        ]);
    }

    // ─── Auth Guard Tests ──────────────────────────────────────────────────────

    public function test_guests_cannot_download_backup(): void
    {
        $response = $this->get(route('settings.backup.download'));
        $response->assertRedirect(route('login'));
    }

    public function test_guests_cannot_restore_backup(): void
    {
        $response = $this->post(route('settings.backup.restore'));
        $response->assertRedirect(route('login'));
    }

    // ─── Download Backup Tests ─────────────────────────────────────────────────

    public function test_authenticated_user_can_download_backup(): void
    {
        $user = $this->createOwner();
        $this->createSettings();

        $response = $this->actingAs($user)->get(route('settings.backup.download'));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/json');
        $this->assertStringContainsString(
            'attachment',
            $response->headers->get('Content-Disposition')
        );
        $this->assertStringContainsString(
            'warmup_backup_',
            $response->headers->get('Content-Disposition')
        );
    }

    public function test_backup_contains_valid_json_structure(): void
    {
        $user = $this->createOwner();
        $this->createSettings();

        // Create some data to export
        $plan    = MembershipPlan::create(['name' => 'Monthly', 'duration_days' => 30, 'price' => 3000]);
        $trainer = Trainer::create([
            'name' => 'John Trainer', 'phone' => '03001111111',
            'gender' => 'male', 'joining_date' => now()->toDateString(),
        ]);
        $member = Member::create([
            'name' => 'Jane Member', 'phone' => '03002222222',
            'gender' => 'female', 'joining_date' => now()->toDateString(),
        ]);

        $response = $this->actingAs($user)->get(route('settings.backup.download'));
        $response->assertStatus(200);

        $json = json_decode($response->getContent(), true);

        $this->assertNotNull($json, 'Backup JSON is valid');
        $this->assertArrayHasKey('meta',              $json);
        $this->assertArrayHasKey('gym_settings',      $json);
        $this->assertArrayHasKey('membership_plans',  $json);
        $this->assertArrayHasKey('trainers',          $json);
        $this->assertArrayHasKey('members',           $json);
        $this->assertArrayHasKey('expense_categories', $json);
        $this->assertArrayHasKey('expenses',          $json);

        // Meta checks
        $this->assertEquals('WarmUp Gym Management', $json['meta']['app']);
        $this->assertNotEmpty($json['meta']['version']);
        $this->assertNotEmpty($json['meta']['created_at']);

        // Data checks
        $this->assertCount(1, $json['gym_settings']);
        $this->assertCount(1, $json['membership_plans']);
        $this->assertCount(1, $json['trainers']);
        $this->assertCount(1, $json['members']);
    }

    public function test_backup_logs_activity(): void
    {
        $user = $this->createOwner();
        $this->createSettings();

        $this->actingAs($user)->get(route('settings.backup.download'));

        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $user->id,
            'action'  => 'Backup Downloaded',
        ]);
    }

    // ─── Restore Backup Tests ──────────────────────────────────────────────────

    public function test_restore_requires_a_file(): void
    {
        $user = $this->createOwner();
        $this->createSettings();

        $response = $this->actingAs($user)->post(route('settings.backup.restore'), []);
        $response->assertSessionHasErrors('backup_file');
    }

    public function test_restore_rejects_non_json_files(): void
    {
        $user = $this->createOwner();
        $this->createSettings();

        $file     = UploadedFile::fake()->create('backup.txt', 10, 'text/plain');
        $response = $this->actingAs($user)->post(route('settings.backup.restore'), [
            'backup_file' => $file,
        ]);

        $response->assertSessionHasErrors('backup_file');
    }

    public function test_restore_rejects_invalid_json_content(): void
    {
        $user = $this->createOwner();
        $this->createSettings();

        // Upload a file that has .json extension but is invalid JSON
        $file     = UploadedFile::fake()->createWithContent('bad.json', '{not: valid json}');
        $response = $this->actingAs($user)->post(route('settings.backup.restore'), [
            'backup_file' => $file,
        ]);

        $response->assertSessionHas('error');
    }

    public function test_restore_rejects_json_without_required_keys(): void
    {
        $user = $this->createOwner();
        $this->createSettings();

        $file     = UploadedFile::fake()->createWithContent('bad.json', json_encode(['foo' => 'bar']));
        $response = $this->actingAs($user)->post(route('settings.backup.restore'), [
            'backup_file' => $file,
        ]);

        $response->assertSessionHas('error');
    }

    public function test_restore_rejects_non_warmup_backup(): void
    {
        $user = $this->createOwner();
        $this->createSettings();

        $badBackup = json_encode([
            'meta'    => ['app' => 'SomeOtherApp'],
            'members' => [],
        ]);

        $file     = UploadedFile::fake()->createWithContent('other.json', $badBackup);
        $response = $this->actingAs($user)->post(route('settings.backup.restore'), [
            'backup_file' => $file,
        ]);

        $response->assertSessionHas('error');
    }

    public function test_restore_from_valid_backup_succeeds(): void
    {
        $user = $this->createOwner();
        $this->createSettings();

        // Build a valid WarmUp backup JSON manually
        $backup = json_encode([
            'meta' => [
                'version'    => '1.0',
                'app'        => 'WarmUp Gym Management',
                'created_at' => now()->toIso8601String(),
                'created_by' => 'Test Owner',
            ],
            'gym_settings' => [
                [
                    'gym_name'        => 'Restored Gym',
                    'owner_name'      => 'Restored Owner',
                    'currency'        => 'PKR',
                    'currency_symbol' => 'Rs',
                    'timezone'        => 'Asia/Karachi',
                    'language'        => 'en',
                    'theme'           => 'light',
                    'date_format'     => 'd/m/Y',
                    'time_format'     => '12h',
                ],
            ],
            'membership_plans'   => [],
            'trainers'           => [],
            'members'            => [],
            'expense_categories' => [],
            'expenses'           => [],
        ]);

        $file     = UploadedFile::fake()->createWithContent('backup.json', $backup);
        $response = $this->actingAs($user)->post(route('settings.backup.restore'), [
            'backup_file' => $file,
        ]);

        $response->assertRedirect(route('settings.index'));
        $response->assertSessionHas('success');

        // Verify gym settings were restored
        $this->assertDatabaseHas('gym_settings', [
            'gym_name'  => 'Restored Gym',
            'owner_name' => 'Restored Owner',
        ]);
    }

    public function test_restore_logs_activity(): void
    {
        $user = $this->createOwner();
        $this->createSettings();

        $backup = json_encode([
            'meta' => [
                'version'    => '1.0',
                'app'        => 'WarmUp Gym Management',
                'created_at' => now()->toIso8601String(),
                'created_by' => 'Test Owner',
            ],
            'gym_settings'       => [['gym_name' => 'Test', 'owner_name' => 'Owner', 'currency' => 'PKR', 'currency_symbol' => 'Rs', 'timezone' => 'Asia/Karachi', 'language' => 'en', 'theme' => 'light', 'date_format' => 'd/m/Y', 'time_format' => '12h']],
            'membership_plans'   => [],
            'trainers'           => [],
            'members'            => [],
            'expense_categories' => [],
            'expenses'           => [],
        ]);

        $file = UploadedFile::fake()->createWithContent('backup.json', $backup);
        $this->actingAs($user)->post(route('settings.backup.restore'), [
            'backup_file' => $file,
        ]);

        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $user->id,
            'action'  => 'Backup Restored',
        ]);
    }
}
