<?php

namespace App\Notifications;

use App\Models\CalendarEvent;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Notifications\Messages\MailMessage;

class CalendarEventReminderNotification extends Notification implements ShouldQueue
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
    if ($notifiable instanceof AnonymousNotifiable) {
      return (new MailMessage)
        ->subject("You have {$this->calendarEvent->event_type} meeting after an hour!")
        ->greeting("Hello {$this->calendarEvent->company->name}")
        ->line("Your {$this->calendarEvent->event_type} meeting with {$this->calendarEvent->user->name} is after an hour from now.")
        ->line("The meeting is scheduled on {$eventDate->toFormattedDateString()} at {$eventDate->format('h:i A')}.")
        ->line("Click the following button to view the link in your calendar")
        ->action("View event", $this->calendarEvent->calendar_link)
        ->line('Thank you for using our application!');
    }

    return (new MailMessage)
      ->subject("You have {$this->calendarEvent->event_type} after an hour!")
      ->greeting("Hello {$notifiable->name}")
      ->line("You have {$this->calendarEvent->event_type} meeting with {$this->calendarEvent->company->name} after an hour from now.")
      ->line("The meeting is scheduled on {$eventDate->toFormattedDateString()} at {$eventDate->format('h:i A')}.")
      ->line("Click the following button to view the link in your calendar.")
      ->action("View event", $this->calendarEvent->calendar_link)
      ->line('Thank you for using our application!');
  }

  /**
   * Get the array representation of the notification.
   *
   * @param  mixed  $notifiable
   * @return array
   */
  public function toArray($notifiable)
  {
    return [
      //
    ];
  }
}
