<div style="display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 24px; text-align: center;">
    {{-- QR Container --}}
    <div style="background: #ffffff; padding: 16px; border-radius: 16px; box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1); border: 1px solid #e5e7eb; display: inline-block; margin-bottom: 20px;">
        {!! SimpleSoftwareIO\QrCode\Facades\QrCode::size(240)->margin(1)->generate($url) !!}
    </div>

    {{-- Info & Instructions --}}
    <h3 style="font-size: 16px; font-weight: 600; color: #1f2937; margin: 0 0 6px 0;">Scanner Link QR Code</h3>
    <p style="font-size: 13px; color: #6b7280; max-width: 280px; margin: 0 0 20px 0; line-height: 1.5;">
        Scan this QR code with a phone or tablet to open the QR Scanner page for this receptionist.
    </p>

    {{-- URL display --}}
    <div style="display: flex; align-items: center; gap: 8px; width: 100%; max-width: 380px; background-color: #f3f4f6; border: 1px solid #e5e7eb; padding: 10px 14px; border-radius: 10px; box-sizing: border-box;">
        <span style="font-family: monospace; font-size: 12px; color: #374151; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; flex: 1; text-align: left;">
            {{ $url }}
        </span>
    </div>
</div>
