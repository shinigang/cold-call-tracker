<?php

namespace App\Exports;

use App\Models\Company;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;

class CompaniesExport implements FromQuery, WithMapping, WithHeadings
{
    use Exportable;

    public function query()
    {
        return Company::query()->select(
            "companies.name",
            "companies.description",
            "companies.industry",
            "companies.total_employees",
            "companies.email",
            "companies.website",
            "companies.linkedin",
            "companies.address_street",
            "companies.address_city",
            "companies.address_state",
            "companies.address_country",
            "companies.address_zipcode",
            "first_contact_person.prefix as contact_person_prefix",
            "first_contact_person.first_name as contact_person_first_name",
            "first_contact_person.middle_name as contact_person_middle_name",
            "first_contact_person.last_name as contact_person_last_name",
            "first_contact_person.suffix as contact_person_suffix",
            "first_contact_person.position as contact_person_position",
            "first_contact_person.email as contact_person_email",
            "first_contact_number.number as telephone",
            "companies.call_status"
        )
            ->leftJoin(DB::raw("(SELECT * FROM contact_persons LIMIT 1) as first_contact_person"), "first_contact_person.company_id", "=", "companies.id")
            ->leftJoin(DB::raw("(SELECT * FROM contact_numbers WHERE label = 'telephone' LIMIT 1) as first_contact_number"), "first_contact_number.company_id", "=", "companies.id")
            ->orderBy('name');
    }

    public function map($company): array
    {
        return [
            $company->name,
            $company->description,
            $company->industry,
            $company->total_employees,
            $company->email,
            $company->website,
            $company->linkedin,
            $company->address_street,
            $company->address_city,
            $company->address_state,
            $company->address_country,
            $company->address_zipcode,
            $company->contact_person_prefix,
            $company->contact_person_first_name,
            $company->contact_person_middle_name,
            $company->contact_person_last_name,
            $company->contact_person_suffix,
            $company->contact_person_position,
            $company->contact_person_email,
            $company->telephone,
            $company->call_status
        ];
    }

    public function headings(): array
    {
        return ["name", "description", "industry", "total_employees", "email", "website", "linkedin", "address_street", "address_city", "address_state", "address_country", "address_zipcode", "contact_person_prefix", "contact_person_first_name", "contact_person_middle_name", "contact_person_last_name", "contact_person_suffix", "contact_person_position", "contact_person_email", "telephone", "call_status"];
    }
}
