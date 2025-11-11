<?php

namespace App\Services;

use App\Mail\EmailVerificationMail;
use App\Models\EmailVerification;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class EmailVerificationService
{
    /**
     * Generate and send verification code
     */
    public function sendVerificationCode(string $email, string $firstName, string $lastName): EmailVerification
    {
        // Delete any existing verification for this email
        EmailVerification::where('email', $email)->delete();

        // Generate 6-digit code
        $code = str_pad((string) random_int(100000, 999999), 6, '0', STR_PAD_LEFT);

        // Create verification record
        $verification = EmailVerification::create([
            'email' => $email,
            'code' => $code,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'expires_at' => Carbon::now()->addMinutes(10),
            'verified' => false,
        ]);

        // Send email
        Mail::to($email)->send(new EmailVerificationMail($code, $firstName, 10));

        return $verification;
    }

    /**
     * Verify the code
     */
    public function verifyCode(string $email, string $code): bool
    {
        $verification = EmailVerification::where('email', $email)
            ->where('verified', false)
            ->latest()
            ->first();

        if (!$verification) {
            return false;
        }

        if ($verification->isValid($code)) {
            $verification->update(['verified' => true]);
            return true;
        }

        return false;
    }

    /**
     * Check if email has valid verification
     */
    public function hasValidVerification(string $email): bool
    {
        return EmailVerification::where('email', $email)
            ->where('verified', true)
            ->where('expires_at', '>', Carbon::now())
            ->exists();
    }

    /**
     * Get verification by email
     */
    public function getVerification(string $email): ?EmailVerification
    {
        return EmailVerification::where('email', $email)
            ->latest()
            ->first();
    }

    /**
     * Clean up expired verifications
     */
    public function cleanupExpired(): int
    {
        return EmailVerification::where('expires_at', '<', Carbon::now()->subHours(24))
            ->delete();
    }

    /**
     * Resend verification code (with cooldown check)
     */
    public function canResend(string $email): bool
    {
        $lastVerification = EmailVerification::where('email', $email)
            ->latest()
            ->first();

        if (!$lastVerification) {
            return true;
        }

        // Allow resend after 60 seconds
        return $lastVerification->created_at->addSeconds(60)->isPast();
    }
}
