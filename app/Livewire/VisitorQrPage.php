<?php

namespace App\Livewire;

use App\Models\Visitor;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.scanner-layout')]
class VisitorQrPage extends Component
{
    public string $code_uuid = '';

    public ?Visitor $visitor = null;
    public ?string $eventName = null;
    public ?string $errorMessage = null;

    public function mount(string $code_uuid): void
    {
        $visitor = Visitor::with('event')
            ->where('code_uuid', $code_uuid)
            ->first();

        if (!$visitor) {
            $this->errorMessage = 'Invalid QR code link. Visitor not found.';
            return;
        }

        $this->visitor = $visitor;
        $this->eventName = $visitor->event?->name ?? 'Event';
    }

    public function render()
    {
        return view('livewire.visitor-qr-page');
    }
}
