<div style="min-height: 100svh; display: flex; flex-direction: column; background: #0f0f0f; font-family: system-ui, sans-serif;">

    {{-- ===================== ERROR STATE ===================== --}}
    @if($errorMessage)
    <div style="flex: 1; display: flex; align-items: center; justify-content: center; padding: 24px;">
        <div style="text-align: center;">
            <div style="display: inline-flex; align-items: center; justify-content: center; width: 64px; height: 64px; border-radius: 50%; background: rgba(239,68,68,0.15); margin-bottom: 16px;">
                <svg style="width: 32px; height: 32px; color: #f87171;" fill="none" stroke="#f87171" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z" />
                </svg>
            </div>
            <h2 style="font-size: 20px; font-weight: 700; color: #fff; margin: 0 0 8px;">Scanner Unavailable</h2>
            <p style="font-size: 14px; color: #9ca3af; margin: 0; max-width: 300px;">{{ $errorMessage }}</p>
        </div>
    </div>

    {{-- ===================== PIN AUTH ===================== --}}
    @elseif(!$isAuthenticated)
    <div style="flex: 1; display: flex; align-items: center; justify-content: center; padding: 24px;">
        <div style="width: 100%; max-width: 360px;">

            {{-- Logo / Icon --}}
            <div style="text-align: center; margin-bottom: 32px;">
                <div style="display: inline-flex; align-items: center; justify-content: center; width: 72px; height: 72px; border-radius: 50%; background: rgba(245,158,11,0.15); margin-bottom: 16px;">
                    <svg style="width: 36px; height: 36px;" fill="none" stroke="#f59e0b" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                </div>
                <h1 style="font-size: 24px; font-weight: 700; color: #fff; margin: 0 0 6px;">{{ $eventName }}</h1>
                <p style="font-size: 15px; color: #d1d5db; margin: 0 0 4px;">Hello, <strong style="color: #f59e0b;">{{ $receptionistName }}</strong></p>
                <p style="font-size: 13px; color: #6b7280; margin: 0;">Enter your PIN to access the scanner</p>
            </div>

            {{-- PIN Form --}}
            <form wire:submit="verifyPin">
                <input
                    type="password"
                    inputmode="numeric"
                    maxlength="6"
                    wire:model="pinInput"
                    placeholder="••••••"
                    autofocus
                    style="
                            width: 100%;
                            box-sizing: border-box;
                            text-align: center;
                            font-size: 28px;
                            letter-spacing: 0.5em;
                            background: #1f1f1f;
                            border: 1.5px solid #374151;
                            border-radius: 14px;
                            padding: 16px;
                            color: #fff;
                            outline: none;
                            margin-bottom: 12px;
                            transition: border-color 0.2s;
                        "
                    onfocus="this.style.borderColor='#f59e0b'"
                    onblur="this.style.borderColor='#374151'" />

                @if($pinError)
                <p style="font-size: 13px; color: #f87171; text-align: center; margin: 0 0 12px;">{{ $pinError }}</p>
                @endif

                <button
                    type="submit"
                    style="
                            width: 100%;
                            background: #f59e0b;
                            color: #000;
                            font-size: 15px;
                            font-weight: 600;
                            padding: 14px;
                            border: none;
                            border-radius: 14px;
                            cursor: pointer;
                            transition: background 0.2s;
                        "
                    onmouseover="this.style.background='#d97706'"
                    onmouseout="this.style.background='#f59e0b'">
                    Unlock Scanner
                </button>
            </form>
        </div>
    </div>

    {{-- ===================== SCANNER VIEW ===================== --}}
    @else
    <div
        style="display: flex; flex-direction: column; height: 100svh; overflow: hidden;"
        x-data="qrScanner()"
        x-init="startScanner()">
        {{-- Header --}}
        <div style="text-align: center; padding: 20px 16px 12px; flex-shrink: 0; z-index: 10;">
            <h1 style="font-size: 18px; font-weight: 700; color: #fff; margin: 0 0 4px; letter-spacing: 0.02em;">{{ $eventName }}</h1>
            <p style="font-size: 13px; color: #f59e0b; margin: 0 0 4px;">Receptionist: {{ $receptionistName }}</p>
            <p style="font-size: 12px; color: #6b7280; margin: 0;">Align the QR code within the frame</p>
        </div>

        {{-- Scanner Area --}}
        <div style="flex: 1; display: flex; align-items: center; justify-content: center; padding: 0 16px; min-height: 0;">
            <div style="width: 100%; max-width: 380px;">
                <div
                    id="reader"
                    style="border-radius: 20px; overflow: hidden; border: 2px solid rgba(245,158,11,0.3); box-shadow: 0 0 0 1px rgba(245,158,11,0.1);"></div>
            </div>
        </div>

        {{-- Result / Footer Area --}}
        <div style="padding: 12px 16px 32px; flex-shrink: 0; z-index: 10;">

            {{-- Success --}}
            @if($scanResult === 'success')
            <div style="background: rgba(16,185,129,0.15); border: 1px solid rgba(16,185,129,0.4); border-radius: 14px; padding: 14px 16px; margin-bottom: 14px; max-width: 400px; margin-left: auto; margin-right: auto;">
                <div style="display: flex; align-items: center; gap: 12px;">
                    <svg style="width: 24px; height: 24px; flex-shrink: 0;" fill="none" stroke="#34d399" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <div>
                        <p style="font-size: 13px; font-weight: 600; color: #34d399; margin: 0 0 2px;">Check-in Successful</p>
                        <p style="font-size: 13px; color: #6ee7b7; margin: 0;">{{ $scanMessage }}</p>
                    </div>
                </div>
            </div>
            @endif

            {{-- Error --}}
            @if($scanResult === 'error')
            <div style="background: rgba(239,68,68,0.15); border: 1px solid rgba(239,68,68,0.4); border-radius: 14px; padding: 14px 16px; margin-bottom: 14px; max-width: 400px; margin-left: auto; margin-right: auto;">
                <div style="display: flex; align-items: center; gap: 12px;">
                    <svg style="width: 24px; height: 24px; flex-shrink: 0;" fill="none" stroke="#f87171" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <div>
                        <p style="font-size: 13px; font-weight: 600; color: #f87171; margin: 0 0 2px;">Check-in Failed</p>
                        <p style="font-size: 13px; color: #fca5a5; margin: 0;">{{ $scanMessage }}</p>
                    </div>
                </div>
            </div>
            @endif

            {{-- Scan Again / Hint --}}
            <div style="text-align: center;">
                @if($scanResult)
                <button
                    wire:click="resetScanner"
                    style="
                                background: #f59e0b;
                                color: #000;
                                font-size: 15px;
                                font-weight: 600;
                                padding: 13px 40px;
                                border: none;
                                border-radius: 14px;
                                cursor: pointer;
                                transition: background 0.2s;
                            "
                    onmouseover="this.style.background='#d97706'"
                    onmouseout="this.style.background='#f59e0b'">
                    Scan Again
                </button>
                @else
                <p style="font-size: 12px; color: #4b5563; margin: 0;">Requires camera permissions</p>
                @endif
            </div>
        </div>
    </div>
    @endif

    <script>
        window.qrScanner = function() {
            return {
                scannedCode: '',
                scannerInstance: null,

                startScanner() {
                    const self = this;

                    if (typeof window.Html5Qrcode === 'undefined') {
                        setTimeout(() => self.startScanner(), 500);
                        return;
                    }

                    Html5Qrcode.getCameras().then(devices => {
                        if (devices && devices.length) {
                            self.scannerInstance = new window.Html5Qrcode("reader");

                            // Responsive qrbox: 70% of the smaller screen dimension, capped at 280px
                            const boxSize = Math.min(Math.floor(Math.min(window.innerWidth, window.innerHeight) * 0.7), 280);

                            const config = {
                                fps: 15,
                                qrbox: {
                                    width: boxSize,
                                    height: boxSize
                                },
                                aspectRatio: 1.0,
                            };

                            let cameraId = devices[0].id;
                            const backCamera = devices.find(d => d.label.toLowerCase().includes('back'));
                            if (backCamera) cameraId = backCamera.id;

                            self.scannerInstance.start(
                                cameraId,
                                config,
                                (decodedText) => {
                                    self.scannedCode = decodedText;
                                    if (navigator.vibrate) navigator.vibrate(100);
                                    self.$wire.handleScannedCode(decodedText);
                                    self.scannerInstance.stop().catch(() => {});
                                },
                                () => {}
                            ).catch(() => {
                                const el = document.getElementById('reader');
                                if (el) {
                                    el.innerHTML = '<p style="text-align:center;color:#f87171;padding:32px 16px;font-size:14px;">Camera access denied or not available.</p>';
                                }
                            });
                        }
                    }).catch(() => {
                        const el = document.getElementById('reader');
                        if (el) {
                            el.innerHTML = '<p style="text-align:center;color:#f87171;padding:32px 16px;font-size:14px;">Unable to access camera. Please grant permissions.</p>';
                        }
                    });
                }
            };
        };

        document.addEventListener('restart-scanner', () => {
            const alpineEl = document.querySelector('[x-data]');
            if (alpineEl && window.Alpine) {
                window.Alpine.$data(alpineEl).startScanner();
            } else {
                location.reload();
            }
        });
    </script>
</div>