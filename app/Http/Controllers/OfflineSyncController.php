<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Attendance;
use App\Models\Expense;
use App\Models\FeePayment;
use App\Models\GymSetting;
use App\Models\Member;
use App\Models\Trainer;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * OfflineSyncController — Phase 14 + Phase 15
 *
 * Processes a batch of offline-queued actions from IndexedDB.
 *
 * Supported action types (strict whitelist):
 *
 * CREATES
 *  - member_create      : create a new member
 *  - trainer_create     : create a new trainer          [Phase 15]
 *  - attendance_create  : mark attendance
 *  - expense_create     : add an expense
 *  - fee_payment_create : record a fee payment
 *
 * UPDATES (with Last-Write-Wins conflict resolution)
 *  - member_update      : update an existing member by id
 *  - trainer_update     : update an existing trainer by id [Phase 15]
 *  - settings_update    : update gym settings              [Phase 15]
 *
 * DELETES
 *  - member_delete      : soft-delete a member             [Phase 15]
 *
 * Response statuses per action:
 *  - success  : operation completed
 *  - conflict : LWW check failed — server record is newer
 *  - error    : operation failed (validation, not found, etc.)
 */
class OfflineSyncController extends Controller
{
    public function sync(Request $request)
    {
        $request->validate([
            'actions'        => 'required|array|min:1|max:200',
            'actions.*.type' => 'required|string',
            'actions.*.data' => 'required|array',
        ]);

        $results   = [];
        $synced    = 0;
        $failed    = 0;
        $conflicts = 0;

        foreach ($request->input('actions') as $index => $action) {
            $type = $action['type'];
            $data = $action['data'];

            try {
                DB::beginTransaction();

                [$status, $result] = match ($type) {
                    'member_create'      => ['success', $this->handleMemberCreate($data)],
                    'member_update'      => $this->handleMemberUpdate($data),
                    'member_delete'      => ['success', $this->handleMemberDelete($data)],
                    'trainer_create'     => ['success', $this->handleTrainerCreate($data)],
                    'trainer_update'     => $this->handleTrainerUpdate($data),
                    'attendance_create'  => ['success', $this->handleAttendanceCreate($data)],
                    'expense_create'     => ['success', $this->handleExpenseCreate($data)],
                    'fee_payment_create' => ['success', $this->handleFeePaymentCreate($data)],
                    'settings_update'    => $this->handleSettingsUpdate($data),
                    default              => throw new \InvalidArgumentException("Unknown action type: {$type}"),
                };

                DB::commit();

                if ($status === 'conflict') {
                    $conflicts++;
                    $results[] = [
                        'index'   => $index,
                        'type'    => $type,
                        'status'  => 'conflict',
                        'message' => $result,
                    ];
                } else {
                    $synced++;
                    $results[] = [
                        'index'  => $index,
                        'type'   => $type,
                        'status' => 'success',
                        'id'     => is_object($result) ? ($result->id ?? null) : null,
                    ];
                }

            } catch (\Throwable $e) {
                DB::rollBack();
                Log::warning("Offline sync failed for action [{$type}]: " . $e->getMessage());

                $failed++;
                $results[] = [
                    'index'   => $index,
                    'type'    => $type,
                    'status'  => 'error',
                    'message' => $e->getMessage(),
                ];
            }
        }

        // Log the batch sync activity (only when at least one action succeeded)
        if ($synced > 0) {
            ActivityLog::create([
                'user_id'      => auth()->id(),
                'action'       => 'Offline Sync',
                'description'  => "Synced {$synced} offline action(s). {$failed} failed. {$conflicts} conflict(s).",
                'subject_type' => null,
                'subject_id'   => null,
                'ip_address'   => $request->ip(),
                'user_agent'   => $request->userAgent(),
            ]);
        }

        $httpStatus = ($failed > 0 && $synced === 0 && $conflicts === 0) ? 422 : 200;

        return response()->json([
            'synced'    => $synced,
            'failed'    => $failed,
            'conflicts' => $conflicts,
            'results'   => $results,
        ], $httpStatus);
    }

    // ─────────────────────────────────────────────────────────────────
    // CREATE Handlers
    // ─────────────────────────────────────────────────────────────────

    private function handleMemberCreate(array $data): Member
    {
        $allowed = [
            'name', 'email', 'phone', 'gender', 'date_of_birth',
            'address', 'emergency_contact_name', 'emergency_contact_phone',
            'medical_notes', 'height', 'weight', 'blood_group',
            'joining_date', 'status', 'trainer_id',
        ];

        $filtered = array_intersect_key($data, array_flip($allowed));

        if (empty($filtered['name']) || empty($filtered['phone']) || empty($filtered['gender'])) {
            throw new \InvalidArgumentException('member_create requires name, phone, and gender.');
        }

        return Member::create($filtered);
    }

    private function handleTrainerCreate(array $data): Trainer
    {
        $allowed = [
            'name', 'email', 'phone', 'gender', 'date_of_birth',
            'specialization', 'bio', 'address', 'salary',
            'joining_date', 'is_active',
        ];

        $filtered = array_intersect_key($data, array_flip($allowed));

        if (empty($filtered['name']) || empty($filtered['phone']) || empty($filtered['gender'])) {
            throw new \InvalidArgumentException('trainer_create requires name, phone, and gender.');
        }
        if (empty($filtered['joining_date'])) {
            throw new \InvalidArgumentException('trainer_create requires joining_date.');
        }

        // Validate gender
        if (!in_array($filtered['gender'], ['male', 'female', 'other'])) {
            throw new \InvalidArgumentException('trainer_create: gender must be male, female, or other.');
        }

        // Validate salary if present
        if (isset($filtered['salary']) && (!is_numeric($filtered['salary']) || $filtered['salary'] < 0)) {
            throw new \InvalidArgumentException('trainer_create: salary must be a non-negative number.');
        }

        // Validate email uniqueness if provided
        if (!empty($filtered['email']) && Trainer::where('email', $filtered['email'])->exists()) {
            throw new \InvalidArgumentException('trainer_create: a trainer with that email already exists.');
        }

        // Default is_active to true if not provided
        $filtered['is_active'] = isset($filtered['is_active']) ? (bool) $filtered['is_active'] : true;

        return Trainer::create($filtered);
    }

    private function handleAttendanceCreate(array $data): Attendance
    {
        if (empty($data['member_id']) || empty($data['date'])) {
            throw new \InvalidArgumentException('attendance_create requires member_id and date.');
        }

        $attendance = Attendance::where('member_id', $data['member_id'])
            ->whereDate('date', $data['date'])
            ->first();

        if ($attendance) {
            return $attendance;
        }

        return Attendance::create([
            'member_id'      => $data['member_id'],
            'date'           => $data['date'],
            'status'         => $data['status']         ?? 'present',
            'check_in_time'  => $data['check_in_time']  ?? null,
            'check_out_time' => $data['check_out_time'] ?? null,
        ]);
    }

    private function handleExpenseCreate(array $data): Expense
    {
        $allowed = [
            'expense_category_id', 'title', 'amount',
            'expense_date', 'paid_to', 'notes',
        ];

        $filtered = array_intersect_key($data, array_flip($allowed));

        if (empty($filtered['expense_category_id']) || empty($filtered['title']) || empty($filtered['amount'])) {
            throw new \InvalidArgumentException('expense_create requires expense_category_id, title, and amount.');
        }

        return Expense::create($filtered);
    }

    private function handleFeePaymentCreate(array $data): FeePayment
    {
        $allowed = [
            'member_id', 'member_membership_id', 'amount_paid',
            'payment_date', 'due_date', 'payment_method',
            'receipt_number', 'notes',
        ];

        $filtered = array_intersect_key($data, array_flip($allowed));

        if (empty($filtered['member_id']) || empty($filtered['member_membership_id']) || empty($filtered['amount_paid'])) {
            throw new \InvalidArgumentException('fee_payment_create requires member_id, member_membership_id, and amount_paid.');
        }

        return FeePayment::create($filtered);
    }

    // ─────────────────────────────────────────────────────────────────
    // UPDATE Handlers (with Last-Write-Wins)
    // ─────────────────────────────────────────────────────────────────

    /**
     * Compare client timestamp against the server record's updated_at.
     *
     * Returns true  → client update is safe to apply (client_ts >= server_ts)
     * Returns false → conflict: server record is newer than the client's base
     */
    private function isClientUpdateFresh(?string $clientTimestamp, Carbon $serverUpdatedAt): bool
    {
        if (empty($clientTimestamp)) {
            // No timestamp provided: cannot verify freshness — reject to prevent blind overwrites
            return false;
        }

        try {
            $clientTs = Carbon::parse($clientTimestamp);
        } catch (\Exception $e) {
            // Malformed timestamp — reject
            return false;
        }

        // Client's base timestamp must be >= server's updated_at
        // (i.e., client saw at least as recent a version as the server has)
        return $clientTs->greaterThanOrEqualTo($serverUpdatedAt);
    }

    /** @return array{0: string, 1: Member|string} */
    private function handleMemberUpdate(array $data): array
    {
        if (empty($data['id'])) {
            throw new \InvalidArgumentException('member_update requires id.');
        }

        $member = Member::findOrFail($data['id']);

        // ── Last-Write-Wins check ──────────────────────────────────────
        if (!$this->isClientUpdateFresh($data['client_updated_at'] ?? null, $member->updated_at)) {
            return [
                'conflict',
                'The member record was modified after this offline change was queued. Update rejected to prevent data loss.',
            ];
        }

        $allowed = [
            'name', 'email', 'phone', 'gender', 'date_of_birth',
            'address', 'emergency_contact_name', 'emergency_contact_phone',
            'medical_notes', 'height', 'weight', 'blood_group',
            'joining_date', 'status', 'trainer_id',
        ];

        $filtered = array_intersect_key($data, array_flip($allowed));
        $member->update($filtered);

        return ['success', $member];
    }

    /** @return array{0: string, 1: Trainer|string} */
    private function handleTrainerUpdate(array $data): array
    {
        if (empty($data['id'])) {
            throw new \InvalidArgumentException('trainer_update requires id.');
        }

        $trainer = Trainer::findOrFail($data['id']);

        // ── Last-Write-Wins check ──────────────────────────────────────
        if (!$this->isClientUpdateFresh($data['client_updated_at'] ?? null, $trainer->updated_at)) {
            return [
                'conflict',
                'The trainer record was modified after this offline change was queued. Update rejected to prevent data loss.',
            ];
        }

        $allowed = [
            'name', 'email', 'phone', 'gender', 'date_of_birth',
            'specialization', 'bio', 'address', 'salary',
            'joining_date', 'is_active',
        ];

        $filtered = array_intersect_key($data, array_flip($allowed));

        // Validate gender if provided
        if (isset($filtered['gender']) && !in_array($filtered['gender'], ['male', 'female', 'other'])) {
            throw new \InvalidArgumentException('trainer_update: gender must be male, female, or other.');
        }

        // Validate salary if provided
        if (isset($filtered['salary']) && (!is_numeric($filtered['salary']) || $filtered['salary'] < 0)) {
            throw new \InvalidArgumentException('trainer_update: salary must be a non-negative number.');
        }

        // Validate email uniqueness if changing email
        if (!empty($filtered['email']) && Trainer::where('email', $filtered['email'])->where('id', '!=', $trainer->id)->exists()) {
            throw new \InvalidArgumentException('trainer_update: a trainer with that email already exists.');
        }

        if (isset($filtered['is_active'])) {
            $filtered['is_active'] = (bool) $filtered['is_active'];
        }

        $trainer->update($filtered);

        return ['success', $trainer];
    }

    /** @return array{0: string, 1: GymSetting|string} */
    private function handleSettingsUpdate(array $data): array
    {
        $settings = GymSetting::firstOrFail();

        // ── Last-Write-Wins check ──────────────────────────────────────
        if (!$this->isClientUpdateFresh($data['client_updated_at'] ?? null, $settings->updated_at)) {
            return [
                'conflict',
                'Gym settings were modified after this offline change was queued. Update rejected to prevent data loss.',
            ];
        }

        $allowed = [
            'gym_name', 'owner_name', 'contact_email', 'contact_phone',
            'address', 'country', 'city', 'currency', 'currency_symbol',
            'timezone', 'language', 'theme', 'date_format', 'time_format',
        ];

        $filtered = array_intersect_key($data, array_flip($allowed));

        if (empty($filtered)) {
            throw new \InvalidArgumentException('settings_update: no valid settings fields provided.');
        }

        // Validate language if present
        if (isset($filtered['language']) && !in_array($filtered['language'], ['en', 'ur', 'sd'])) {
            throw new \InvalidArgumentException('settings_update: language must be en, ur, or sd.');
        }

        // Validate theme if present
        if (isset($filtered['theme']) && !in_array($filtered['theme'], ['light', 'dark'])) {
            throw new \InvalidArgumentException('settings_update: theme must be light or dark.');
        }

        // Validate time_format if present
        if (isset($filtered['time_format']) && !in_array($filtered['time_format'], ['12h', '24h'])) {
            throw new \InvalidArgumentException('settings_update: time_format must be 12h or 24h.');
        }

        // Note: gym_logo cannot be updated via offline sync (requires file upload)
        $settings->update($filtered);

        return ['success', $settings];
    }

    // ─────────────────────────────────────────────────────────────────
    // DELETE Handlers
    // ─────────────────────────────────────────────────────────────────

    /**
     * Soft-delete a member offline.
     * Uses the same SoftDeletes mechanism as the online MemberController.
     * Does not apply LWW — delete is idempotent: if already deleted, return success.
     */
    private function handleMemberDelete(array $data): Member
    {
        if (empty($data['id'])) {
            throw new \InvalidArgumentException('member_delete requires id.');
        }

        $member = Member::find($data['id']);

        if (!$member) {
            // Already deleted or never existed — treat as success (idempotent)
            $ghost = new Member();
            $ghost->id = $data['id'];
            return $ghost;
        }

        $member->delete(); // SoftDelete via the Member model's SoftDeletes trait

        return $member;
    }
}
