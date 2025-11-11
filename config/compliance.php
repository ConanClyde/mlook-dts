<?php

return [

    /*
    |--------------------------------------------------------------------------
    | GDPR Compliance
    |--------------------------------------------------------------------------
    |
    | Enable GDPR compliance features including data export and deletion.
    |
    */

    'gdpr_enabled' => env('GDPR_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Data Retention Policy
    |--------------------------------------------------------------------------
    |
    | Configure how long documents and data should be retained.
    |
    */

    'data_retention_enabled' => env('DATA_RETENTION_ENABLED', true),
    'document_retention_days' => env('DOCUMENT_RETENTION_DAYS', 365 * 7), // 7 years
    'auto_archive_days' => env('AUTO_ARCHIVE_DAYS', 365), // 1 year
    'audit_log_retention_days' => env('AUDIT_LOG_RETENTION_DAYS', 90),

    /*
    |--------------------------------------------------------------------------
    | Data Export
    |--------------------------------------------------------------------------
    |
    | Settings for GDPR data export requests.
    |
    */

    'export_format' => env('DATA_EXPORT_FORMAT', 'json'), // json, csv, pdf
    'export_includes_documents' => env('EXPORT_INCLUDES_DOCUMENTS', true),
    'export_includes_audit_logs' => env('EXPORT_INCLUDES_AUDIT_LOGS', true),

    /*
    |--------------------------------------------------------------------------
    | Right to be Forgotten
    |--------------------------------------------------------------------------
    |
    | Configure data deletion policies.
    |
    */

    'allow_user_deletion' => env('ALLOW_USER_DELETION', true),
    'anonymize_instead_of_delete' => env('ANONYMIZE_INSTEAD_OF_DELETE', true),
    'deletion_grace_period_days' => env('DELETION_GRACE_PERIOD_DAYS', 30),

    /*
    |--------------------------------------------------------------------------
    | Consent Management
    |--------------------------------------------------------------------------
    |
    | Track user consent for data processing.
    |
    */

    'require_consent' => env('REQUIRE_CONSENT', true),
    'consent_version' => env('CONSENT_VERSION', '1.0'),

];
