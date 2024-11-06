<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class TimesheetExport implements FromArray, WithHeadings
{
    protected $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function array(): array
    {
        return $this->data;
    }

    public function headings(): array
    {
        return [
            'Employee',
            'Date',
            'Project Name',
            'Start Time',
            'End Time',
            'Time',
            'Hours Shortfall',
            'Platform',
            'Project Status',
        ];
    }
}
