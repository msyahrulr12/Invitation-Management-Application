<?php

use App\Jobs\SendEventReminderEmails;
use App\Models\Event;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Send event reminders daily — checks for events starting tomorrow
Schedule::call(function () {
    $tomorrow = now()->addDay()->startOfDay();
    $dayAfter = $tomorrow->copy()->endOfDay();

    $events = Event::where('status', Event::STATUS_ACTIVE)
        ->whereBetween('started_at', [$tomorrow, $dayAfter])
        ->get();

    foreach ($events as $event) {
        SendEventReminderEmails::dispatch($event);
    }
})->daily()->at('08:00')->name('send-event-reminders');
