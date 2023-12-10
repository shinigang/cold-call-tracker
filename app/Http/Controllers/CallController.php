<?php

namespace App\Http\Controllers;

use App\Models\ActionLog;
use App\Models\Assignment;
use App\Models\Call;
use App\Models\Company;
use App\Models\User;
use App\Exports\CallsExport;

use Carbon\Carbon;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Spatie\GoogleCalendar\Event;

class CallController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        // $calls = Call::orderBy('contact_number');
        $calls = Call::with(['user', 'consultant', 'company'])->whereHas('company', function ($q) {
            $q->where('team_id', auth()->user()->current_team_id);
        });
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
            // 'keyword' => $keyword,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'company_id' => 'required',
            'contact_number' => 'string|max:255',
            'called_at' => 'required|string',
            'status' => 'required|string|max:255',
            'follow_up_at' => 'nullable|required_if:status,Call again on Date|date|after:called_at',
            'appointment_at' => 'nullable|required_if:status,Set Appointment Date|date|after:called_at',
            'consultant_id' => 'nullable|required_if:status,Set Appointment Date',
            'meeting_email' => 'nullable|email|max:255',
        ]);
        $validated['called_at'] = Carbon::parse($validated['called_at'])->format('Y-m-d H:i:s');
        if ($validated['status'] == 'Call again on Date') {
            $validated['follow_up_at'] = Carbon::parse($validated['follow_up_at'])->format('Y-m-d H:i:s');
        } else if ($validated['status'] == 'Set Appointment Date') {
            $validated['appointment_at'] = Carbon::parse($validated['appointment_at'])->format('Y-m-d H:i:s');
        }
        $validated['user_id'] = auth()->user()->id;

        $call = Call::create($validated);
        $company = Company::find($request->company_id);
        $actionLog = new ActionLog;
        $actionLog->company_id = $company->id;
        $actionLog->user_id = auth()->user()->id;
        $actionLog->action_type = 'added call log';
        $actionLog->action_value = $call->contact_number . ' - ' . $call->status;
        $actionLog->save();

        $company->call_status = $call->status;
        if ($company->assigned_caller != $call->user_id) {
            $company->assigned_caller = $call->user_id;
            Assignment::create([
                'company_id' => $company->id,
                'user_id' => $call->user_id,
                'role' => 'Caller'
            ]);
        }

        if ($call->status == 'Call again on Date' || $call->status == 'Set Appointment Date') {
            $event = new Event;

            if ($call->status == 'Set Appointment Date') {
                $company->appointment_date = $call->appointment_at;

                $event->name = 'Appointment';
                $event->description = 'You have an appointment with our very own web consultant.';
                $event->startDateTime = Carbon::parse($call->appointment_at);
                $event->endDateTime = Carbon::parse($call->appointment_at)->addHour();

                $consultant = User::find($call->consultant_id);
                $event->addAttendee([
                    'email' => $consultant->email,
                    'name' => $consultant->name,
                    'comment' => 'Web Consultant',
                ]);

                if ($company->assigned_consultant != $call->consultant_id) {
                    $company->assigned_consultant = $call->consultant_id;
                    Assignment::create([
                        'company_id' => $company->id,
                        'user_id' => $call->consultant_id,
                        'role' => 'Web Consultant'
                    ]);
                }
            } else if ($call->status == 'Call again on Date') {
                $company->follow_up_date = $call->follow_up_at;

                $event->name = 'Follow up';
                $event->description = 'Hi, we would like to talk to you again on your chosen schedule.';
                $event->startDateTime = Carbon::parse($call->follow_up_at);
                $event->endDateTime = Carbon::parse($call->follow_up_at)->addHour();

                $event->addAttendee([
                    'email' => auth()->user()->email,
                    'name' => auth()->user()->name,
                    'comment' => 'Caller',
                ]);
            }

            // $event->addAttendee([
            //     'email' => 'guanlao.sherwin@gmail.com',
            //     'name' => 'Urban Gulaman',
            //     'comment' => 'Lorum ipsum',
            //     'responseStatus' => 'needsAction',
            // ]);
            if ($call->meeting_email) {
                $event->addAttendee(['email' => $call->meeting_email]);
            }
            if ($company->email) {
                $event->addAttendee(['email' => $company->email]);
            }

            $event->addMeetLink();
            $event->save();
        }
        $company->save();

        if (request()->wantsJson()) {
            $companyRelationships = [
                'contactPersons',
                'contactNumbers',
                'assignedCaller',
                'assignedConsultant',
                'calendarEvents.user',
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
     * Display the specified resource.
     */
    public function show(Call $call)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Call $call)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Call $call)
    {
        //
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
}
