<?php

namespace App\Imports;

use App\Models\Company;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Row;
use Maatwebsite\Excel\Concerns\OnEachRow;

class CompaniesImport implements OnEachRow, WithHeadingRow
{
    public function onRow(Row $row)
    {
        $row = $row->toArray();
        $company = Company::create([
            'team_id' => auth()->user()->current_team_id,
            'name' => $row['name'],
            'description' => $row['description'],
            'industry' => $row['industry'],
            'total_employees' => $row['total_employees'],
            'email' => $row['email'],
            'website' => $row['website'],
            'linkedin' => $row['linkedin'],
            'address_street' => $row['address_street'],
            'address_city' => $row['address_city'],
            'address_state' => $row['address_state'],
            'address_country' => $row['address_country'],
            'address_zipcode' => $row['address_zipcode'],
            'status' => 'Lead',
            'call_status' => 'Unprocessed',
            'created_by' => auth()->user()->id,
        ]);

        if ($row['contact_person_first_name'] && $row['contact_person_last_name']) {
            $company->contactPersons()->create([
                'prefix' => $row['contact_person_prefix'] ?? NULL,
                'first_name' => $row['contact_person_first_name'],
                'middle_name' => $row['contact_person_middle_name'] ?? NULL,
                'last_name' => $row['contact_person_last_name'],
                'suffix' => $row['contact_person_suffix'] ?? NULL,
                'position' => $row['contact_person_position'] ?? NULL,
                'email' => $row['contact_person_email'] ?? NULL
            ]);
        }

        if ($row['telephone']) {
            $company->contactNumbers()->create([
                'label' => 'Telephone',
                'number' => $row['telephone']
            ]);
        }
        if ($row['mobile']) {
            $company->contactNumbers()->create([
                'label' => 'Mobile',
                'number' => $row['mobile']
            ]);
        }
    }
}
