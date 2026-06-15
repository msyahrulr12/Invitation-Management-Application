<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">{{ $this->record?->name }}</x-slot>
        <x-slot name="description">{{ $this->record?->description }}</x-slot>

        {{-- Summary Badges --}}
        @php
        $visitors = $this->getVisitors();
        $presence = count(array_filter($visitors, fn($v) => strtolower($v['status'] ?? '') === 'presence'));
        $pending = count(array_filter($visitors, fn($v) => strtolower($v['status'] ?? '') === 'pending'));
        $absence = count(array_filter($visitors, fn($v) => strtolower($v['status'] ?? '') === 'absence'));
        $total = count($visitors);
        @endphp

        <div style="display: flex; flex-wrap: wrap; gap: 10px; margin-bottom: 16px;">
            <div style="display: flex; align-items: center; gap: 8px; background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 10px; padding: 8px 14px;">
                <span style="width: 8px; height: 8px; border-radius: 50%; background: #22c55e; display: inline-block;"></span>
                <span style="font-size: 13px; font-weight: 500; color: #15803d;">Presence</span>
                <span style="font-size: 13px; font-weight: 700; color: #15803d;">{{ $presence }}</span>
            </div>
            <div style="display: flex; align-items: center; gap: 8px; background: #fffbeb; border: 1px solid #fde68a; border-radius: 10px; padding: 8px 14px;">
                <span style="width: 8px; height: 8px; border-radius: 50%; background: #f59e0b; display: inline-block;"></span>
                <span style="font-size: 13px; font-weight: 500; color: #b45309;">Pending</span>
                <span style="font-size: 13px; font-weight: 700; color: #b45309;">{{ $pending }}</span>
            </div>
            <div style="display: flex; align-items: center; gap: 8px; background: #fef2f2; border: 1px solid #fecaca; border-radius: 10px; padding: 8px 14px;">
                <span style="width: 8px; height: 8px; border-radius: 50%; background: #ef4444; display: inline-block;"></span>
                <span style="font-size: 13px; font-weight: 500; color: #b91c1c;">Absence</span>
                <span style="font-size: 13px; font-weight: 700; color: #b91c1c;">{{ $absence }}</span>
            </div>
            <div style="display: flex; align-items: center; gap: 8px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 8px 14px;">
                <span style="width: 8px; height: 8px; border-radius: 50%; background: #94a3b8; display: inline-block;"></span>
                <span style="font-size: 13px; font-weight: 500; color: #475569;">Total</span>
                <span style="font-size: 13px; font-weight: 700; color: #475569;">{{ $total }}</span>
            </div>
        </div>

        {{-- Search Bar --}}
        <div style="margin-bottom: 16px; display: flex; gap: 10px; flex-wrap: wrap;">
            <input
                type="text"
                wire:model.live.debounce.300ms="search"
                placeholder="Search by name, description or status..."
                style="
                    flex: 1;
                    min-width: 200px;
                    padding: 8px 14px;
                    font-size: 14px;
                    border: 1px solid #d1d5db;
                    border-radius: 8px;
                    outline: none;
                    box-sizing: border-box;
                    background: #fff;
                    color: #111827;
                " />

            {{-- Status Filter --}}
            <select
                wire:model.live="statusFilter"
                style="
                    padding: 8px 14px;
                    font-size: 14px;
                    border: 1px solid #d1d5db;
                    border-radius: 8px;
                    outline: none;
                    background: #fff;
                    color: #111827;
                    cursor: pointer;
                ">
                <option value="">All Status</option>
                <option value="presence">Presence</option>
                <option value="pending">Pending</option>
                <option value="absence">Absence</option>
            </select>
        </div>

        {{-- Results Count --}}
        <p style="font-size: 13px; color: #6b7280; margin: 0 0 12px;">
            {{ $total }} visitor(s) found
        </p>

        {{-- Grid --}}
        @if ($total > 0)
        <div id="visitor-grid" style="display: grid; grid-template-columns: repeat(5, 1fr); gap: 12px;">
            @foreach ($visitors as $visitor)
            @php
            $status = strtolower($visitor['status'] ?? 'pending');
            $statusConfig = match($status) {
            'presence' => [
            'label' => 'Presence',
            'bg' => '#f0fdf4',
            'border' => '#bbf7d0',
            'color' => '#15803d',
            'dot' => '#22c55e',
            ],
            'absence' => [
            'label' => 'Absence',
            'bg' => '#fef2f2',
            'border' => '#fecaca',
            'color' => '#b91c1c',
            'dot' => '#ef4444',
            ],
            default => [
            'label' => 'Pending',
            'bg' => '#fffbeb',
            'border' => '#fde68a',
            'color' => '#b45309',
            'dot' => '#f59e0b',
            ],
            };
            @endphp

            <div
                style="border-radius: 12px; border: 1px solid #e5e7eb; background: #fff; overflow: hidden; cursor: pointer; transition: box-shadow 0.2s;"
                onclick="showQrModal('{{ addslashes($visitor['name']) }}', '{{ addslashes($visitor['description'] ?? '') }}', '{{ asset('storage/' . $visitor['qr_code_path']) }}', '{{ $status }}')"
                onmouseover="this.style.boxShadow='0 4px 16px rgba(0,0,0,0.10)'"
                onmouseout="this.style.boxShadow='none'">
                {{-- QR Image --}}
                <div style="position: relative; width: 100%; aspect-ratio: 4/3; overflow: hidden; background: #f3f4f6; display: flex; justify-content: center; align-items: center;">
                    <img
                        src="{{ asset('storage/' . $visitor['qr_code_path']) }}"
                        alt="{{ $visitor['name'] }}"
                        loading="lazy"
                        style="width: auto; height: 100%; max-width: 100%; max-height: 100%; object-fit: contain; display: block; padding: 5px 0;" />

                    {{-- Status Badge on image --}}
                    <div style="
                                position: absolute;
                                top: 7px;
                                left: 7px;
                                background: {{ $statusConfig['bg'] }};
                                border: 1px solid {{ $statusConfig['border'] }};
                                border-radius: 999px;
                                padding: 3px 8px;
                                display: flex;
                                align-items: center;
                                gap: 5px;
                            ">
                        <span style="width: 6px; height: 6px; border-radius: 50%; background: {{ $statusConfig['dot'] }}; display: inline-block; flex-shrink: 0;"></span>
                        <span style="font-size: 10px; font-weight: 600; color: {{ $statusConfig['color'] }};">{{ $statusConfig['label'] }}</span>
                    </div>
                </div>

                {{-- Card Footer --}}
                <div style="padding: 8px 10px 10px;">
                    <p style="margin: 0 0 2px; font-size: 13px; font-weight: 500; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; color: #6b7280;">
                        {{ $visitor['name'] }}
                    </p>
                    <p style="margin: 0; font-size: 12px; color: #000; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                        {{ $visitor['description'] }}
                    </p>
                </div>
            </div>
            @endforeach
        </div>
        @else
        <div style="text-align: center; padding: 48px 0;">
            <p style="font-size: 15px; margin: 0 0 4px; color: #6b7280;">No visitors found</p>
            <p style="font-size: 13px; margin: 0; color: #9ca3af;">Try a different search term or filter</p>
        </div>
        @endif

    </x-filament::section>
</x-filament-widgets::widget>

<style>
    @keyframes qrModalIn {
        from {
            opacity: 0;
            transform: scale(0.95);
        }

        to {
            opacity: 1;
            transform: scale(1);
        }
    }
</style>

<script>
    document.getElementById('qr-modal-overlay')?.remove();

    function getStatusConfig(status) {
        const configs = {
            presence: {
                label: 'Presence',
                bg: '#f0fdf4',
                border: '#bbf7d0',
                color: '#15803d',
                dot: '#22c55e',
            },
            absence: {
                label: 'Absence',
                bg: '#fef2f2',
                border: '#fecaca',
                color: '#b91c1c',
                dot: '#ef4444',
            },
            pending: {
                label: 'Pending',
                bg: '#fffbeb',
                border: '#fde68a',
                color: '#b45309',
                dot: '#f59e0b',
            },
        };
        return configs[status] ?? configs.pending;
    }

    function showQrModal(name, description, qrSrc, status) {
        document.getElementById('qr-modal-overlay')?.remove();

        const s = getStatusConfig(status);

        const overlay = document.createElement('div');
        overlay.id = 'qr-modal-overlay';
        overlay.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.6);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 99999;
            padding: 24px;
            box-sizing: border-box;
        `;

        overlay.innerHTML = `
            <div style="
                background: #fff;
                border-radius: 16px;
                padding: 24px;
                width: 100%;
                max-width: 360px;
                box-shadow: 0 20px 60px rgba(0,0,0,0.3);
                position: relative;
                animation: qrModalIn 0.2s ease;
            ">
                {{-- Close --}}
                <button
                    onclick="closeQrModal()"
                    style="
                        position: absolute;
                        top: 12px;
                        right: 12px;
                        background: #f3f4f6;
                        border: none;
                        border-radius: 50%;
                        width: 32px;
                        height: 32px;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        cursor: pointer;
                        font-size: 16px;
                        color: #6b7280;
                        line-height: 1;
                    "
                >✕</button>

                {{-- Visitor info + status --}}
                <div style="margin-bottom: 16px; padding-right: 32px;">
                    <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 6px; flex-wrap: wrap;">
                        <p style="font-size: 16px; font-weight: 600; color: #111827; margin: 0;">${name}</p>
                        <div style="
                            display: inline-flex;
                            align-items: center;
                            gap: 5px;
                            background: ${s.bg};
                            border: 1px solid ${s.border};
                            border-radius: 999px;
                            padding: 3px 10px;
                        ">
                            <span style="width: 7px; height: 7px; border-radius: 50%; background: ${s.dot}; display: inline-block;"></span>
                            <span style="font-size: 11px; font-weight: 600; color: ${s.color};">${s.label}</span>
                        </div>
                    </div>
                    <p style="font-size: 13px; color: #6b7280; margin: 0;">${description}</p>
                </div>

                {{-- QR --}}
                <div style="background: #f9fafb; border-radius: 12px; padding: 16px; display: flex; align-items: center; justify-content: center;">
                    <img src="${qrSrc}" alt="${name}" style="width: 100%; max-width: 280px; height: auto; display: block;" />
                </div>

                {{-- Download --}}
                
                    <a href="${qrSrc}"
                    download="${name}_qr.png"
                    style="
                        display: block;
                        margin-top: 16px;
                        text-align: center;
                        background: #111827;
                        color: #fff;
                        font-size: 14px;
                        font-weight: 500;
                        padding: 10px;
                        border-radius: 10px;
                        text-decoration: none;
                    "
                    onmouseover="this.style.background='#374151'"
                    onmouseout="this.style.background='#111827'"
                >Download QR</a>
            </div>
        `;

        overlay.addEventListener('click', function(e) {
            if (e.target === overlay) closeQrModal();
        });

        document._qrEscHandler = function(e) {
            if (e.key === 'Escape') closeQrModal();
        };
        document.addEventListener('keydown', document._qrEscHandler);

        document.body.appendChild(overlay);
    }

    function closeQrModal() {
        const overlay = document.getElementById('qr-modal-overlay');
        if (overlay) {
            overlay.style.opacity = '0';
            overlay.style.transition = 'opacity 0.15s ease';
            setTimeout(() => overlay.remove(), 150);
        }
        document.removeEventListener('keydown', document._qrEscHandler);
    }

    // Responsive grid
    (function() {
        function getColumns() {
            const w = window.innerWidth;
            if (w >= 1280) return 5;
            if (w >= 1024) return 4;
            if (w >= 768) return 3;
            if (w >= 480) return 2;
            return 1;
        }

        function updateGrid() {
            const grid = document.getElementById('visitor-grid');
            if (!grid) return;
            grid.style.gridTemplateColumns = `repeat(${getColumns()}, 1fr)`;
        }

        updateGrid();

        let resizeTimer;
        window.addEventListener('resize', function() {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(updateGrid, 100);
        });

        document.addEventListener('livewire:navigated', updateGrid);
        document.addEventListener('livewire:updated', updateGrid);
    })();
</script>