<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Reminder</title>
</head>
<body style="margin: 0; padding: 0; background-color: #f8fafc; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;">
    <table width="100%" cellpadding="0" cellspacing="0" style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <tr>
            <td style="background: linear-gradient(135deg, #f59e0b, #d97706); border-radius: 16px 16px 0 0; padding: 32px; text-align: center;">
                <h1 style="color: #ffffff; margin: 0; font-size: 24px;">Event Reminder</h1>
                <p style="color: #fef3c7; margin: 8px 0 0; font-size: 14px;">Tomorrow's Event</p>
            </td>
        </tr>
        <tr>
            <td style="background-color: #ffffff; padding: 32px; border-radius: 0 0 16px 16px; box-shadow: 0 4px 6px rgba(0,0,0,0.07);">
                <h2 style="color: #1e293b; margin: 0 0 16px; font-size: 20px;">{{ e($event->name) }}</h2>

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
                    <tr>
                        <td style="color: #64748b; font-size: 14px;">Visitors</td>
                        <td style="color: #1e293b; font-size: 14px; font-weight: 600;">{{ $visitorCount }} registered</td>
                    </tr>
                    <tr>
                        <td style="color: #64748b; font-size: 14px;">Receptionists</td>
                        <td style="color: #1e293b; font-size: 14px; font-weight: 600;">{{ $receptionistCount }} assigned</td>
                    </tr>
                </table>

                @if($event->description)
                <div style="background-color: #f8fafc; border-radius: 8px; padding: 16px; margin-bottom: 24px;">
                    <p style="color: #475569; font-size: 14px; margin: 0; line-height: 1.6;">{{ e($event->description) }}</p>
                </div>
                @endif

                <p style="color: #94a3b8; font-size: 12px; text-align: center; margin: 24px 0 0;">
                    This is an automated reminder from {{ config('app.name') }}.
                </p>
            </td>
        </tr>
    </table>
</body>
</html>
