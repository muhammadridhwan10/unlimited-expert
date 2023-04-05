<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FinancialStatement extends Model
{
    use HasFactory;

    protected $table = 'financial_statement';

    protected $fillable = [
        'project_id', 'm', 'lk', 'cn', 'rp',
        'add1', 'add2', 'add3', 'coa',
        'account', 'unaudited2020', 'audited2021', 'inhouse2022',
        'dr', 'cr', 'audited2022', 'jan',
        'feb', 'mar', 'apr', 'may',
        'jun', 'jul', 'aug', 'sep',
        'oct', 'nov', 'dec', 'triwulan1',
        'triwulan2', 'triwulan3', 'triwulan4'
    ];
}
