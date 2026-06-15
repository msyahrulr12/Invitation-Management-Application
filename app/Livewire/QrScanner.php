<?php

namespace App\Livewire;

use App\Models\EventReceptionist;
use App\Models\Visitor;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.scanner-layout')]
class QrScanner extends Component
{
    public string $uuid = '';

    // Event/Receptionist info
    public ?string $eventName = null;
    public ?string $receptionistName = null;
    public ?int $eventId = null;
    public ?int $receptionistId = null;
    public ?string $receptionistCodeUuid = null;

    // PIN auth state
    public bool $isAuthenticated = false;
    public string $pinInput = '';
    public ?string $pinError = null;

    // Scan result
    public ?string $scanResult = null;
    public ?string $scanMessage = null;
    public bool $scanSuccess = false;
    public ?string $scannedVisitorName = null;

    // Page state
    public bool $isValid = false;
    public ?string $errorMessage = null;

    public function mount(string $uuid): void
    {
        $this->uuid = $uuid;

        $eventReceptionist = EventReceptionist::with(['event', 'receptionist'])
            ->where('code_uuid', $uuid)
            ->first();

        if (!$eventReceptionist) {
            $this->errorMessage = 'Invalid scanner link. This scanner code does not exist.';
            return;
        }

        $event = $eventReceptionist->event;
        $receptionist = $eventReceptionist->receptionist;

        if (!$event || !$receptionist) {
            $this->errorMessage = 'Event or receptionist data not found.';
            return;
        }

        // Validate time window: started_at - 1 day until finished_at
        $windowStart = Carbon::parse($event->started_at)->subDay();
        $windowEnd = Carbon::parse($event->finished_at);
        $now = Carbon::now();

        // if ($now->lt($windowStart)) {
        //     $this->errorMessage = "Scanner is not available yet. It will be active from {$windowStart->format('d M Y, H:i')}.";
        //     return;
        // }

        if ($now->gt($windowEnd)) {
            $this->errorMessage = 'This event has ended. Scanner is no longer available.';
            return;
        }

        $this->isValid = true;
        $this->eventName = $event->name;
        $this->receptionistName = $receptionist->name;
        $this->eventId = $event->id;
        $this->receptionistId = $receptionist->id;
        $this->receptionistCodeUuid = $receptionist->code_uuid;

        // Restore PIN auth from session (so PIN is only entered once)
        if (Session::get("scanner_auth_{$this->uuid}") === true) {
            $this->isAuthenticated = true;
        }
    }

    public function verifyPin(): void
    {
        $this->pinError = null;

        $eventReceptionist = EventReceptionist::where('code_uuid', $this->uuid)->first();

        if (!$eventReceptionist) {
            $this->pinError = 'Invalid scanner link.';
            return;
        }

        if ($this->pinInput !== $eventReceptionist->pin) {
            $this->pinError = 'Invalid PIN. Please try again.';
            $this->pinInput = '';
            return;
        }

        $this->isAuthenticated = true;

        // Persist in session so PIN is only entered once
        Session::put("scanner_auth_{$this->uuid}", true);
    }

    public function handleScannedCode(string $decodedText): void
    {
        $this->resetScanState();

        try {
            // Find visitor by code_uuid
            $visitor = Visitor::where('code_uuid', $decodedText)->first();

            if (!$visitor) {
                $this->setScanFailure('Visitor not found. Invalid QR code.');
                return;
            }

            // Validate visitor belongs to the same event
            if ($visitor->event_id !== $this->eventId) {
                $this->setScanFailure('This visitor is not registered for this event.');
                return;
            }

            // Check if already present
            if ($visitor->status === Visitor::STATUS_PRESENCE) {
                $this->setScanFailure(
                    "Visitor \"{$visitor->name}\" has already been marked as present at {$visitor->presence_timestamp?->format('H:i:s')}."
                );
                return;
            }

            // Mark as present
            $visitor->update([
                'status' => Visitor::STATUS_PRESENCE,
                'presence_timestamp' => Carbon::now(),
                'receptionist_id' => $this->receptionistId,
                'receptionist_name' => $this->receptionistName,
                'receptionist_code_uuid' => $this->receptionistCodeUuid,
            ]);

            $this->scannedVisitorName = $visitor->name;
            $this->setScanSuccess("Welcome, {$visitor->name}! Presence recorded successfully.");

            Log::info("Visitor presence recorded: {$visitor->name} (UUID: {$decodedText}) by receptionist: {$this->receptionistName}");
        } catch (\Throwable $e) {
            Log::error("Scan presence error: {$e->getMessage()}");
            $this->setScanFailure('An error occurred while processing the QR code. Please try again.');
        }
    }

    public function resetScanner(): void
    {
        $this->resetScanState();
        $this->dispatch('restart-scanner');
    }

    private function resetScanState(): void
    {
        $this->scanResult = null;
        $this->scanMessage = null;
        $this->scanSuccess = false;
        $this->scannedVisitorName = null;
    }

    private function setScanSuccess(string $message): void
    {
        $this->scanResult = 'success';
        $this->scanMessage = $message;
        $this->scanSuccess = true;
    }

    private function setScanFailure(string $message): void
    {
        $this->scanResult = 'error';
        $this->scanMessage = $message;
        $this->scanSuccess = false;
    }

    public function render()
    {
        return view('livewire.qr-scanner');
    }
}
