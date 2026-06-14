<?php

namespace App\Jobs;

use App\Mail\AdminEventReminder;
use App\Mail\ReceptionistEventReminder;
use App\Models\Event;
use App\Models\EventReceptionist;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Modules\UserManagement\Models\User;

class SendEventReminderEmails implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 60;

    public function __construct(
        public Event $event
    ) {}

    public function handle(): void
    {
        $event = $this->event;

        $visitorCount = $event->visitors()->count();
        $receptionistCount = $event->eventReceptionists()->count();

        // Send to admin(s) — all super_admin users
        $admins = User::role('super_admin')->where('status', User::STATUS_ACTIVE)->get();
        foreach ($admins as $admin) {
            try {
                Mail::to($admin->email)
                    ->send(new AdminEventReminder($event, $visitorCount, $receptionistCount));

                Log::info("Admin event reminder sent to: {$admin->email} for event: {$event->name}");
            } catch (\Throwable $e) {
                Log::error("Failed to send admin reminder to {$admin->email}: {$e->getMessage()}");
            }
        }

        // Send to receptionists
        $eventReceptionists = EventReceptionist::with(['receptionist', 'event'])
            ->where('event_id', $event->id)
            ->get();

        foreach ($eventReceptionists as $er) {
            $receptionist = $er->receptionist;
            if (!$receptionist || !$receptionist->email) {
                continue;
            }

            try {
                Mail::to($receptionist->email)
                    ->send(new ReceptionistEventReminder($event, $receptionist, $er));

                Log::info("Receptionist event reminder sent to: {$receptionist->email} for event: {$event->name}");
            } catch (\Throwable $e) {
                Log::error("Failed to send receptionist reminder to {$receptionist->email}: {$e->getMessage()}");
            }
        }
    }
}
