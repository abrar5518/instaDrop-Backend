<!doctype html>
<html lang="en">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>{{ $title }}</title></head>
<body style="margin:0;padding:0;background-color:#eef2f6;color:#14243a;font-family:Arial,Helvetica,sans-serif;">
<div style="display:none;max-height:0;overflow:hidden;mso-hide:all;">{{ $intro }}</div>
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#eef2f6;"><tr><td align="center" style="padding:32px 12px;">
<table role="presentation" width="600" cellpadding="0" cellspacing="0" style="width:100%;max-width:600px;background-color:#ffffff;border:1px solid #dfe5ec;border-radius:16px;overflow:hidden;">
<tr><td style="padding:28px 28px;background-color:#051329;border-bottom:4px solid #c6ff00;">
<a href="{{ $websiteUrl }}" style="text-decoration:none;font-size:30px;font-weight:800;color:#ffffff;">Insta<span style="color:#c6ff00;">Drop</span></a>
<p style="margin:8px 0 0;color:#a9bad0;font-size:10px;letter-spacing:2px;">UK SAME-DAY COURIER</p>
</td></tr>
<tr><td style="padding:30px 28px 18px;">
<p style="margin:0 0 12px;color:#0066ff;font-size:11px;font-weight:bold;letter-spacing:1px;text-transform:uppercase;">{{ $eyebrow }}</p>
<h1 style="margin:0 0 16px;font-size:28px;line-height:1.25;color:#051329;">{{ $title }}</h1>
<p style="margin:0;color:#526176;font-size:15px;line-height:1.7;">{{ $intro }}</p>
</td></tr>
@if($amount)
<tr><td style="padding:6px 28px 20px;"><table role="presentation" width="100%" cellpadding="0" cellspacing="0"><tr><td style="padding:22px;background-color:#051329;border-radius:10px;">
<p style="margin:0 0 8px;color:#b8c7db;font-size:12px;">{{ $amountLabel }}</p>
<p style="margin:0;font-size:32px;font-weight:bold;color:#c6ff00;">{{ $amount }}</p>
</td></tr></table></td></tr>
@endif
<tr><td style="padding:4px 28px 20px;"><table width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;table-layout:fixed;">
@foreach($details as $label => $value)
@if($value !== null && $value !== '')
<tr><th scope="row" width="38%" align="left" valign="top" style="padding:13px 12px 13px 0;border-bottom:1px solid #e7ecf2;color:#66758a;font-size:12px;font-weight:normal;line-height:1.6;">{{ $label }}</th><td valign="top" style="padding:13px 0;border-bottom:1px solid #e7ecf2;color:#14243a;font-size:13px;font-weight:bold;line-height:1.6;word-wrap:break-word;overflow-wrap:anywhere;">{{ $value }}</td></tr>
@endif
@endforeach
</table></td></tr>
@if($note)
<tr><td style="padding:0 28px 22px;"><div style="padding:16px;background-color:#f3f6fa;border-left:3px solid #0066ff;color:#526176;font-size:13px;line-height:1.7;white-space:pre-line;overflow-wrap:anywhere;">{{ $note }}</div></td></tr>
@endif
@if($actionUrl && $actionLabel)
<tr><td style="padding:4px 28px 28px;">
<table role="presentation" cellpadding="0" cellspacing="0"><tr><td bgcolor="#c6ff00" style="border-radius:7px;mso-padding-alt:16px 24px;"><a href="{{ $actionUrl }}" style="display:inline-block;padding:16px 24px;color:#051329;font-size:14px;font-weight:bold;text-decoration:none;">{{ $actionLabel }} &rarr;</a></td></tr></table>
<p style="margin:18px 0 0;color:#768397;font-size:11px;line-height:1.6;">If the button does not work, copy this link into your browser:<br><a href="{{ $actionUrl }}" style="color:#526176;word-break:break-all;">{{ $actionUrl }}</a></p>
</td></tr>
@endif
<tr><td style="padding:24px 28px;background-color:#f7f9fc;border-top:1px solid #e7ecf2;">
<p style="margin:0 0 8px;color:#14243a;font-size:13px;font-weight:bold;">Need a hand? Contact dispatch.</p>
<p style="margin:0;font-size:12px;line-height:1.9;">
@if($supportEmail)<a href="mailto:{{ $supportEmail }}" style="color:#0066ff;text-decoration:none;">{{ $supportEmail }}</a><br>@endif
@if($supportPhone)<a href="tel:{{ preg_replace('/[^+0-9]/', '', $supportPhone) }}" style="color:#526176;text-decoration:none;">{{ $supportPhone }}</a>@endif
</p></td></tr>
</table>
<p style="max-width:560px;margin:20px 0 0;color:#7b8798;font-size:11px;line-height:1.8;">&copy; {{ date('Y') }} {{ $businessName }}<br>This service email relates to an InstaDrop request, booking or delivery.</p>
</td></tr></table>
</body></html>
