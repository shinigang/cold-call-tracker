<?php

namespace App\Http\Controllers;

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
            'prefix' => 'nullable|string|max:100',
            'first_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name' => 'required|string|max:255',
            'suffix' => 'nullable|string|max:100',
            'position' => 'nullable|string|max:255',
            'verified' => 'nullable|boolean'
        ]);
        $validated['company_id'] = auth()->user()->currentTeam->id;
        ContactPerson::create($validated);
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
        $status = __('Contact person updated.');
        return $request->wantsJson() ? response(200)->json(['status' => $status]) : back()->with('status', $status);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, ContactPerson $contactPerson)
    {
        $contactPerson->delete();
        $status = __('Contact person removed.');
        return $request->wantsJson() ? response(200)->json(['status' => $status]) : back()->with('status', $status);
    }
}
