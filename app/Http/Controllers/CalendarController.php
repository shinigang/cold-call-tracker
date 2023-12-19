<?php

namespace App\Http\Controllers;

use App\Models\CalendarEvent;
use Inertia\Inertia;
use Inertia\Response;

class CalendarController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): Response
    {
        $role = request()->user()->teamRole(auth()->user()->currentTeam);

        $meetings = CalendarEvent::whereNotNull('start');
        if ($role->key == 'caller') {
            $meetings = $meetings->where('event_type', 'Follow Up Call')->where('user_id', auth()->id());
        } else if ($role->key == 'consultant') {
            $meetings = $meetings->where('event_type', 'Appointment')->where('user_id', auth()->id());
        }
        $meetings = $meetings->get();
        $mappedMeetings = $meetings->map(function ($meeting) {
            return [
                'title' => $meeting->event_type . ' with ' . $meeting->company->name,
                'start' => $meeting->start,
                'end' => $meeting->end,
                'url' => $meeting->meet_link,
                'backgroundColor' => $meeting->event_type == 'Appointment' ? 'rgb(34 197 94 / 1)' : 'rgb(129 140 248 / 1)'
            ];
        });

        return Inertia::render('Calendar', [
            'meetings' => $mappedMeetings
        ]);
    }
}
