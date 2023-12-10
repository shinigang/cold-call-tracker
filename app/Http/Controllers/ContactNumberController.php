<?php

namespace App\Http\Controllers;

use App\Models\ActionLog;
use App\Models\Company;
use App\Models\ContactNumber;

use Illuminate\Http\Request;

class ContactNumberController extends Controller
{
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'company_id' => 'required',
            'type' => 'nullable|string|max:100',
            'number' => 'required|string|max:255',
            'verified' => 'nullable|boolean',
        ]);
        $number = ContactNumber::create($validated);

        $actionLog = new ActionLog;
        $actionLog->company_id = $number->company_id;
        $actionLog->user_id = auth()->user()->id;
        $actionLog->action_type = 'added a contact number';
        $actionLog->action_value = $number->label . ' ' . $number->number;
        $actionLog->save();

        if ($request->wantsJson() && $request->source == 'dashboard') {
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
                'number' => $number,
                'company' => Company::with($companyRelationships)->whereId($number->company_id)->first()
            ];
        }

        $status = __('Contact number added.');
        return $request->wantsJson() ? response(200)->json(['status' => $status]) : back()->with('status', $status);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ContactNumber $contactNumber)
    {
        $validated = $request->validate([
            'type' => 'nullable|string|max:100',
            'number' => 'required|string|max:255',
            'verified' => 'nullable|boolean',
        ]);
        $contactNumber->update($validated);

        if ($request->wantsJson() && $request->source == 'dashboard') {
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
                'number' => $contactNumber,
                'company' => Company::with($companyRelationships)->whereId($contactNumber->company_id)->first()
            ];
        }

        $status = __('Contact number updated.');
        return $request->wantsJson() ? response(200)->json(['status' => $status]) : back()->with('status', $status);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, ContactNumber $contactNumber)
    {
        $companyId = $contactNumber->company_id;

        $actionLog = new ActionLog;
        $actionLog->company_id = $companyId;
        $actionLog->user_id = auth()->user()->id;
        $actionLog->action_type = 'removed a contact number';
        $actionLog->action_value = $contactNumber->label . ' ' . $contactNumber->number;
        $actionLog->save();

        $contactNumber->delete();
        if ($request->wantsJson() && $request->source == 'dashboard') {
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
            return Company::with($companyRelationships)->whereId($companyId)->first();
        }

        $status = __('Contact number removed.');
        return $request->wantsJson() ? response(200)->json(['status' => $status]) : back()->with('status', $status);
    }
}
