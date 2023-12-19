<?php

namespace App\Jobs;

use Carbon\Carbon;
use App\Models\CalendarEvent;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Notification;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use App\Notifications\CalendarEventReminderNotification;

class CalendarEventReminderJob implements ShouldQueue
{
  use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

  /**
   * Create a new job instance.
   *
   * @return void
   */
  public function __construct()
  {
    //
  }

  /**
   * Execute the job.
   *
   * @return void
   */
  public function handle()
  {
    $calendar_events = CalendarEvent::where('start', Carbon::today()->toDateString())
      ->whereBetween(
        'start',
        Carbon::now()->addHour()->format('H:i'),
        Carbon::now()->addHours(2)->format('H:i')
      );

    foreach ($calendar_events as $calendar_event) {
      // notify attendee emails
      foreach ($calendar_event->emails as $email) {
        Notification::route('mail', $email)->notify(new CalendarEventReminderNotification($calendar_event));
      }

      // notify event creator
      $calendar_event->user->notify(new CalendarEventReminderNotification($calendar_event));
    }
  }
}
