<div class="min-h-screen flex flex-col">
    {{-- Error state --}}
    @if($errorMessage)
        <div class="flex-1 flex items-center justify-center p-6">
            <div class="text-center space-y-4">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-red-500/20 mb-4">
                    <svg class="w-8 h-8 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                    </svg>
                </div>
                <h2 class="text-xl font-bold text-white">Scanner Unavailable</h2>
                <p class="text-gray-400 max-w-sm">{{ $errorMessage }}</p>
            </div>
        </div>
    @elseif(!$isAuthenticated)
        {{-- PIN Authentication --}}
        <div class="flex-1 flex items-center justify-center p-6">
            <div class="w-full max-w-sm space-y-6">
                <div class="text-center space-y-2">
                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-amber-500/20 mb-2">
                        <svg class="w-8 h-8 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                        </svg>
                    </div>
                    <h1 class="text-2xl font-bold text-white">{{ $eventName }}</h1>
                    <p class="text-gray-400">Hello, {{ $receptionistName }}</p>
                    <p class="text-sm text-gray-500">Enter your PIN to access the scanner</p>
                </div>

                <form wire:submit="verifyPin" class="space-y-4">
                    <div>
                        <input
                            type="password"
                            inputmode="numeric"
                            maxlength="6"
                            wire:model="pinInput"
                            placeholder="Enter PIN"
                            class="w-full text-center text-2xl tracking-[0.5em] bg-gray-800 border border-gray-700 rounded-xl px-4 py-4 text-white placeholder-gray-600 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent"
                            autofocus
                        />
                    </div>

                    @if($pinError)
                        <p class="text-red-400 text-sm text-center">{{ $pinError }}</p>
                    @endif

                    <button
                        type="submit"
                        class="w-full bg-amber-500 hover:bg-amber-600 text-black font-semibold py-3 px-4 rounded-xl transition-colors duration-200"
                    >
                        Unlock Scanner
                    </button>
                </form>
            </div>
        </div>
    @else
        {{-- Scanner View --}}
        <div class="flex flex-col h-screen" x-data="qrScanner()" x-init="startScanner()">
            {{-- Header --}}
            <div class="text-center pt-6 pb-4 px-4 z-10">
                <h1 class="text-xl font-bold tracking-wide text-white">{{ $eventName }}</h1>
                <p class="text-sm text-amber-400 mt-1">Receptionist: {{ $receptionistName }}</p>
                <p class="text-xs text-gray-500 mt-1">Align the QR code within the frame</p>
            </div>

            {{-- Scanner Area --}}
            <div class="flex-1 flex items-center justify-center px-4">
                <div class="w-full max-w-md">
                    <div id="reader" class="rounded-2xl overflow-hidden"></div>
                </div>
            </div>

            {{-- Result Area --}}
            <div class="pb-8 px-4 z-10">
                {{-- Success Message --}}
                @if($scanResult === 'success')
                    <div class="bg-emerald-500/20 border border-emerald-500/50 rounded-xl p-4 mb-4 mx-auto max-w-md backdrop-blur-md">
                        <div class="flex items-center space-x-3">
                            <svg class="w-6 h-6 text-emerald-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <div>
                                <p class="text-sm font-semibold text-emerald-400">Success!</p>
                                <p class="text-sm text-emerald-300 mt-0.5">{{ $scanMessage }}</p>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Error Message --}}
                @if($scanResult === 'error')
                    <div class="bg-red-500/20 border border-red-500/50 rounded-xl p-4 mb-4 mx-auto max-w-md backdrop-blur-md">
                        <div class="flex items-center space-x-3">
                            <svg class="w-6 h-6 text-red-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <div>
                                <p class="text-sm font-semibold text-red-400">Failed</p>
                                <p class="text-sm text-red-300 mt-0.5">{{ $scanMessage }}</p>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Scan Again Button --}}
                @if($scanResult)
                    <div class="text-center">
                        <button
                            wire:click="resetScanner"
                            class="bg-amber-500 hover:bg-amber-600 text-black font-semibold py-3 px-8 rounded-xl transition-colors duration-200"
                        >
                            Scan Again
                        </button>
                    </div>
                @else
                    <p class="text-xs text-gray-500 text-center">Requires camera permissions</p>
                @endif
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
                            const config = {
                                fps: 15,
                                qrbox: {
                                    width: 250,
                                    height: 250
                                }
                            };

                            let cameraId = devices[0].id;
                            const backCamera = devices.find(device => device.label.toLowerCase().includes('back'));
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
                                (errorMessage) => {}
                            ).catch((err) => {
                                const readerEl = document.getElementById('reader');
                                if (readerEl) {
                                    readerEl.textContent = '';
                                    const p = document.createElement('p');
                                    p.className = 'text-center text-red-400 py-8';
                                    p.textContent = 'Camera access denied or not available.';
                                    readerEl.appendChild(p);
                                }
                            });
                        }
                    }).catch(err => {
                        const readerEl = document.getElementById('reader');
                        if (readerEl) {
                            readerEl.textContent = '';
                            const p = document.createElement('p');
                            p.className = 'text-center text-red-400 py-8';
                            p.textContent = 'Unable to access camera. Please grant permissions.';
                            readerEl.appendChild(p);
                        }
                    });
                }
            };
        };

        // Listen for restart-scanner event from Livewire
        document.addEventListener('restart-scanner', () => {
            // Re-initialize the scanner
            const alpineComponent = document.querySelector('[x-data]');
            if (alpineComponent) {
                if (alpineComponent.__x) {
                    alpineComponent.__x.$data.startScanner();
                } else if (window.Alpine) {
                    window.Alpine.$data(alpineComponent).startScanner();
                } else {
                    location.reload();
                }
            } else {
                location.reload();
            }
        });
    </script>
</div>