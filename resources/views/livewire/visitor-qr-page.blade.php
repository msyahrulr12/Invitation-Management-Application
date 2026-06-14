<div class="min-h-screen flex items-center justify-center p-6">
    @if($errorMessage)
        <div class="text-center space-y-4">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-red-500/20 mb-4">
                <svg class="w-8 h-8 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                </svg>
            </div>
            <h2 class="text-xl font-bold text-white">Not Found</h2>
            <p class="text-gray-400 max-w-sm">{{ $errorMessage }}</p>
        </div>
    @elseif($visitor)
        <div class="w-full max-w-sm space-y-6">
            {{-- Event Header --}}
            <div class="text-center space-y-2">
                <h1 class="text-2xl font-bold text-white">{{ e($eventName) }}</h1>
                <p class="text-gray-400">Your Invitation QR Code</p>
            </div>

            {{-- QR Code Card --}}
            <div class="bg-white rounded-2xl p-6 shadow-xl">
                <div class="flex flex-col items-center space-y-4">
                    @if($visitor->qr_code_path)
                        <img
                            src="{{ asset('storage/' . $visitor->qr_code_path) }}"
                            alt="QR Code"
                            class="w-64 h-64"
                        />
                    @else
                        <div class="flex items-center justify-center w-64 h-64 bg-gray-100 rounded-lg">
                            <p class="text-sm text-gray-500">QR code is being generated...</p>
                        </div>
                    @endif

                    <div class="text-center space-y-1">
                        <p class="text-lg font-semibold text-gray-900">{{ e($visitor->name) }}</p>
                        @if($visitor->email)
                            <p class="text-sm text-gray-500">{{ e($visitor->email) }}</p>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Status --}}
            <div class="text-center">
                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $visitor->status === 'PRESENCE' ? 'bg-emerald-500/20 text-emerald-400' : 'bg-amber-500/20 text-amber-400' }}">
                    @if($visitor->status === 'PRESENCE')
                        ✓ Already Checked In
                    @else
                        Awaiting Check-In
                    @endif
                </span>
            </div>

            {{-- Instructions --}}
            <div class="text-center">
                <p class="text-xs text-gray-500">Show this QR code to the receptionist at the event entrance.</p>
            </div>
        </div>
    @endif
</div>
