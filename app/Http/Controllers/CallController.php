<?php

namespace App\Http\Controllers;

use App\Exports\CallsExport;
use App\Models\ActionLog;
use App\Models\Assignment;
use App\Models\Call;
use App\Models\Company;
use App\Models\User;
use App\Notifications\CalendarEventCreatedNotification;

use Carbon\Carbon;
use Exception;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use Google\Client;
use Google\Service\Calendar;
use Google\Service\Calendar\Event;

class CallController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $role = $request->user()->teamRole($request->user()->currentTeam);

        $calls = Call::with(['user', 'consultant', 'company'])->whereHas('company', function ($q) {
            $q->where('team_id', auth()->user()->current_team_id);
        });
        if (!in_array($role->key, ['admin', 'owner'])) {
            $calls = $calls->where('user_id', auth()->id());
        }
        $keyword = $request->input('keyword');
        if ($keyword) {
            $calls = $calls->where('contact_number', 'like', "%$keyword%")
                ->orWhere('status', 'like', "%$keyword%")
                ->orWhereHas('company', function ($q) {
                    $q->where('name', 'like', "%" . request()->input('keyword') . "%");
                })
                ->orWhereHas('user', function ($q) {
                    $q->where('name', 'like', "%" . request()->input('keyword') . "%");
                });
        }

        return Inertia::render('Calls/Index', [
            'calls' => $calls->orderByDesc('called_at')->paginate(10),
            'keyword' => $keyword,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $event_date = '';
        $event_time = '';
        $validated = $request->validate([
            'company_id' => 'required',
            'contact_number' => 'string|max:255',
            'called_at' => 'required|string|date',
            'status' => 'required|string|max:255',
            'follow_up_at' => 'nullable|required_if:status,Call again on Date|date|after:called_at',
            'follow_up_time' => 'nullable|required_if:status,Call again on Date|date_format:H:i',
            'appointment_at' => 'nullable|required_if:status,Set Appointment Date|date|after:called_at',
            'appointment_time' => 'nullable|required_if:status,Set Appointment Date|date_format:H:i',
            'consultant_id' => 'nullable|required_if:status,Set Appointment Date',
            'meeting_email' => 'nullable|email|max:255',
        ]);
        $validated['called_at'] = Carbon::parse($validated['called_at'])->format('Y-m-d H:i:s');
        if ($validated['status'] == 'Call again on Date') {
            $event_date = $validated['follow_up_at'];
            $event_time = $validated['follow_up_time'];
            $validated['follow_up_at'] = Carbon::parse($validated['follow_up_at'] . ' ' . $validated['follow_up_time'] . ':00')->format('Y-m-d H:i:s');
            unset($validated['appointment_at']);
            unset($validated['appointment_time']);
            unset($validated['consultant_id']);
        } else if ($validated['status'] == 'Set Appointment Date') {
            $event_date = $validated['appointment_at'];
            $event_time = $validated['appointment_time'];
            $validated['appointment_at'] = Carbon::parse($validated['appointment_at' . ' ' .  $validated['appointment_time'] . ':00'])->format('Y-m-d H:i:s');
            unset($validated['follow_up_at']);
            unset($validated['follow_up_time']);
        } else {
            unset($validated['appointment_at']);
            unset($validated['appointment_time']);
            unset($validated['consultant_id']);
            unset($validated['follow_up_at']);
            unset($validated['follow_up_time']);
            unset($validated['meeting_email']);
        }
        $validated['user_id'] = auth()->user()->id;

        $call = Call::create($validated);
        $company = Company::find($request->company_id);

        if (config('app.save_action_logs')) {
            $actionLog = new ActionLog;
            $actionLog->company_id = $company->id;
            $actionLog->user_id = auth()->user()->id;
            $actionLog->action_type = 'added call log';
            $actionLog->action_value = $call->contact_number . ' - ' . $call->status;
            $actionLog->save();
        }

        $company->call_status = $call->status;
        if ($company->assigned_caller != $call->user_id) {
            $company->assigned_caller = $call->user_id;
            Assignment::create([
                'company_id' => $company->id,
                'user_id' => $call->user_id,
                'role' => 'Caller'
            ]);
        }
        if ($call->status == 'Set Appointment Date' && $company->assigned_consultant != $call->consultant_id) {
            $company->assigned_consultant = $call->consultant_id;
            Assignment::create([
                'company_id' => $company->id,
                'user_id' => $call->consultant_id,
                'role' => 'Web Consultant'
            ]);
        }

        if ($call->status == 'Call again on Date' || $call->status == 'Set Appointment Date') {
            if (!app()->runningUnitTests()) {
                $event_type = $call->status == 'Set Appointment Date' ? 'Appointment' : 'Follow Up Call';
                $event_name = $call->status == 'Set Appointment Date' ? 'Web consultant appointment' : 'Follow up call';
                $calendar_owner = $call->status == 'Set Appointment Date' ? $call->consultant : $call->user;
                $event_emails = [$company->email];
                if ($call->meeting_email) {
                    array_push($event_emails, $call->meeting_email);
                }
                foreach ($company->contactPersons as $contactPerson) {
                    if ($contactPerson->email) {
                        array_push($event_emails, [$contactPerson->email => $contactPerson->name]);
                    }
                }
                try {
                    $calendarEvent = $this->createGoogleEvent(
                        $calendar_owner,
                        $event_name,
                        $event_date,
                        $event_time,
                        $event_emails,
                    );
                } catch (Exception $ex) {
                    dd($ex);
                    return redirect()->back()->with([
                        'alert_type' => 'error',
                        'alert_message' => "Something happened please try again!"
                    ]);
                }

                $event = $company->calendarEvents()->create([
                    'event_type' => $event_type,
                    'call_id' => $call->id,
                    ...$calendarEvent ?? []
                ]);

                // Notify user (Caller / Web Consultant)
                $calendar_owner->notify(new CalendarEventCreatedNotification($event));

                // Notify the attendees
                foreach ($event_emails as $event_email) {
                    Notification::route('mail', $event_email)->notify(new CalendarEventCreatedNotification($event));
                }
            }
        }
        $company->save();

        if (request()->wantsJson()) {
            $companyRelationships = [
                'contactPersons',
                'contactNumbers',
                'assignedCaller',
                'assignedConsultant',
                // 'calendarEvents.user',
                'calls.user',
                'comments.user',
                'actionLogs.user',
                'assignments.user',
            ];
            return [
                'company' => Company::with($companyRelationships)->whereUuid($company->uuid)->first(),
                'call' => Call::with(['user', 'consultant'])->whereId($call->id)->first()
            ];
        }

        if (request()->input('source') == 'dashboard' && $company) {
            return redirect('/dashboard?company=' . $company->uuid)->with('company', $company);
        }

        return redirect(route('calls.index'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Call $call)
    {
        $call->delete();

        return redirect()->back()->with('message', 'Call deleted.');
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function export()
    {
        return Excel::download(new CallsExport, 'calls.xlsx');
    }

    /**
     * Add event to google calendar
     *
     * @param string $event_name
     * @param User $user
     * @param string $event_date
     * @param string $event_time
     * @param array $event_emails
     * @return array
     */
    protected function createGoogleEvent(User $user, $event_name, $event_date, $event_time, $event_emails)
    {
        $client = new Client();
        if (Carbon::now()->greaterThan($user->google_metadata['token_expiry'])) {
            $client->setClientId(config('services.google.client_id'));
            $client->setClientSecret(config('services.google.client_secret'));
            $newToken = $client->fetchAccessTokenWithRefreshToken($user->google_metadata['refresh_token']);
            $user->setGoogleMetadata(null, $newToken['access_token'], $newToken['refresh_token'], $newToken['expires_in']);
        }
        $client->setAccessToken($user->google_metadata['token']);
        $service = new Calendar($client);

        $attendees = [];
        foreach ($event_emails as $event_email) {
            if (is_array($event_email)) {
                $attendees[] = ['email' => array_key_first($event_email), 'displayName' => $event_email[0]];
            } else {
                $attendees[] = ['email' => $event_email];
            }
        }
        $parsed_event_time = Carbon::parse($event_time);

        $event_start = Carbon::parse($event_date)->setTimeFrom($parsed_event_time);
        $event_end = Carbon::parse($event_date)->setTimeFrom($parsed_event_time)->addMinutes($user->availability['duration'] ?? 60);

        $calendar_event = new Event(array(
            'summary' => $event_name,
            'location' => 'Google Meet',
            'start' => array(
                'dateTime' => $event_start,
            ),
            'end' => array(
                'dateTime' => $event_end,
            ),
            'attendees' => [
                ...$attendees,
                [
                    'email' => $user->email,
                    'displayName' => $user->name
                ]
            ],
            'reminders' => array(
                'useDefault' => FALSE,
                'overrides' => array(
                    array('method' => 'email', 'minutes' => 60),
                    array('method' => 'popup', 'minutes' => 10),
                ),
            ),
            'conferenceData' => [
                'createRequest' => [
                    'conferenceSolutionKey' => [
                        'type' => 'hangoutsMeet'
                    ],
                    'requestId' => Str::random(),
                ],
            ]
        ));

        $calendar_id = 'primary';
        $calendar_event = $service->events->insert($calendar_id, $calendar_event, [
            "conferenceDataVersion" => 1,
            'sendUpdates' => "all"
        ]);

        return [
            'user_id' => $user->id,
            'start' => $event_start,
            'end' => $event_end,
            'calendar_id' => $calendar_event->id,
            'calendar_link' => $calendar_event->htmlLink,
            'meet_link' => $calendar_event->hangoutLink
        ];
    }
}
