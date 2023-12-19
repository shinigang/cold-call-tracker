<?php

namespace App\Notifications;

use App\Models\CalendarEvent;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class CalendarEventCancelledNotification extends Notification implements ShouldQueue
{
  use Queueable;

  protected CalendarEvent $calendarEvent;

  /**
   * Create a new notification instance.
   *
   * @return void
   */
  public function __construct(CalendarEvent $calendarEvent)
  {
    $this->calendarEvent = $calendarEvent;
  }

  /**
   * Get the notification's delivery channels.
   *
   * @param  mixed  $notifiable
   * @return array
   */
  public function via($notifiable)
  {
    return ['mail'];
  }

  /**
   * Get the mail representation of the notification.
   *
   * @param  mixed  $notifiable
   * @return \Illuminate\Notifications\Messages\MailMessage
   */
  public function toMail($notifiable)
  {
    $eventDate = Carbon::parse($this->calendarEvent->start);
    return (new MailMessage)
      ->subject("Your {$this->calendarEvent->event_type} meeting was cancelled!")
      ->greeting("Hello {$this->calendarEvent->company->name}")
      ->line("Your {$this->calendarEvent->event_type} meeting with {$notifiable->name} was canceled.")
      ->line("It was scheduled on {$eventDate->toFormattedDateString()} at {$eventDate->format('h:i A')}.")
      ->line('Thank you for using our application!');
  }
}
