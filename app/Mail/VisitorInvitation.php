<?php

namespace App\Mail;

use App\Models\Event;
use App\Models\Visitor;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class VisitorInvitation extends Mailable
{
    use Queueable, SerializesModels;

    public string $qrPageUrl;

    public function __construct(
        public Event $event,
        public Visitor $visitor
    ) {
        $this->qrPageUrl = url("/visitor-qr/{$this->visitor->code_uuid}");
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "You're Invited: {$this->event->name}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.visitor-invitation',
            with: [
                'event' => $this->event,
                'visitor' => $this->visitor,
                'qrPageUrl' => $this->qrPageUrl,
            ],
        );
    }

    public function build(): self
    {
        $mail = $this;

        // Attach QR code image inline if it exists
        if ($this->visitor->qr_code_path && Storage::disk('public')->exists($this->visitor->qr_code_path)) {
            $qrPath = Storage::disk('public')->path($this->visitor->qr_code_path);
            $mail->attachData(
                file_get_contents($qrPath),
                'qr-code.png',
                [
                    'mime' => 'image/png',
                ]
            );
        }

        return $mail;
    }
}
