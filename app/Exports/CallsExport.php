<?php

namespace App\Exports;

use App\Models\Call;
use Maatwebsite\Excel\Concerns\FromQuery;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;

class CallsExport implements FromQuery, WithMapping, WithHeadings
{
    use Exportable;

    public function query()
    {
        return Call::query();
    }

    public function map($call): array
    {
        return [
            $call->user->name,
            $call->company->name,
            $call->contact_number,
            Date::dateTimeToExcel($call->called_at),
            $call->status,
            $call->follow_up_at ? Date::dateTimeToExcel($call->follow_up_at) : '',
            $call->appontment_at ? Date::dateTimeToExcel($call->appontment_at) : ''
        ];
    }

    public function headings(): array
    {
        return ["Caller Name", "Company", "Contact Number", "Called At", "Status", "Follow Up At", "Appointment At"];
    }
}
