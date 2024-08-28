<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class AttendanceExport implements FromArray, WithHeadings
{
    protected $data;
    protected $dates;

    public function __construct(array $data, array $dates)
    {
        $this->data = $data;
        $this->dates = $dates;
    }

    public function array(): array
    {
        return $this->data;
    }

    public function headings(): array
    {
        // Adding 'Employee' heading with dates
        return array_merge(['Employee'], $this->dates);
    }
}
