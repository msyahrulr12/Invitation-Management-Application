<?php

namespace App\Mail;

use App\Models\Event;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AdminEventReminder extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Event $event,
        public int $visitorCount,
        public int $receptionistCount
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Event Reminder: {$this->event->name} — Tomorrow",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.admin-event-reminder',
            with: [
                'event' => $this->event,
                'visitorCount' => $this->visitorCount,
                'receptionistCount' => $this->receptionistCount,
            ],
        );
    }
}
