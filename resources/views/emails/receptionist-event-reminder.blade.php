<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Assignment Reminder</title>
</head>
<body style="margin: 0; padding: 0; background-color: #f8fafc; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;">
    <table width="100%" cellpadding="0" cellspacing="0" style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <tr>
            <td style="background: linear-gradient(135deg, #3b82f6, #1d4ed8); border-radius: 16px 16px 0 0; padding: 32px; text-align: center;">
                <h1 style="color: #ffffff; margin: 0; font-size: 24px;">Receptionist Assignment</h1>
                <p style="color: #bfdbfe; margin: 8px 0 0; font-size: 14px;">You have been assigned to an upcoming event</p>
            </td>
        </tr>
        <tr>
            <td style="background-color: #ffffff; padding: 32px; border-radius: 0 0 16px 16px; box-shadow: 0 4px 6px rgba(0,0,0,0.07);">
                <p style="color: #475569; font-size: 16px; margin: 0 0 20px;">Hello, <strong>{{ e($receptionist->name) }}</strong></p>

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
                </table>

                {{-- Scanner Link --}}
                <div style="background: linear-gradient(135deg, #eff6ff, #dbeafe); border: 1px solid #93c5fd; border-radius: 12px; padding: 20px; margin-bottom: 24px; text-align: center;">
                    <p style="color: #1e40af; font-size: 14px; margin: 0 0 8px; font-weight: 600;">Your Scanner Link</p>
                    <a href="{{ $scannerUrl }}" style="display: inline-block; background-color: #2563eb; color: #ffffff; text-decoration: none; padding: 12px 24px; border-radius: 8px; font-size: 14px; font-weight: 600;">
                        Open Scanner
                    </a>
                    <p style="color: #64748b; font-size: 12px; margin: 12px 0 0; word-break: break-all;">{{ $scannerUrl }}</p>
                </div>

                {{-- PIN --}}
                <div style="background-color: #fefce8; border: 1px solid #fde047; border-radius: 12px; padding: 16px; margin-bottom: 24px; text-align: center;">
                    <p style="color: #854d0e; font-size: 14px; margin: 0 0 4px; font-weight: 600;">Your Scanner PIN</p>
                    <p style="color: #1e293b; font-size: 28px; margin: 0; font-weight: 700; letter-spacing: 0.3em; font-family: monospace;">{{ e($pin) }}</p>
                    <p style="color: #a16207; font-size: 12px; margin: 8px 0 0;">Keep this PIN confidential. You will need it to access the scanner.</p>
                </div>

                <p style="color: #94a3b8; font-size: 12px; text-align: center; margin: 24px 0 0;">
                    This is an automated reminder from {{ config('app.name') }}.
                </p>
            </td>
        </tr>
    </table>
</body>
</html>
