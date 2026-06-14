<?php

namespace App\Jobs;

use App\Mail\VisitorInvitation;
use App\Models\Visitor;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendVisitorInvitationEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 30;

    public function __construct(
        public Visitor $visitor
    ) {}

    public function handle(): void
    {
        if (!$this->visitor->email) {
            Log::warning("Cannot send invitation: Visitor {$this->visitor->id} has no email.");
            return;
        }

        if ($this->visitor->invitation_email_sent) {
            Log::info("Invitation already sent to visitor {$this->visitor->id}. Skipping.");
            return;
        }

        $event = $this->visitor->event;
        if (!$event) {
            Log::warning("Cannot send invitation: Visitor {$this->visitor->id} has no event.");
            return;
        }

        try {
            Mail::to($this->visitor->email)
                ->send(new VisitorInvitation($event, $this->visitor));

            $this->visitor->update(['invitation_email_sent' => true]);

            Log::info("Invitation email sent to visitor: {$this->visitor->name} ({$this->visitor->email})");
        } catch (\Throwable $e) {
            Log::error("Failed to send invitation email to visitor {$this->visitor->id}: {$e->getMessage()}");
            throw $e;
        }
    }
}
