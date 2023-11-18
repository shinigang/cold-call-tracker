<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

use App\Models\Company;
use App\Models\ContactNumber;
use App\Models\ContactPerson;
use App\Exports\CompaniesExport;
use App\Imports\CompaniesImport;

class CompanyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $companies = Company::where('team_id', auth()->user()->current_team_id)->orderBy('name');
        $keyword = $request->input('keyword');
        if ($keyword) {
            $companies = $companies->where('name', 'like', "%$keyword%")
                ->orWhere('description', 'like', "%$keyword%")
                ->orWhere('industry', 'like', "%$keyword%");
        }

        return Inertia::render('Companies/Index', [
            'companies' => $companies->paginate(10),
            // 'keyword' => $keyword,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        return Inertia::render('Companies/Create', []);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|max:1000',
            'industry' => 'nullable|string|max:255',
            'total_employees' => 'nullable|numeric',
            'email' => 'nullable|string|email|max:255',
            'referral_source' => 'nullable|string|max:255',
            'website' => 'nullable|string|max:255',
            'linkedin' => 'nullable|string|max:255',
            'address_street' => 'nullable|string|max:255',
            'address_city' => 'nullable|string|max:255',
            'address_state' => 'nullable|string|max:255',
            'address_country' => 'nullable|string|max:255',
            'address_zipcode' => 'nullable|string|max:255'
        ]);
        $validated['team_id'] = auth()->user()->current_team_id;
        $validated['created_by'] = auth()->user()->id;
        $validated['assigned_caller'] = auth()->user()->id;
        DB::transaction(function () use ($validated) {
            return tap(Company::create($validated), function (Company $company) {
                $this->createContactPersons($company);
                $this->createContactNumbers($company);
            });
        });
        // Company::create($validated);
        return redirect(route('companies.index'));
    }

    private function createContactPersons(Company $company)
    {
        $contact_persons = json_decode(request()->input('contact_persons'), true);
        if (count($contact_persons) > 0) {
            $contact_persons_data = [];
            for ($index = 0; $index < count($contact_persons); $index++) {
                array_push($contact_persons_data, new ContactPerson($contact_persons[$index]));
            }
            $company->contactPersons()->saveMany($contact_persons_data);
        }
    }

    private function createContactNumbers(Company $company)
    {
        $contact_numbers = json_decode(request()->input('contact_numbers'), true);
        if (count($contact_numbers) > 0) {
            $contact_numbers_data = [];
            for ($index = 0; $index < count($contact_numbers); $index++) {
                array_push($contact_numbers_data, new ContactNumber($contact_numbers[$index]));
            }
            $company->contactNumbers()->saveMany($contact_numbers_data);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Company $company)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Company $company)
    {
        return Inertia::render('Companies/Edit', [
            'company' => $company
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Company $company)
    {
        $company->fill($request->all());
        $company->save();

        return redirect()->route('companies.index')->with('message', 'Company Updated Successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Company $company)
    {
        $company->delete();
        return redirect()->route('companies.index')->with('message', 'Company Delete Successfully');
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function export()
    {
        return Excel::download(new CompaniesExport, 'companies.xlsx');
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function import()
    {
        Excel::import(new CompaniesImport, request()->file('file'));
        return redirect()->route('companies.index')->with('message', 'Companies Imported Successfully');
    }
}
