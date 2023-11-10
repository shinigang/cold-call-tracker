<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CompanyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $companies = Company::latest();
        $keyword = $request->input('keyword');
        if ($keyword) {
            $companies = $companies->where('name', 'like', "%$keyword%")
                ->orWhere('description', 'like', "%$keyword%")
                ->orWhere('industry', 'like', "%$keyword%");
        }

        return Inertia::render('Companies/Index', [
            'companies' => $companies->paginate(10),
            'keyword' => $keyword,
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
        $validated['created_by'] = auth()->user()->id;
        $validated['assigned_caller'] = auth()->user()->id;
        Company::create($validated);
        return redirect(route('companies.index'));
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
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Company $company)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Company $company)
    {
        //
    }
}
