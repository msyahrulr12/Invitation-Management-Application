<?php

namespace App\Jobs;

use App\Models\Visitor;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class GenerateVisitorQrCode implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(
        public Visitor $visitor
    ) {}

    public function handle(): void
    {
        try {
            $codeUuid = $this->visitor->code_uuid;
            $fileName = "qrcodes/{$codeUuid}.png";

            // Generate QR code as PNG image containing the visitor's code_uuid
            $qrImage = QrCode::format('png')
                ->size(400)
                ->errorCorrection('H')
                ->margin(2)
                ->generate($codeUuid);

            // Store in public disk
            Storage::disk('public')->put($fileName, $qrImage);

            // Update visitor record with the path
            $this->visitor->update([
                'qr_code_path' => $fileName,
            ]);

            Log::info("QR code generated for visitor: {$this->visitor->name} ({$codeUuid})");
        } catch (\Throwable $e) {
            Log::error("Failed to generate QR code for visitor {$this->visitor->id}: {$e->getMessage()}");
            throw $e;
        }
    }
}
