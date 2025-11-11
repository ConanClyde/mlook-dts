<?php

namespace App\Services;

use App\Models\User;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use PragmaRX\Google2FA\Google2FA;

class TwoFactorService
{
    protected Google2FA $google2fa;

    public function __construct()
    {
        $this->google2fa = new Google2FA;
    }

    /**
     * Enable 2FA for a user.
     */
    public function enable(User $user): array
    {
        $secret = $this->google2fa->generateSecretKey();
        $recoveryCodes = $this->generateRecoveryCodes();

        $user->update([
            'two_factor_secret' => encrypt($secret),
            'two_factor_recovery_codes' => encrypt(json_encode($recoveryCodes)),
            'two_factor_enabled' => false, // Will be enabled after confirmation
        ]);

        return [
            'secret' => $secret,
            'qr_code' => $this->generateQrCode($user, $secret),
            'recovery_codes' => $recoveryCodes,
        ];
    }

    /**
     * Confirm 2FA setup.
     */
    public function confirm(User $user, string $code): bool
    {
        if (! $user->two_factor_secret) {
            return false;
        }

        $secret = decrypt($user->two_factor_secret);

        // Allow 2 windows (60 seconds) of time drift
        if ($this->google2fa->verifyKey($secret, $code, 2)) {
            $user->update([
                'two_factor_enabled' => true,
                'two_factor_confirmed_at' => now(),
            ]);

            return true;
        }

        return false;
    }

    /**
     * Disable 2FA for a user.
     */
    public function disable(User $user): void
    {
        $user->update([
            'two_factor_enabled' => false,
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ]);
    }

    /**
     * Verify a 2FA code.
     */
    public function verify(User $user, string $code): bool
    {
        if (! $user->two_factor_enabled || ! $user->two_factor_secret) {
            return false;
        }

        $secret = decrypt($user->two_factor_secret);

        // Check if it's a valid TOTP code
        if ($this->google2fa->verifyKey($secret, $code, 2)) {
            return true;
        }

        // Check if it's a recovery code
        return $this->verifyRecoveryCode($user, $code);
    }

    /**
     * Verify a recovery code.
     */
    protected function verifyRecoveryCode(User $user, string $code): bool
    {
        if (! $user->two_factor_recovery_codes) {
            return false;
        }

        $recoveryCodes = json_decode(decrypt($user->two_factor_recovery_codes), true);

        $index = array_search($code, $recoveryCodes);

        if ($index !== false) {
            // Remove used recovery code
            unset($recoveryCodes[$index]);
            $user->update([
                'two_factor_recovery_codes' => encrypt(json_encode(array_values($recoveryCodes))),
            ]);

            return true;
        }

        return false;
    }

    /**
     * Generate recovery codes.
     */
    protected function generateRecoveryCodes(int $count = 8): array
    {
        $codes = [];
        for ($i = 0; $i < $count; $i++) {
            $codes[] = strtoupper(substr(bin2hex(random_bytes(5)), 0, 10));
        }

        return $codes;
    }

    /**
     * Regenerate recovery codes.
     */
    public function regenerateRecoveryCodes(User $user): array
    {
        $recoveryCodes = $this->generateRecoveryCodes();

        $user->update([
            'two_factor_recovery_codes' => encrypt(json_encode($recoveryCodes)),
        ]);

        return $recoveryCodes;
    }

    /**
     * Get recovery codes for a user.
     */
    public function getRecoveryCodes(User $user): array
    {
        if (! $user->two_factor_recovery_codes) {
            return [];
        }

        return json_decode(decrypt($user->two_factor_recovery_codes), true);
    }

    /**
     * Generate QR code for 2FA setup.
     */
    protected function generateQrCode(User $user, string $secret): string
    {
        $qrCodeUrl = $this->google2fa->getQRCodeUrl(
            config('app.name'),
            $user->email,
            $secret
        );

        $renderer = new ImageRenderer(
            new RendererStyle(200),
            new SvgImageBackEnd
        );

        $writer = new Writer($renderer);

        return $writer->writeString($qrCodeUrl);
    }
}
