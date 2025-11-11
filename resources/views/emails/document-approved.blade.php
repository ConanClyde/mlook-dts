<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document Approved</title>
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
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
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
        .badge-success {
            background-color: #10b981;
            color: white;
        }
        .info-row {
            margin: 15px 0;
            padding: 12px;
            background-color: #f0fdf4;
            border-left: 4px solid #10b981;
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
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
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
            <h1>✅ Document Approved</h1>
            <span class="badge badge-success">{{ $document->tracking_number }}</span>
        </div>

        <p>Hello,</p>
        <p>Great news! Your document has been <strong>approved</strong> by <strong>{{ $approvedBy->full_name }}</strong>.</p>

        <div class="info-row">
            <div class="info-label">Document Title</div>
            <div class="info-value">{{ $document->title }}</div>
        </div>

        <div class="info-row">
            <div class="info-label">Approved By</div>
            <div class="info-value">{{ $approvedBy->full_name }} ({{ $approvedBy->usertype }})</div>
        </div>

        <div class="info-row">
            <div class="info-label">Approved On</div>
            <div class="info-value">{{ $document->approved_at->format('F d, Y h:i A') }}</div>
        </div>

        @if($remarks)
        <div class="info-row">
            <div class="info-label">Approval Remarks</div>
            <div class="info-value">{{ $remarks }}</div>
        </div>
        @endif

        <a href="{{ $viewUrl }}" class="button">View Document</a>

        <div class="footer">
            <p>This is an automated message from the Document Tracking System.</p>
            <p>&copy; {{ date('Y') }} DMMMSU DTS. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
