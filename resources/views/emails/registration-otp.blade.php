<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Verify your email</title>
</head>
<body style="font-family: Arial, sans-serif; color: #111827; line-height: 1.5;">
    <h2 style="margin-bottom: 8px;">Verify your email</h2>
    <p>Use this code to continue your registration for <strong>{{ $event->title }}</strong>:</p>
    <p style="font-size: 28px; font-weight: 700; letter-spacing: 6px; margin: 24px 0;">{{ $otp }}</p>
    <p>This code expires in {{ $expiresMinutes }} minutes.</p>
    <p>
        You can also resume your registration here:<br>
        <a href="{{ $resumeUrl }}">{{ $resumeUrl }}</a>
    </p>
    <p style="color: #6b7280; font-size: 13px;">If you did not start this registration, you can ignore this email.</p>
</body>
</html>
