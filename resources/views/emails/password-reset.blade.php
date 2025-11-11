<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Password Reset Code - MLOOK</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Satoshi', -apple-system, BlinkMacSystemFont, 'Segoe UI', Arial, sans-serif;
            background-color: #f9fafb;
            color: #111827;
            line-height: 1.6;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }
        .email-wrapper {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
        }
        .header {
            background-color: #111827;
            padding: 48px 40px 40px;
            text-align: center;
        }
        .logo {
            color: #ffffff;
            font-size: 24px;
            font-weight: 700;
            letter-spacing: -0.5px;
        }
        .content {
            padding: 48px 40px;
        }
        .title {
            font-size: 24px;
            font-weight: 600;
            color: #111827;
            margin-bottom: 16px;
            letter-spacing: -0.3px;
        }
        .text {
            font-size: 16px;
            color: #4b5563;
            margin-bottom: 24px;
            line-height: 1.7;
        }
        .email-address {
            color: #111827;
            font-weight: 500;
        }
        .code-section {
            margin: 40px 0;
        }
        .code-label {
            font-size: 13px;
            font-weight: 500;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 12px;
            text-align: center;
        }
        .code-box {
            background-color: #fff7ed;
            border: 1px solid #fed7aa;
            border-radius: 8px;
            padding: 24px;
            text-align: center;
        }
        .code {
            font-size: 32px;
            font-weight: 700;
            color: #9a3412;
            letter-spacing: 8px;
            font-family: 'Courier New', 'Monaco', monospace;
            line-height: 1.2;
        }
        .expiry {
            font-size: 14px;
            color: #6b7280;
            margin-top: 24px;
            text-align: center;
        }
        .divider {
            height: 1px;
            background-color: #e5e7eb;
            margin: 40px 0;
        }
        .security-notice {
            background-color: #fef2f2;
            border-left: 3px solid #ef4444;
            padding: 16px 20px;
            margin: 32px 0;
            border-radius: 4px;
        }
        .security-notice-title {
            font-size: 14px;
            font-weight: 600;
            color: #991b1b;
            margin-bottom: 8px;
        }
        .security-notice-text {
            font-size: 14px;
            color: #7f1d1d;
            line-height: 1.6;
        }
        .footer {
            background-color: #f9fafb;
            padding: 32px 40px;
            border-top: 1px solid #e5e7eb;
        }
        .footer-text {
            font-size: 14px;
            color: #6b7280;
            text-align: center;
            line-height: 1.6;
        }
        .footer-brand {
            font-weight: 600;
            color: #111827;
        }
        .footer-subtext {
            font-size: 12px;
            color: #9ca3af;
            margin-top: 16px;
        }
        @media only screen and (max-width: 600px) {
            .email-wrapper {
                width: 100% !important;
            }
            .header,
            .content,
            .footer {
                padding-left: 24px !important;
                padding-right: 24px !important;
            }
            .code {
                font-size: 28px !important;
                letter-spacing: 6px !important;
            }
        }
    </style>
</head>
<body>
    <div style="background-color: #f9fafb; padding: 40px 20px;">
        <div class="email-wrapper">
            <!-- Header -->
            <div class="header">
                <div class="logo">MLOOK</div>
            </div>

            <!-- Content -->
            <div class="content">
                <h1 class="title">Password Reset Request</h1>
                
                <p class="text">
                    Hello,
                </p>
                
                <p class="text">
                    You requested to reset your password for your MLOOK account. Use the verification code below to complete the reset process.
                </p>

                <!-- Code Section -->
                <div class="code-section">
                    <div class="code-label">Verification Code</div>
                    <div class="code-box">
                        <div class="code">{{ $code }}</div>
                    </div>
                    <p class="expiry">
                        This code expires in 15 minutes
                    </p>
                </div>

                <div class="divider"></div>

                <p class="text">
                    Enter this code on the password reset page to create a new password. If you did not request a password reset, you can safely ignore this email.
                </p>

                <!-- Security Notice -->
                <div class="security-notice">
                    <div class="security-notice-title">Security Notice</div>
                    <div class="security-notice-text">
                        If you did not request this password reset, please ignore this email. Your account remains secure and no changes have been made.
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="footer">
                <p class="footer-text">
                    <span class="footer-brand">MLOOK</span> - MLUC Document Tracking System
                </p>
                <p class="footer-text" style="margin-top: 8px;">
                    Don Mariano Marcos Memorial State University<br>
                    Mid La Union Campus
                </p>
                <p class="footer-subtext">
                    This is an automated message. Please do not reply to this email.
                </p>
            </div>
        </div>
    </div>
</body>
</html>
