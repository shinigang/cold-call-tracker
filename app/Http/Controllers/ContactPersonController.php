<?php

namespace App\Http\Controllers;

use App\Models\ActionLog;
use App\Models\Company;
use App\Models\ContactPerson;

use Illuminate\Http\Request;

class ContactPersonController extends Controller
{
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'company_id' => 'required',
            'prefix' => 'nullable|string|max:100',
            'first_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name' => 'required|string|max:255',
            'suffix' => 'nullable|string|max:100',
            'position' => 'nullable|string|max:255',
            'verified' => 'nullable|boolean'
        ]);
        $person = ContactPerson::create($validated);

        if (config('app.save_action_logs')) {
            $actionLog = new ActionLog;
            $actionLog->company_id = $person->company_id;
            $actionLog->user_id = auth()->user()->id;
            $actionLog->action_type = 'added a contact person';
            $actionLog->action_value = $person->first_name . ' ' . $person->last_name;
            $actionLog->save();
        }

        if ($request->wantsJson() && $request->source == 'dashboard') {
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
                'person' => $person,
                'company' => Company::with($companyRelationships)->whereId($person->company_id)->first()
            ];
        }

        $status = __('Contact person added.');
        return $request->wantsJson() ? response(200)->json(['status' => $status]) : back()->with('status', $status);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ContactPerson $contactPerson)
    {
        $validated = $request->validate([
            'prefix' => 'nullable|string|max:100',
            'first_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name' => 'required|string|max:255',
            'suffix' => 'nullable|string|max:100',
            'position' => 'nullable|string|max:255',
            'verified' => 'nullable|boolean'
        ]);
        $contactPerson->update($validated);

        if ($request->wantsJson() && $request->source == 'dashboard') {
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
                'person' => $contactPerson,
                'company' => Company::with($companyRelationships)->whereId($contactPerson->company_id)->first()
            ];
        }

        $status = __('Contact person updated.');
        return $request->wantsJson() ? response(200)->json(['status' => $status]) : back()->with('status', $status);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, ContactPerson $contactPerson)
    {
        $companyId = $contactPerson->company_id;

        if (config('app.save_action_logs')) {
            $actionLog = new ActionLog;
            $actionLog->company_id = $companyId;
            $actionLog->user_id = auth()->user()->id;
            $actionLog->action_type = 'removed a contact person';
            $actionLog->action_value = $contactPerson->first_name . ' ' . $contactPerson->last_name;
            $actionLog->save();
        }

        $contactPerson->delete();
        if ($request->wantsJson() && $request->source == 'dashboard') {
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
            return Company::with($companyRelationships)->whereId($companyId)->first();
        }

        $status = __('Contact person removed.');
        return $request->wantsJson() ? response(200)->json(['status' => $status]) : back()->with('status', $status);
    }
}
