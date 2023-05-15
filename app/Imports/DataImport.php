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
            [
                'coa' => $row['coa'],
                'project_id' => $this->projectId,
        
            ], // Kolom yang menjadi kunci utama data
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
                'prior_period2' => $row['prior_period2'],
                'prior_period' => $row['prior_period'],
                'inhouse' => $row['inhouse'],
                'dr' => $row['dr'],
                'cr' => $row['cr'],
                'audited' => $row['audited'],
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

