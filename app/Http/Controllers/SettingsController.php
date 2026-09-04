<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Attendance;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\FeePayment;
use App\Models\GymSetting;
use App\Models\Member;
use App\Models\MemberMembership;
use App\Models\MembershipPlan;
use App\Models\Trainer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class SettingsController extends Controller
{
    /**
     * Show the gym settings page.
     */
    public function index()
    {
        $settings = GymSetting::first();

        // If no settings exist yet, create a default record
        if (!$settings) {
            $settings = GymSetting::create([
                'gym_name'  => 'WarmUp Gym',
                'owner_name' => auth()->user()->name,
                'contact_email' => auth()->user()->email,
                'contact_phone' => '0000000000',
            ]);
        }

        return view('settings.index', compact('settings'));
    }

    /**
     * Update the gym settings.
     */
    public function update(Request $request)
    {
        $settings = GymSetting::firstOrFail();

        $validated = $request->validate([
            'gym_name'        => 'required|string|max:150',
            'gym_logo'        => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'owner_name'      => 'required|string|max:100',
            'contact_email'   => 'nullable|email|max:150',
            'contact_phone'   => 'nullable|string|max:20',
            'address'         => 'nullable|string',
            'country'         => 'nullable|string|max:100',
            'city'            => 'nullable|string|max:100',
            'currency'        => 'required|string|max:10',
            'currency_symbol' => 'required|string|max:10',
            'timezone'        => 'required|string|max:60',
            'language'        => 'required|string|max:10',
            'theme'           => 'required|string|max:20',
            'date_format'     => 'required|string|max:20',
            'time_format'     => 'required|string|max:10',
        ]);

        if ($request->hasFile('gym_logo')) {
            // Delete old logo if exists
            if ($settings->gym_logo) {
                Storage::disk('public')->delete($settings->gym_logo);
            }

            $path = $request->file('gym_logo')->store('logos', 'public');
            $validated['gym_logo'] = $path;
        }

        $settings->update($validated);

        // Log Activity
        ActivityLog::create([
            'user_id'      => auth()->id(),
            'action'       => 'Settings Updated',
            'description'  => "Updated gym settings.",
            'subject_type' => GymSetting::class,
            'subject_id'   => $settings->id,
            'ip_address'   => request()->ip(),
            'user_agent'   => request()->userAgent(),
        ]);

        return redirect()->route('settings.index')->with('success', __('Gym settings updated successfully.'));
    }

    /**
     * Download a full JSON backup of all gym data.
     */
    public function downloadBackup()
    {
        $backup = [
            'meta' => [
                'version'    => '1.0',
                'app'        => 'WarmUp Gym Management',
                'created_at' => now()->toIso8601String(),
                'created_by' => auth()->user()->name,
            ],
            'gym_settings'       => GymSetting::all()->toArray(),
            'membership_plans'   => MembershipPlan::all()->toArray(),
            'trainers'           => Trainer::all()->toArray(),
            'members'            => Member::withTrashed()->with([
                'memberships',
                'feePayments',
                'attendances',
            ])->get()->toArray(),
            'expense_categories' => ExpenseCategory::all()->toArray(),
            'expenses'           => Expense::all()->toArray(),
        ];

        $filename = 'warmup_backup_' . now()->format('Y-m-d_His') . '.json';
        $json     = json_encode($backup, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        // Log Activity
        ActivityLog::create([
            'user_id'      => auth()->id(),
            'action'       => 'Backup Downloaded',
            'description'  => "Downloaded gym data backup ({$filename}).",
            'subject_type' => GymSetting::class,
            'subject_id'   => GymSetting::first()?->id,
            'ip_address'   => request()->ip(),
            'user_agent'   => request()->userAgent(),
        ]);

        return response($json, 200, [
            'Content-Type'        => 'application/json',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    /**
     * Restore gym data from a previously downloaded JSON backup.
     */
    public function restoreBackup(Request $request)
    {
        $request->validate([
            'backup_file' => 'required|file|mimes:json|max:10240',
        ]);

        $contents = file_get_contents($request->file('backup_file')->getRealPath());
        $data     = json_decode($contents, true);

        // Validate that the JSON is a valid WarmUp backup
        if (json_last_error() !== JSON_ERROR_NONE || !isset($data['meta'], $data['members'])) {
            return back()->with('error', __('Invalid backup file. Please upload a valid WarmUp backup.'));
        }

        // Check meta signature
        if (!isset($data['meta']['app']) || $data['meta']['app'] !== 'WarmUp Gym Management') {
            return back()->with('error', __('Unrecognised backup format. This file was not generated by WarmUp.'));
        }

        try {
            DB::transaction(function () use ($data) {

                // ── Restore Gym Settings ───────────────────────────────────────
                if (!empty($data['gym_settings'])) {
                    $settingsRow = $data['gym_settings'][0];
                    unset($settingsRow['id'], $settingsRow['created_at'], $settingsRow['updated_at']);
                    GymSetting::updateOrCreate(['id' => 1], $settingsRow);
                }

                // ── Restore Membership Plans ───────────────────────────────────
                if (!empty($data['membership_plans'])) {
                    foreach ($data['membership_plans'] as $planData) {
                        unset($planData['created_at'], $planData['updated_at']);
                        $id = $planData['id'];
                        unset($planData['id']);
                        MembershipPlan::updateOrCreate(['id' => $id], $planData);
                    }
                }

                // ── Restore Trainers ───────────────────────────────────────────
                if (!empty($data['trainers'])) {
                    foreach ($data['trainers'] as $trainerData) {
                        unset($trainerData['created_at'], $trainerData['updated_at']);
                        $id = $trainerData['id'];
                        unset($trainerData['id']);
                        Trainer::updateOrCreate(['id' => $id], $trainerData);
                    }
                }

                // ── Restore Expense Categories ─────────────────────────────────
                if (!empty($data['expense_categories'])) {
                    foreach ($data['expense_categories'] as $catData) {
                        unset($catData['created_at'], $catData['updated_at']);
                        $id = $catData['id'];
                        unset($catData['id']);
                        ExpenseCategory::updateOrCreate(['id' => $id], $catData);
                    }
                }

                // ── Restore Members (with memberships, payments, attendance) ───
                if (!empty($data['members'])) {
                    foreach ($data['members'] as $memberData) {
                        // Extract nested relations
                        $memberships = $memberData['memberships'] ?? [];
                        $feePayments = $memberData['fee_payments']  ?? [];
                        $attendances = $memberData['attendances']   ?? [];

                        unset(
                            $memberData['memberships'],
                            $memberData['fee_payments'],
                            $memberData['attendances'],
                            $memberData['created_at'],
                            $memberData['updated_at'],
                            $memberData['deleted_at']
                        );
                        $memberId = $memberData['id'];
                        unset($memberData['id']);
                        $member = Member::updateOrCreate(['id' => $memberId], $memberData);

                        // Memberships
                        foreach ($memberships as $mm) {
                            unset($mm['created_at'], $mm['updated_at']);
                            $mmId = $mm['id'];
                            unset($mm['id']);
                            $mm['member_id'] = $member->id;
                            MemberMembership::updateOrCreate(['id' => $mmId], $mm);
                        }

                        // Fee Payments
                        foreach ($feePayments as $fp) {
                            unset($fp['created_at'], $fp['updated_at']);
                            $fpId = $fp['id'];
                            unset($fp['id']);
                            $fp['member_id'] = $member->id;
                            FeePayment::updateOrCreate(['id' => $fpId], $fp);
                        }

                        // Attendances
                        foreach ($attendances as $att) {
                            unset($att['created_at'], $att['updated_at']);
                            $attId = $att['id'];
                            unset($att['id']);
                            $att['member_id'] = $member->id;
                            Attendance::updateOrCreate(['id' => $attId], $att);
                        }
                    }
                }

                // ── Restore Expenses ───────────────────────────────────────────
                if (!empty($data['expenses'])) {
                    foreach ($data['expenses'] as $expData) {
                        unset($expData['created_at'], $expData['updated_at']);
                        $id = $expData['id'];
                        unset($expData['id']);
                        Expense::updateOrCreate(['id' => $id], $expData);
                    }
                }
            });

        } catch (\Throwable $e) {
            return back()->with('error', __('Restore failed: :message', ['message' => $e->getMessage()]));
        }

        // Log Activity
        ActivityLog::create([
            'user_id'      => auth()->id(),
            'action'       => 'Backup Restored',
            'description'  => "Restored gym data from backup file.",
            'subject_type' => GymSetting::class,
            'subject_id'   => GymSetting::first()?->id,
            'ip_address'   => request()->ip(),
            'user_agent'   => request()->userAgent(),
        ]);

        return redirect()->route('settings.index')
            ->with('success', __('Backup restored successfully. All data has been updated.'));
    }
}
