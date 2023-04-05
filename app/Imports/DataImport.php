<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use App\Models\FinancialStatement;

class DataImport implements ToModel, WithHeadingRow
{
    private $projectId;

    public function __construct($projectId)
    {
        $this->projectId = $projectId;
    }
    
    public function model(array $row)
    {
        return FinancialStatement::updateOrCreate(
            ['coa' => $row['coa']], // Kolom yang menjadi kunci utama data
            [
                'project_id' => $this->projectId,
                'm' => $row['m'],
                'lk' => $row['lk'],
                'cn' => $row['cn'],
                'rp' => $row['rp'],
                'add1' => $row['add1'],
                'add2' => $row['add2'],
                'add3' => $row['add3'],
                'account' => $row['account'],
                'unaudited2020' => $row['unaudited2020'],
                'audited2021' => $row['audited2021'],
                'inhouse2022' => $row['inhouse2022'],
                'dr' => $row['dr'],
                'cr' => $row['cr'],
                'audited2022' => $row['audited2022'],
                'jan' => $row['jan'],
                'feb' => $row['feb'],
                'mar' => $row['mar'],
                'apr' => $row['apr'],
                'may' => $row['may'],
                'jun' => $row['jun'],
                'jul' => $row['jul'],
                'aug' => $row['aug'],
                'sep' => $row['sep'],
                'oct' => $row['oct'],
                'nov' => $row['nov'],
                'dec' => $row['dec'],
                'triwulan1' => $row['triwulan1'],
                'triwulan2' => $row['triwulan2'],
                'triwulan3' => $row['triwulan3'],
                'triwulan4' => $row['triwulan4'],
            ]
        );
    }
}

