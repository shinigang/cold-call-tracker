<?php

namespace App\Imports;

use App\Models\Company;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class CompaniesImport implements ToModel, WithHeadingRow
{
    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        return new Company([
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
            'created_by' => auth()->user()->id,
        ]);
    }
}
