<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\ActionLog;
use App\Models\ContactPerson;
use App\Models\ContactNumber;

use App\Exports\CompaniesExport;
use App\Imports\CompaniesImport;

use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class CompanyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $limit = isset(request()->limit) ? request()->input('limit') : 10;
        // $companies = Company::with(['contactPersons', 'contactNumbers'])->where('team_id', auth()->user()->current_team_id)->orderBy('name');
        $company = Company::search(request()->input('query'), function ($meilisearch, $query, $options) {
            if (isset(request()->call_status) && request()->input('call_status') != "All") {
                $options['filter'][] = 'call_status = "' . request()->input('call_status') . '"';
            }
            return $meilisearch->search($query, $options);
        });

        $companies = $company->query(fn (Builder $query) => $query->with([
            'contactPersons',
            'contactNumbers'
        ]))->orderBy('name')->paginate($limit)->withQueryString();

        return Inertia::render('Companies/Index', [
            'companies' => $companies,
            'search' => [
                'query' => request()->input('query'),
                'call_status' => request()->input('call_status') ?? "All",
            ]
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
    public function store(Request $request)
    {
        $validated = $request->validateWithBag('companyStore', [
            'name' => 'required|string|max:255',
            'description' => 'nullable|max:1000',
            'industry' => 'nullable|string|max:255',
            'total_employees' => 'nullable|integer|min:1',
            'email' => 'required|string|email|max:255',
            'referral_source' => 'nullable|string|max:255',
            'website' => 'nullable|string|active_url|max:255',
            'linkedin' => 'nullable|string|url:https|max:255',
            'address_street' => 'nullable|string|max:255',
            'address_city' => 'nullable|string|max:255',
            'address_state' => 'nullable|string|max:255',
            'address_country' => 'nullable|string|max:255',
            'address_zipcode' => 'nullable|string|max:255',
            'contact_persons.*.name' => 'required|string|max:255',
            'contact_persons.*.email' => 'nullable|string|email|max:255',
            'contact_numbers.*.label' => 'required|string|max:255',
            'contact_numbers.*.number' => 'required|string|max:255',
        ]);
        $validated['team_id'] = auth()->user()->current_team_id;
        $validated['created_by'] = auth()->user()->id;
        // $validated['assigned_caller'] = auth()->user()->id;
        $validated['status'] = 'Lead';
        $validated['call_status'] = 'Unprocessed';
        $company = DB::transaction(function () use ($validated) {
            return tap(Company::create($validated), function (Company $company) {
                $this->createContactPersons($company);
                $this->createContactNumbers($company);

                if (config('app.save_action_logs')) {
                    $actionLog = new ActionLog;
                    $actionLog->company_id = $company->id;
                    $actionLog->user_id = auth()->user()->id;
                    $actionLog->action_type = 'created company';
                    $actionLog->action_value = $company->name;
                    $actionLog->save();
                }

                return $company;
            });
        });

        // if (request()->input('source') == 'dashboard' && $company) {
        //     return redirect('/dashboard?company=' . $company->uuid)->with('company', $company);
        // }

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
            return Company::with($companyRelationships)->whereUuid($company->uuid)->first();
        }

        return redirect(route('companies.index'));
    }

    private function createContactPersons(Company $company)
    {
        $contact_persons = request()->input('contact_persons');
        if (count($contact_persons) > 0) {
            $contact_persons_data = [];
            for ($index = 0; $index < count($contact_persons); $index++) {
                unset($contact_persons[$index]['id']);
                array_push($contact_persons_data, new ContactPerson($contact_persons[$index]));
            }
            $company->contactPersons()->saveMany($contact_persons_data);
        }
    }

    private function createContactNumbers(Company $company)
    {
        $contact_numbers = request()->input('contact_numbers');
        if (count($contact_numbers) > 0) {
            $contact_numbers_data = [];
            for ($index = 0; $index < count($contact_numbers); $index++) {
                unset($contact_numbers[$index]['id']);
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
        $oldValue = $company[$request->fieldName];
        if ($oldValue != $request->input($request->fieldName)) {
            $rules = [
                'name' => 'required|string|max:255',
                'description' => 'nullable|max:1000',
                'industry' => 'nullable|string|max:255',
                'total_employees' => 'nullable|integer|min:1',
                'email' => 'required|string|email|max:255',
                'referral_source' => 'nullable|string|max:255',
                'website' => 'nullable|string|active_url|max:255',
                'linkedin' => 'nullable|string|url:https|max:255',
                'address_street' => 'nullable|string|max:255',
                'address_city' => 'nullable|string|max:255',
                'address_state' => 'nullable|string|max:255',
                'address_country' => 'nullable|string|max:255',
                'address_zipcode' => 'nullable|string|max:255'
            ];
            $request->validateWithBag('companyUpdate', [$request->fieldName => $rules[$request->fieldName]]);

            $company->fill($request->all());
            $company->save();

            if (config('app.save_action_logs')) {
                $actionLog = new ActionLog;
                $actionLog->company_id = $company->id;
                $actionLog->user_id = auth()->user()->id;
                $actionLog->action_type = 'updated company ' . str_replace("_", " ", $request->fieldName);
                $actionLog->action_value = $request->input($request->fieldName);
                $actionLog->action_old_value = $oldValue;
                $actionLog->save();
            }

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
                return Company::with($companyRelationships)->whereId($company->id)->first();
            }
            return redirect()->route('companies.index')->with('message', 'Company Updated Successfully');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Company $company)
    {
        $company->delete();

        return redirect()->back()->with('message', 'Company deleted.');
    }

    /**
     * Restore the specified resource from storage.
     */
    public function restore(Company $company)
    {
        $company->restore();

        return redirect()->back()->with('message', 'Company restored.');
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function export()
    {
        return Excel::download(new CompaniesExport, 'companies.csv', \Maatwebsite\Excel\Excel::CSV);
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function import()
    {
        // Validate the uploaded file
        request()->validate([
            'file' => 'required|file|mimes:csv,txt',
        ]);
        Excel::import(new CompaniesImport, request()->file('file'));
        return redirect()->route('companies.index')->with('message', 'Companies Imported Successfully');
    }
}
