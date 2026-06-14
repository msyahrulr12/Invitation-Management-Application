<div class="flex flex-col items-center justify-center p-4 space-y-4">
    @if($visitor->qr_code_path)
    <img
        src="{{ asset('storage/' . $visitor->qr_code_path) }}"
        alt="QR Code for {{ e($visitor->name) }}"
        class="w-64 h-64 border rounded-lg shadow-md"
        style="margin: 0 auto;" />
    @else
    <div class="flex items-center justify-center w-64 h-64 bg-gray-100 dark:bg-gray-800 border rounded-lg">
        <p class="text-sm text-gray-500 dark:text-gray-400">QR code is being generated...</p>
    </div>
    @endif

    <div class="text-center space-y-1 flex justify-center" style="margin:0 auto;">
        <table>
            <tr>
                <td class="w-20">Nama</td>
                <td class="w-2">:</td>
                <td>{{ e($visitor->name) }}</td>
            </tr>
            <tr>
                <td class="w-20">Kode UUID</td>
                <td class="w-2">:</td>
                <td class="font-mono">{{ e($visitor->code_uuid) }}</td>
            </tr>
            <tr>
                <td class="w-20">Email</td>
                <td class="w-2">:</td>
                <td>{{ e($visitor->email) }}</td>
            </tr>
            <tr>
                <td class="w-20">Status</td>
                <td class="w-2">:</td>
                <td>{{ e($visitor->status) }}</td>
            </tr>
        </table>
    </div>
</div>