<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SecurityService
{
    /**
     * Check if IP is whitelisted.
     */
    public function isIpWhitelisted(string $ip): bool
    {
        $whitelist = config('security.ip_whitelist', []);

        if (empty($whitelist)) {
            return true; // No whitelist configured
        }

        return in_array($ip, $whitelist);
    }

    /**
     * Log suspicious activity.
     */
    public function logSuspiciousActivity(string $type, array $data): void
    {
        Log::channel('security')->warning('Suspicious Activity Detected', [
            'type' => $type,
            'data' => $data,
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'timestamp' => now()->toIso8601String(),
        ]);

        // Store in cache for monitoring
        $key = "suspicious_activity:{$type}:".request()->ip();
        $count = Cache::get($key, 0);
        Cache::put($key, $count + 1, now()->addHours(24));

        // Alert if threshold exceeded
        if ($count + 1 >= 5) {
            $this->alertAdmins($type, $data);
        }
    }

    /**
     * Alert administrators of security issues.
     */
    private function alertAdmins(string $type, array $data): void
    {
        $admins = User::where('usertype', 'admin')->get();

        foreach ($admins as $admin) {
            // Send notification (implement as needed)
            Log::channel('security')->critical('Security Alert Sent to Admin', [
                'admin_id' => $admin->id,
                'type' => $type,
            ]);
        }
    }

    /**
     * Check password strength.
     */
    public function checkPasswordStrength(string $password): array
    {
        $score = 0;
        $feedback = [];

        // Length check
        if (strlen($password) >= 8) {
            $score += 1;
        } else {
            $feedback[] = 'Password should be at least 8 characters';
        }

        // Uppercase check
        if (preg_match('/[A-Z]/', $password)) {
            $score += 1;
        } else {
            $feedback[] = 'Add uppercase letters';
        }

        // Lowercase check
        if (preg_match('/[a-z]/', $password)) {
            $score += 1;
        } else {
            $feedback[] = 'Add lowercase letters';
        }

        // Number check
        if (preg_match('/[0-9]/', $password)) {
            $score += 1;
        } else {
            $feedback[] = 'Add numbers';
        }

        // Special character check
        if (preg_match('/[^A-Za-z0-9]/', $password)) {
            $score += 1;
        } else {
            $feedback[] = 'Add special characters';
        }

        $strength = match (true) {
            $score >= 5 => 'strong',
            $score >= 3 => 'medium',
            default => 'weak',
        };

        return [
            'score' => $score,
            'strength' => $strength,
            'feedback' => $feedback,
        ];
    }

    /**
     * Enforce password policy.
     */
    public function enforcePasswordPolicy(string $password): bool
    {
        $minLength = config('security.password_min_length', 8);
        $requireUppercase = config('security.password_require_uppercase', true);
        $requireLowercase = config('security.password_require_lowercase', true);
        $requireNumbers = config('security.password_require_numbers', true);
        $requireSpecial = config('security.password_require_special', true);

        if (strlen($password) < $minLength) {
            return false;
        }

        if ($requireUppercase && ! preg_match('/[A-Z]/', $password)) {
            return false;
        }

        if ($requireLowercase && ! preg_match('/[a-z]/', $password)) {
            return false;
        }

        if ($requireNumbers && ! preg_match('/[0-9]/', $password)) {
            return false;
        }

        if ($requireSpecial && ! preg_match('/[^A-Za-z0-9]/', $password)) {
            return false;
        }

        return true;
    }

    /**
     * Track login attempts.
     */
    public function trackLoginAttempt(string $email, bool $successful): void
    {
        $key = "login_attempts:{$email}";

        if ($successful) {
            Cache::forget($key);
        } else {
            $attempts = Cache::get($key, 0);
            Cache::put($key, $attempts + 1, now()->addMinutes(30));

            if ($attempts + 1 >= 5) {
                $this->logSuspiciousActivity('multiple_failed_logins', [
                    'email' => $email,
                    'attempts' => $attempts + 1,
                ]);
            }
        }
    }

    /**
     * Check if account is locked.
     */
    public function isAccountLocked(string $email): bool
    {
        $key = "login_attempts:{$email}";
        $attempts = Cache::get($key, 0);

        return $attempts >= 5;
    }

    /**
     * Get active sessions for user.
     */
    public function getActiveSessions(User $user): array
    {
        // This would require session tracking implementation
        // For now, return placeholder
        return [];
    }

    /**
     * Revoke all sessions except current.
     */
    public function revokeOtherSessions(User $user): int
    {
        // Revoke all Sanctum tokens except current
        $currentToken = $user->currentAccessToken();

        if ($currentToken) {
            return $user->tokens()
                ->where('id', '!=', $currentToken->id)
                ->delete();
        }

        return $user->tokens()->delete();
    }

    /**
     * Encrypt sensitive data.
     */
    public function encryptData(string $data): string
    {
        return encrypt($data);
    }

    /**
     * Decrypt sensitive data.
     */
    public function decryptData(string $encryptedData): string
    {
        return decrypt($encryptedData);
    }

    /**
     * Generate secure token.
     */
    public function generateSecureToken(int $length = 32): string
    {
        return bin2hex(random_bytes($length));
    }

    /**
     * Validate CSRF token.
     */
    public function validateCsrfToken(string $token): bool
    {
        return hash_equals(session()->token(), $token);
    }
}
