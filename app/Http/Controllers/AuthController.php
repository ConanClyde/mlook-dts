<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegisterRequest;
use App\Models\Program;
use App\Services\AuditLogService;
use App\Services\EmailVerificationService;
use App\Services\UserRegistrationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLogin()
    {
        // Redirect authenticated users to dashboard
        if (auth()->check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.login');
    }

    public function login(Request $request, AuditLogService $auditLog)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            // Log successful login
            $auditLog->logUserLogin();

            // Handle AJAX requests
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Login successful',
                    'redirect' => route('dashboard'),
                ]);
            }

            return redirect()->route('dashboard');
        }

        // Log failed login attempt
        $auditLog->logFailedLogin($credentials['email']);

        // Handle AJAX requests
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => false,
                'message' => 'The provided credentials do not match our records.',
                'errors' => [
                    'email' => ['The provided credentials do not match our records.'],
                ],
            ], 422);
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    public function showRegister()
    {
        // Redirect authenticated users to dashboard
        if (auth()->check()) {
            return redirect()->route('dashboard');
        }

        $programs = Program::with('college')->orderBy('name')->get();
        $departments = \App\Models\Department::where('type', 'department')
            ->orderBy('name')
            ->get();

        return view('auth.register', compact('programs', 'departments'));
    }

    public function sendVerificationCode(Request $request, EmailVerificationService $verificationService)
    {
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
        ]);

        // Send verification code
        $verificationService->sendVerificationCode(
            $validated['email'],
            $validated['first_name'],
            $validated['last_name']
        );

        // Store data in session for later use
        session([
            'verification_email' => $validated['email'],
            'verification_first_name' => $validated['first_name'],
            'verification_last_name' => $validated['last_name'],
        ]);

        return redirect()->route('verify.show')
            ->with('success', 'Verification code sent to your email!');
    }

    public function showVerifyEmail()
    {
        if (!session('verification_email')) {
            return redirect()->route('register');
        }

        return view('auth.verify-email');
    }

    public function verifyCode(Request $request, EmailVerificationService $verificationService)
    {
        $request->validate([
            'code' => ['required', 'string', 'size:6'],
        ]);

        $email = session('verification_email');

        if (!$email) {
            return redirect()->route('register')
                ->withErrors(['code' => 'Session expired. Please start registration again.']);
        }

        if ($verificationService->verifyCode($email, $request->code)) {
            session(['email_verified' => true]);
            return redirect()->route('register.complete');
        }

        return back()->withErrors(['code' => 'Invalid or expired verification code.']);
    }

    public function resendVerificationCode(EmailVerificationService $verificationService)
    {
        $email = session('verification_email');
        $firstName = session('verification_first_name');
        $lastName = session('verification_last_name');

        if (!$email || !$firstName || !$lastName) {
            return response()->json([
                'success' => false,
                'message' => 'Session expired. Please start registration again.'
            ], 400);
        }

        if (!$verificationService->canResend($email)) {
            return response()->json([
                'success' => false,
                'message' => 'Please wait 60 seconds before requesting another code.'
            ], 429);
        }

        $verificationService->sendVerificationCode($email, $firstName, $lastName);

        session(['resend_cooldown' => true]);

        return response()->json([
            'success' => true,
            'message' => 'Verification code resent successfully!'
        ]);
    }

    public function showCompleteRegistration()
    {
        if (!session('email_verified')) {
            return redirect()->route('register');
        }

        $programs = Program::with('college')->orderBy('name')->get();
        $departments = \App\Models\Department::where('type', 'department')
            ->orderBy('name')
            ->get();

        return view('auth.register-complete', compact('programs', 'departments'));
    }

    public function register(RegisterRequest $request)
    {
        if (!session('email_verified')) {
            return redirect()->route('register')
                ->withErrors(['email' => 'Please verify your email first.']);
        }

        $data = array_merge($request->validated(), [
            'email' => session('verification_email'),
            'first_name' => session('verification_first_name'),
            'last_name' => session('verification_last_name'),
        ]);

        $service = new UserRegistrationService;
        $user = $service->register($data);

        // Clear verification session data
        session()->forget(['verification_email', 'verification_first_name', 'verification_last_name', 'email_verified']);

        Auth::login($user);

        return redirect()->route('dashboard');
    }

    public function logout(Request $request, AuditLogService $auditLog)
    {
        // Log logout before logging out
        $auditLog->logUserLogout();

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
