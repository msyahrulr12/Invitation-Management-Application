<?php

namespace App\Mail;

use App\Models\Event;
use App\Models\EventReceptionist;
use App\Models\Receptionist;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ReceptionistEventReminder extends Mailable
{
    use Queueable, SerializesModels;

    public string $scannerUrl;

    public function __construct(
        public Event $event,
        public Receptionist $receptionist,
        public EventReceptionist $eventReceptionist
    ) {
        $this->scannerUrl = url("/scan-presence/{$this->eventReceptionist->code_uuid}");
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Event Reminder: {$this->event->name} — You are assigned as Receptionist",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.receptionist-event-reminder',
            with: [
                'event' => $this->event,
                'receptionist' => $this->receptionist,
                'scannerUrl' => $this->scannerUrl,
                'pin' => $this->eventReceptionist->pin,
            ],
        );
    }
}
