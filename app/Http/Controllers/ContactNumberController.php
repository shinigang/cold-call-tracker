<?php

namespace App\Http\Controllers;

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
            'type' => 'nullable|string|max:100',
            'number' => 'required|string|max:255',
            'verified' => 'nullable|boolean',
        ]);
        $validated['company_id'] = auth()->user()->currentTeam->id;
        ContactNumber::create($validated);
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
        $status = __('Contact number updated.');
        return $request->wantsJson() ? response(200)->json(['status' => $status]) : back()->with('status', $status);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, ContactNumber $contactNumber)
    {
        $contactNumber->delete();
        $status = __('Contact number removed.');
        return $request->wantsJson() ? response(200)->json(['status' => $status]) : back()->with('status', $status);
    }
}
