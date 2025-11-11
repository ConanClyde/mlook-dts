<?php

namespace App\Http\Controllers;

use App\Services\TwoFactorService;
use Illuminate\Http\Request;

class TwoFactorController extends Controller
{
    protected TwoFactorService $twoFactorService;

    public function __construct(TwoFactorService $twoFactorService)
    {
        $this->twoFactorService = $twoFactorService;
    }

    /**
     * Show 2FA settings page.
     */
    public function index()
    {
        $user = auth()->user();
        $recoveryCodes = $user->two_factor_enabled
            ? $this->twoFactorService->getRecoveryCodes($user)
            : [];

        return view('two-factor.index', compact('user', 'recoveryCodes'));
    }

    /**
     * Enable 2FA.
     */
    public function enable(Request $request)
    {
        $user = auth()->user();

        if ($user->two_factor_enabled) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => '2FA is already enabled.',
                ], 400);
            }

            return redirect()->route('admin.settings.index', ['tab' => 'security'])
                ->with('error', '2FA is already enabled.');
        }

        $data = $this->twoFactorService->enable($user);

        // If AJAX request, return JSON
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'secret' => $data['secret'],
                'qr_code' => $data['qr_code'],
                'recovery_codes' => $data['recovery_codes'],
            ]);
        }

        // Store setup data in session for page reload
        session([
            '2fa_setup' => [
                'secret' => $data['secret'],
                'qr_code' => $data['qr_code'],
                'recovery_codes' => $data['recovery_codes'],
            ],
        ]);

        return redirect()->route('admin.settings.index', ['tab' => 'security'])
            ->with('success', '2FA setup initiated. Please scan the QR code below.');
    }

    /**
     * Confirm 2FA setup.
     */
    public function confirm(Request $request)
    {
        $request->validate([
            'code' => 'required|string|size:6',
        ]);

        $user = auth()->user();

        if ($this->twoFactorService->confirm($user, $request->code)) {
            // Clear setup session data
            session()->forget('2fa_setup');

            // If AJAX request
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => '2FA has been enabled successfully!',
                ]);
            }

            return redirect()->route('admin.settings.index', ['tab' => 'security'])
                ->with('success', '2FA has been enabled successfully!');
        }

        // If AJAX request
        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid verification code. Please try again.',
            ], 422);
        }

        return redirect()->route('admin.settings.index', ['tab' => 'security'])
            ->with('error', 'Invalid verification code. Please try again.');
    }

    /**
     * Disable 2FA.
     */
    public function disable(Request $request)
    {
        $request->validate([
            'password' => 'required|string',
        ]);

        $user = auth()->user();

        if (! \Hash::check($request->password, $user->password)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid password.',
                ], 422);
            }

            return redirect()->route('admin.settings.index', ['tab' => 'security'])
                ->with('error', 'Invalid password.');
        }

        $this->twoFactorService->disable($user);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => '2FA has been disabled.',
            ]);
        }

        return redirect()->route('admin.settings.index', ['tab' => 'security'])
            ->with('success', '2FA has been disabled.');
    }

    /**
     * Show 2FA challenge page.
     */
    public function challenge()
    {
        return view('two-factor.challenge');
    }

    /**
     * Verify 2FA code.
     */
    public function verify(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
        ]);

        $user = auth()->user();

        if ($this->twoFactorService->verify($user, $request->code)) {
            session(['2fa_verified' => true]);

            return redirect()->intended(route('dashboard'));
        }

        return back()->with('error', 'Invalid code. Please try again.');
    }

    /**
     * Cancel 2FA setup.
     */
    public function cancel(Request $request)
    {
        session()->forget('2fa_setup');

        return redirect()->route('admin.settings.index', ['tab' => 'security']);
    }

    /**
     * Regenerate recovery codes.
     */
    public function regenerateRecoveryCodes(Request $request)
    {
        $request->validate([
            'password' => 'required|string',
        ]);

        $user = auth()->user();

        if (! \Hash::check($request->password, $user->password)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid password.',
                ], 422);
            }

            return redirect()->route('admin.settings.index', ['tab' => 'security'])
                ->with('error', 'Invalid password.');
        }

        $recoveryCodes = $this->twoFactorService->regenerateRecoveryCodes($user);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Recovery codes have been regenerated.',
            ]);
        }

        return redirect()->route('admin.settings.index', ['tab' => 'security'])
            ->with('success', 'Recovery codes have been regenerated.')
            ->with('recovery_codes', $recoveryCodes);
    }
}
