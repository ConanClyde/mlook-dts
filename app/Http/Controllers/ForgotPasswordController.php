<?php

namespace App\Http\Controllers;

use App\Mail\PasswordResetMail;
use App\Models\PasswordReset;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rules\Password;

class ForgotPasswordController extends Controller
{
    /**
     * Show the email entry form
     */
    public function showEmailForm()
    {
        // Redirect authenticated users to dashboard
        if (auth()->check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.passwords.email');
    }

    /**
     * Handle email submission and generate reset code
     */
    public function sendResetCode(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email', 'exists:users,email'],
        ]);

        // Delete any existing password reset for this email
        PasswordReset::where('email', $request->email)->delete();

        // Generate a 6-digit reset code
        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        // Create password reset record
        PasswordReset::create([
            'email' => $request->email,
            'code' => $code,
            'expires_at' => now()->addMinutes(15), // Code expires in 15 minutes
        ]);

        // Send email with the code
        try {
            Mail::to($request->email)->send(new PasswordResetMail($code, $request->email));
            session()->put('email', $request->email); // Use put() instead of flash() to persist

            return redirect()->route('password.code')->with('status', 'Reset code has been sent to your email!');
        } catch (\Exception $e) {
            // If email fails, show code on screen for development
            session()->put('reset_code', $code);
            session()->put('email', $request->email); // Use put() instead of flash() to persist

            return redirect()->route('password.code')->with('status', 'Email service unavailable. Your code is displayed below.');
        }
    }

    /**
     * Show the reset code entry form
     */
    public function showCodeForm()
    {
        if (! session()->has('email')) {
            return redirect()->route('password.request')->with('error', 'Please enter your email first.');
        }

        return view('auth.passwords.code');
    }

    /**
     * Validate the reset code
     */
    public function verifyCode(Request $request)
    {
        // Check if email exists in session first
        if (! session()->has('email')) {
            return redirect()->route('password.request')
                ->with('error', 'Session expired. Please enter your email again.');
        }

        $request->validate([
            'code' => ['required', 'string', 'size:6'],
        ]);

        $email = session('email');

        $passwordReset = PasswordReset::where('email', $email)
            ->where('code', $request->code)
            ->first();

        if (! $passwordReset) {
            return back()->withErrors(['code' => 'Invalid reset code.'])->withInput();
        }

        if ($passwordReset->isExpired()) {
            $passwordReset->delete();
            session()->forget('code_verified');

            return back()->withErrors(['code' => 'Reset code has expired. Please request a new one.'])->withInput();
        }

        // Store code verification in session
        session()->put('code_verified', true);

        return redirect()->route('password.reset');
    }

    /**
     * Resend reset code
     */
    public function resendCode()
    {
        $email = session('email');

        if (! $email) {
            return redirect()->route('password.request')
                ->with('error', 'Please enter your email first.');
        }

        // Delete existing password reset
        PasswordReset::where('email', $email)->delete();

        // Generate a new 6-digit reset code
        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        // Create new password reset record
        PasswordReset::create([
            'email' => $email,
            'code' => $code,
            'expires_at' => now()->addMinutes(15),
        ]);

        // Send email with the new code
        try {
            Mail::to($email)->send(new PasswordResetMail($code, $email));

            return back()->with('status', 'A new reset code has been sent to your email!');
        } catch (\Exception $e) {
            // If email fails, show code on screen for development
            session()->put('reset_code', $code);

            return back()->with('status', 'Email service unavailable. Your new code is displayed below.');
        }
    }

    /**
     * Show the new password form
     */
    public function showResetForm()
    {
        if (! session()->has('email') || ! session()->has('code_verified')) {
            return redirect()->route('password.request')->with('error', 'Please complete the previous steps first.');
        }

        return view('auth.passwords.reset');
    }

    /**
     * Handle password reset
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $email = session('email');

        // Find and delete the password reset record
        $passwordReset = PasswordReset::where('email', $email)->first();

        if (! $passwordReset || $passwordReset->isExpired()) {
            return redirect()->route('password.request')
                ->with('error', 'Reset session expired. Please start again.');
        }

        // Update user password
        $user = User::where('email', $email)->first();
        $user->update([
            'password' => Hash::make($request->password),
        ]);

        // Delete password reset record
        $passwordReset->delete();

        // Clear session data
        session()->forget(['email', 'code_verified', 'reset_code']);

        return redirect()->route('login')->with('status', 'Password has been reset successfully! You can now login with your new password.');
    }
}
