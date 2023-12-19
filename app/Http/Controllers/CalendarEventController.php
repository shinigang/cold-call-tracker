<?php

namespace App\Http\Controllers;

use App\Models\CalendarEvent;
use App\Models\User;
use App\Notifications\CalendarEventCancelledNotification;

use Carbon\Carbon;
use Exception;
use Google\Client;
use Google\Service\Calendar;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;


use Illuminate\Support\Facades\Notification;

class CalendarEventController extends Controller
{
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(CalendarEvent $calendarEvent)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, CalendarEvent $calendarEvent)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CalendarEvent $calendarEvent)
    {
        $this->authorize('delete', $calendarEvent);

        // ignore in testing as it interacts with google calendar api
        if (!app()->runningUnitTests()) {
            try {
                $this->deleteGoogleEvent($calendarEvent);
            } catch (Exception $ex) {
                dd($ex);

                return redirect()->back()->with([
                    'alert_type' => 'error',
                    'alert_message' => "Something happened please try again!"
                ]);
            }
        }

        if ($calendarEvent->delete()) {
            // Notify the attendees
            foreach ($calendarEvent->emails as $email) {
                Notification::route('mail', $email)->notify(new CalendarEventCancelledNotification($calendarEvent));
            }
            if (request()->wantsJson()) {
                return true;
            }

            return redirect()->back()->with([
                'alert_type' => 'success',
                'alert_message' => "Calendar event cancelled!"
            ]);
        };
    }

    /**
     * Delete event from google calendar
     *
     * @param CalendarEvent $calendarEvent
     * @return boolean
     */
    protected function deleteGoogleEvent(CalendarEvent $calendarEvent)
    {
        $client = new Client();

        if (Carbon::now()->greaterThan($calendarEvent->user->google_metadata['token_expiry'])) {
            $client->setClientId(config('services.google.client_id'));
            $client->setClientSecret(config('services.google.client_secret'));
            $newToken = $client->fetchAccessTokenWithRefreshToken($calendarEvent->user->google_metadata['refresh_token']);
            $calendarEvent->user->setGoogleMetadata(null, $newToken['access_token'], $newToken['refresh_token'], $newToken['expires_in']);
        }

        $client->setAccessToken($calendarEvent->user->google_metadata['token']);
        $service = new Calendar($client);

        return $service->events->delete('primary', $calendarEvent->calendar_id);
    }

    public function userMeetings($user_id)
    {
        $meetings = [];
        $upcoming = isset(request()->upcoming) ? request()->input('upcoming') : false;
        $availability = request()->user()->availability;
        $timeslots = request()->user()->timeslots;

        if ($user_id) {
            $user = User::find($user_id);
            $availability = $user->availability;
            $timeslots = $user->timeslots;
            $events = CalendarEvent::where('user_id', $user_id);
            if ($upcoming) {
                $events = $events->where('start', '>', now());
            }
            $meetings = $events->get();
        }

        return [
            'meetings' => $meetings,
            'availability' => $availability,
            'timeslots' => $timeslots
        ];
    }
}
