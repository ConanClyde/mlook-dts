<?php

namespace Tests\Unit;

use App\Services\SecurityService;
use Tests\TestCase;

class SecurityServiceTest extends TestCase
{
    protected SecurityService $securityService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->securityService = new SecurityService;
    }

    public function test_password_strength_check_weak(): void
    {
        $result = $this->securityService->checkPasswordStrength('pass');

        $this->assertEquals('weak', $result['strength']);
        $this->assertGreaterThan(0, count($result['feedback']));
    }

    public function test_password_strength_check_medium(): void
    {
        $result = $this->securityService->checkPasswordStrength('Password123');

        $this->assertEquals('medium', $result['strength']);
    }

    public function test_password_strength_check_strong(): void
    {
        $result = $this->securityService->checkPasswordStrength('P@ssw0rd123!');

        $this->assertEquals('strong', $result['strength']);
        $this->assertEquals(5, $result['score']);
    }

    public function test_password_policy_enforcement(): void
    {
        config(['security.password_min_length' => 8]);
        config(['security.password_require_uppercase' => true]);
        config(['security.password_require_numbers' => true]);

        $this->assertTrue($this->securityService->enforcePasswordPolicy('Password123'));
        $this->assertFalse($this->securityService->enforcePasswordPolicy('pass'));
        $this->assertFalse($this->securityService->enforcePasswordPolicy('password'));
    }

    public function test_data_encryption_and_decryption(): void
    {
        $data = 'Sensitive Information';
        $encrypted = $this->securityService->encryptData($data);
        $decrypted = $this->securityService->decryptData($encrypted);

        $this->assertNotEquals($data, $encrypted);
        $this->assertEquals($data, $decrypted);
    }

    public function test_secure_token_generation(): void
    {
        $token1 = $this->securityService->generateSecureToken();
        $token2 = $this->securityService->generateSecureToken();

        $this->assertEquals(64, strlen($token1)); // 32 bytes = 64 hex chars
        $this->assertNotEquals($token1, $token2);
    }
}
