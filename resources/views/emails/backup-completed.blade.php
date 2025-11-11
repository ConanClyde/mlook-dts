<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Backup {{ $success ? 'Completed' : 'Failed' }}</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .container {
            background-color: #ffffff;
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .header {
            color: white;
            padding: 20px;
            border-radius: 8px 8px 0 0;
            margin: -30px -30px 30px -30px;
        }
        .header-success {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        }
        .header-error {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
        }
        .info-row {
            margin: 15px 0;
            padding: 12px;
            background-color: #f9fafb;
            border-left: 4px solid #10b981;
            border-radius: 4px;
        }
        .info-row-error {
            background-color: #fef2f2;
            border-left-color: #ef4444;
        }
        .info-label {
            font-weight: 600;
            color: #4b5563;
            font-size: 14px;
        }
        .info-value {
            color: #1f2937;
            margin-top: 4px;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            font-size: 12px;
            color: #6b7280;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header {{ $success ? 'header-success' : 'header-error' }}">
            <h1>{{ $success ? '✅ Backup Completed Successfully' : '❌ Backup Failed' }}</h1>
        </div>

        @if($success)
            <p>Hello Admin,</p>
            <p>The system backup has been completed successfully.</p>

            <div class="info-row">
                <div class="info-label">Backup Name</div>
                <div class="info-value">{{ $backupName }}</div>
            </div>

            <div class="info-row">
                <div class="info-label">Backup Type</div>
                <div class="info-value">{{ ucfirst($backupType) }}</div>
            </div>

            <div class="info-row">
                <div class="info-label">Backup Size</div>
                <div class="info-value">{{ $backupSize }}</div>
            </div>

            <div class="info-row">
                <div class="info-label">Completed At</div>
                <div class="info-value">{{ now()->format('F d, Y h:i A') }}</div>
            </div>

            <p>The backup file has been stored securely and is ready for download from the admin panel.</p>
        @else
            <p>Hello Admin,</p>
            <p><strong>The system backup has failed.</strong> Please check the error details below and take necessary action.</p>

            <div class="info-row info-row-error">
                <div class="info-label">Backup Type</div>
                <div class="info-value">{{ ucfirst($backupType) }}</div>
            </div>

            <div class="info-row info-row-error">
                <div class="info-label">Failed At</div>
                <div class="info-value">{{ now()->format('F d, Y h:i A') }}</div>
            </div>

            @if($errorMessage)
            <div class="info-row info-row-error">
                <div class="info-label">Error Message</div>
                <div class="info-value">{{ $errorMessage }}</div>
            </div>
            @endif

            <p>Please investigate the issue and attempt the backup again. If the problem persists, contact your system administrator.</p>
        @endif

        <div class="footer">
            <p>This is an automated message from the Document Tracking System.</p>
            <p>&copy; {{ date('Y') }} DMMMSU DTS. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
