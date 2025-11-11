<?php

return [

    /*
    |--------------------------------------------------------------------------
    | IP Whitelist
    |--------------------------------------------------------------------------
    |
    | List of IP addresses that are allowed to access protected routes.
    | Leave empty to disable IP whitelisting.
    |
    | Example: ['127.0.0.1', '192.168.1.100', '10.0.0.0/8']
    |
    */

    'ip_whitelist' => env('IP_WHITELIST') ? explode(',', env('IP_WHITELIST')) : [],

    /*
    |--------------------------------------------------------------------------
    | IP Blacklist
    |--------------------------------------------------------------------------
    |
    | List of IP addresses that are blocked from accessing the system.
    |
    */

    'ip_blacklist' => env('IP_BLACKLIST') ? explode(',', env('IP_BLACKLIST')) : [],

    /*
    |--------------------------------------------------------------------------
    | Document Encryption
    |--------------------------------------------------------------------------
    |
    | Enable encryption for sensitive documents.
    |
    */

    'document_encryption' => [
        'enabled' => env('DOCUMENT_ENCRYPTION_ENABLED', false),
        'auto_encrypt_sensitive' => env('AUTO_ENCRYPT_SENSITIVE', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Watermarking
    |--------------------------------------------------------------------------
    |
    | Enable watermarking for documents.
    |
    */

    'watermarking' => [
        'enabled' => env('WATERMARKING_ENABLED', false),
        'text' => env('WATERMARK_TEXT', 'CONFIDENTIAL'),
        'auto_watermark' => env('AUTO_WATERMARK', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Audit Logging
    |--------------------------------------------------------------------------
    |
    | Enable or disable audit logging for sensitive operations.
    |
    */

    'audit_logging' => [
        'enabled' => env('AUDIT_LOGGING_ENABLED', true),
        'retention_days' => env('AUDIT_RETENTION_DAYS', 90),
    ],

    /*
    |--------------------------------------------------------------------------
    | Password Policy
    |--------------------------------------------------------------------------
    |
    | Configure password requirements for user accounts.
    |
    */

    'password_min_length' => env('PASSWORD_MIN_LENGTH', 8),
    'password_require_uppercase' => env('PASSWORD_REQUIRE_UPPERCASE', true),
    'password_require_lowercase' => env('PASSWORD_REQUIRE_LOWERCASE', true),
    'password_require_numbers' => env('PASSWORD_REQUIRE_NUMBERS', true),
    'password_require_special' => env('PASSWORD_REQUIRE_SPECIAL', false),
    'password_expiry_days' => env('PASSWORD_EXPIRY_DAYS', 90),

    /*
    |--------------------------------------------------------------------------
    | Session Security
    |--------------------------------------------------------------------------
    |
    | Configure session security settings.
    |
    */

    'session_timeout' => env('SESSION_TIMEOUT', 120), // minutes
    'max_concurrent_sessions' => env('MAX_CONCURRENT_SESSIONS', 3),
    'force_logout_on_password_change' => env('FORCE_LOGOUT_ON_PASSWORD_CHANGE', true),

    /*
    |--------------------------------------------------------------------------
    | Login Security
    |--------------------------------------------------------------------------
    |
    | Configure login attempt limits and lockout settings.
    |
    */

    'max_login_attempts' => env('MAX_LOGIN_ATTEMPTS', 5),
    'lockout_duration' => env('LOCKOUT_DURATION', 30), // minutes
    'suspicious_activity_threshold' => env('SUSPICIOUS_ACTIVITY_THRESHOLD', 5),

];
