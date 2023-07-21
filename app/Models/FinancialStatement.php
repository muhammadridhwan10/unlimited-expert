<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\SummaryJournalData;

class FinancialStatement extends Model
{
    use HasFactory;

    protected $table = 'financial_statement';

    protected $fillable = [
        'project_id', 'm', 'lk', 'cn', 'rp',
        'add1', 'add2', 'add3', 'coa',
        'account', 'prior_period2', 'prior_period', 'inhouse',
        'dr', 'cr', 'audited', 'jan',
        'feb', 'mar', 'apr', 'may',
        'jun', 'jul', 'aug', 'sep',
        'oct', 'nov', 'dec', 'triwulan1',
        'triwulan2', 'triwulan3', 'triwulan4','notes'
    ];

    public function materiality(){
        return $this->belongsTo('App\Models\Materialitas', 'm', 'code');
    }

    public function summaryjournaldata()
    {
        return $this->belongsTo('App\Models\SummaryJournalData', 'coa', 'coa');
    }

    public function summary()
    {
        return SummaryJournalData::whereIn('coa', explode(',', $this->id))->get();
    }
}
