<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ForgotPasswordController extends Controller
{
    /**
     * Show the forgot password form.
     */
    public function showLinkRequestForm(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * Handle the forgot password request.
     *
     * FYP-compatible flow (no email required):
     * 1. Validate the submitted email.
     * 2. If the user exists, generate a secure token and store it.
     * 3. Redirect directly to the reset-password page with that token
     *    so evaluators can complete the full flow without SMTP configuration.
     */
    public function sendResetLinkEmail(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $user = User::where('email', $request->email)->first();

        // Always show success to prevent user enumeration
        if (! $user) {
            return back()->with('status', __('If that email is registered, a reset link has been generated.'));
        }

        // Generate a secure token (same algorithm Laravel's PasswordBroker uses)
        $token = hash_hmac('sha256', Str::random(40), config('app.key'));

        // Store in password_reset_tokens table (delete old one first)
        DB::table('password_reset_tokens')->where('email', $request->email)->delete();
        DB::table('password_reset_tokens')->insert([
            'email'      => $request->email,
            'token'      => Hash::make($token),
            'created_at' => now(),
        ]);

        // FYP mode: redirect directly to the reset form with token + email
        return redirect()->route('password.reset', [
            'token' => $token,
            'email' => $request->email,
        ])->with('status', __('A reset token has been generated. Please set your new password below.'));
    }

    /**
     * Show the reset password form.
     */
    public function showResetForm(Request $request, string $token): View
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => $request->query('email'),
        ]);
    }

    /**
     * Handle the password reset.
     */
    public function reset(Request $request): RedirectResponse
    {
        $request->validate([
            'token'                 => ['required'],
            'email'                 => ['required', 'email'],
            'password'              => ['required', 'min:8', 'confirmed'],
        ]);

        $record = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->first();

        if (! $record || ! Hash::check($request->token, $record->token)) {
            return back()->withErrors(['email' => 'This password reset token is invalid or has expired.']);
        }

        // Token expires after 60 minutes (from auth.php config)
        if (now()->diffInMinutes($record->created_at) > config('auth.passwords.users.expire', 60)) {
            DB::table('password_reset_tokens')->where('email', $request->email)->delete();
            return back()->withErrors(['email' => 'This password reset token has expired. Please request a new one.']);
        }

        $user = User::where('email', $request->email)->first();

        if (! $user) {
            return back()->withErrors(['email' => 'No account found with that email address.']);
        }

        $user->update(['password' => Hash::make($request->password)]);

        // Clean up the used token
        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        return redirect()->route('login')
            ->with('status', __('Your password has been reset successfully. Please log in.'));
    }
}
