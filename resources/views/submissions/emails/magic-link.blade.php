<!DOCTYPE html>
<html lang="en">
<body style="margin:0;background:#f8fafc;font-family:Arial,sans-serif;color:#0f172a">
<table role="presentation" width="100%" cellspacing="0" cellpadding="0"><tr><td align="center" style="padding:40px 16px">
<table role="presentation" width="100%" style="max-width:560px;background:white;border:1px solid #e2e8f0;border-radius:20px" cellspacing="0" cellpadding="0">
<tr><td style="padding:36px">
<p style="margin:0;color:#4f46e5;font-size:13px;font-weight:bold;text-transform:uppercase">{{ $event->title }}</p>
<h1 style="margin:14px 0 10px;font-size:28px">Your secure sign-in link</h1>
<p style="margin:0 0 24px;color:#475569;line-height:1.6">Use the button below to open your speaker and abstract portal. This link expires in 20 minutes and can only be used once.</p>
<a href="{{ $url }}" style="display:inline-block;background:#4f46e5;color:white;text-decoration:none;font-weight:bold;padding:14px 22px;border-radius:12px">Open secure portal</a>
<p style="margin:24px 0 0;color:#64748b;font-size:12px;line-height:1.5">If you did not request this email, you can safely ignore it.</p>
</td></tr></table>
</td></tr></table>
</body>
</html>
