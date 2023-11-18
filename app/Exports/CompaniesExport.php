<?php

namespace App\Exports;

use App\Models\Company;
use Maatwebsite\Excel\Concerns\FromCollection;

class CompaniesExport implements FromCollection
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return Company::select('id', 'name', 'description', 'industry', 'total_employees', 'email', 'website', 'linkedin', 'address_street', 'address_city', 'address_state', 'address_country', 'address_zipcode')->get();
    }

    public function headings(): array
    {
        return ["ID", "Company Name", "Description", "Industry", "Total Employees", "Email", "Website", "Linkedin", "Street", "City", "State", "Country", "Zipcode"];
    }
}
