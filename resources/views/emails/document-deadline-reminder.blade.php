<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document Deadline Reminder</title>
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
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: white;
            padding: 20px;
            border-radius: 8px 8px 0 0;
            margin: -30px -30px 30px -30px;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
        }
        .badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
            margin-top: 8px;
        }
        .badge-warning {
            background-color: #f59e0b;
            color: white;
        }
        .alert {
            background-color: #fef3c7;
            border: 2px solid #f59e0b;
            border-radius: 8px;
            padding: 16px;
            margin: 20px 0;
            text-align: center;
        }
        .alert-text {
            font-size: 18px;
            font-weight: 600;
            color: #92400e;
        }
        .info-row {
            margin: 15px 0;
            padding: 12px;
            background-color: #fffbeb;
            border-left: 4px solid #f59e0b;
            border-radius: 4px;
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
        .button {
            display: inline-block;
            padding: 12px 24px;
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
            margin-top: 20px;
        }
        .button:hover {
            opacity: 0.9;
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
        <div class="header">
            <h1>⏰ Deadline Approaching</h1>
            <span class="badge badge-warning">{{ $document->tracking_number }}</span>
        </div>

        <div class="alert">
            <div class="alert-text">⚠️ Only {{ $hoursRemaining }} hours remaining!</div>
        </div>

        <p>Hello,</p>
        <p>This is a reminder that the deadline for the following document is approaching soon.</p>

        <div class="info-row">
            <div class="info-label">Document Title</div>
            <div class="info-value">{{ $document->title }}</div>
        </div>

        <div class="info-row">
            <div class="info-label">Deadline</div>
            <div class="info-value">{{ $document->deadline->format('F d, Y h:i A') }}</div>
        </div>

        <div class="info-row">
            <div class="info-label">Time Remaining</div>
            <div class="info-value">{{ $hoursRemaining }} hours</div>
        </div>

        <div class="info-row">
            <div class="info-label">Urgency Level</div>
            <div class="info-value">{{ ucfirst($document->urgency_level) }}</div>
        </div>

        <p><strong>Please take action on this document as soon as possible to avoid missing the deadline.</strong></p>

        <a href="{{ $viewUrl }}" class="button">View Document Now</a>

        <div class="footer">
            <p>This is an automated reminder from the Document Tracking System.</p>
            <p>&copy; {{ date('Y') }} DMMMSU DTS. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
