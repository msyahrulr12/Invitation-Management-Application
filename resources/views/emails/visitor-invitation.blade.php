<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Invitation</title>
</head>
<body style="margin: 0; padding: 0; background-color: #f8fafc; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;">
    <table width="100%" cellpadding="0" cellspacing="0" style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <tr>
            <td style="background: linear-gradient(135deg, #10b981, #059669); border-radius: 16px 16px 0 0; padding: 32px; text-align: center;">
                <h1 style="color: #ffffff; margin: 0; font-size: 24px;">You're Invited!</h1>
                <p style="color: #a7f3d0; margin: 8px 0 0; font-size: 14px;">{{ e($event->name) }}</p>
            </td>
        </tr>
        <tr>
            <td style="background-color: #ffffff; padding: 32px; border-radius: 0 0 16px 16px; box-shadow: 0 4px 6px rgba(0,0,0,0.07);">
                <p style="color: #475569; font-size: 16px; margin: 0 0 20px;">Dear <strong>{{ e($visitor->name) }}</strong>,</p>

                <p style="color: #475569; font-size: 14px; line-height: 1.6; margin: 0 0 20px;">
                    We are pleased to invite you to <strong>{{ e($event->name) }}</strong>. Please find your event details and QR code below.
                </p>

                <table width="100%" cellpadding="8" cellspacing="0" style="margin-bottom: 24px;">
                    <tr>
                        <td style="color: #64748b; font-size: 14px; width: 120px;">Date & Time</td>
                        <td style="color: #1e293b; font-size: 14px; font-weight: 600;">{{ $event->started_at->format('d M Y, H:i') }} — {{ $event->finished_at->format('H:i') }}</td>
                    </tr>
                    @if($event->google_maps_location_address)
                    <tr>
                        <td style="color: #64748b; font-size: 14px;">Location</td>
                        <td style="color: #1e293b; font-size: 14px;">{{ e($event->google_maps_location_address) }}</td>
                    </tr>
                    @endif
                    @if($event->google_maps_location_url)
                    <tr>
                        <td style="color: #64748b; font-size: 14px;">Map</td>
                        <td><a href="{{ $event->google_maps_location_url }}" style="color: #2563eb; font-size: 14px; text-decoration: underline;">Open in Google Maps</a></td>
                    </tr>
                    @endif
                </table>

                @if($event->description)
                <div style="background-color: #f0fdf4; border-radius: 8px; padding: 16px; margin-bottom: 24px;">
                    <p style="color: #166534; font-size: 14px; margin: 0; line-height: 1.6;">{{ e($event->description) }}</p>
                </div>
                @endif

                {{-- QR Code Section --}}
                <div style="background-color: #f8fafc; border: 2px dashed #cbd5e1; border-radius: 12px; padding: 24px; margin-bottom: 24px; text-align: center;">
                    <p style="color: #475569; font-size: 14px; margin: 0 0 16px; font-weight: 600;">Your Presence QR Code</p>

                    @if($visitor->qr_code_path)
                    <img src="{{ asset('storage/' . $visitor->qr_code_path) }}" alt="QR Code" width="200" height="200" style="display: block; margin: 0 auto 16px;" />
                    @endif

                    <p style="color: #64748b; font-size: 12px; margin: 0 0 12px;">
                        If the QR code image doesn't display, use the link below:
                    </p>

                    <a href="{{ $qrPageUrl }}" style="display: inline-block; background-color: #10b981; color: #ffffff; text-decoration: none; padding: 10px 20px; border-radius: 8px; font-size: 14px; font-weight: 600;">
                        View My QR Code
                    </a>
                </div>

                <p style="color: #475569; font-size: 14px; line-height: 1.6; margin: 0 0 8px;">
                    Please show this QR code to the receptionist at the event entrance for check-in.
                </p>

                <p style="color: #94a3b8; font-size: 12px; text-align: center; margin: 24px 0 0;">
                    This is an automated invitation from {{ config('app.name') }}.
                </p>
            </td>
        </tr>
    </table>
</body>
</html>
