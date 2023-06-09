<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\FinancialStatement;

class SummaryJournalData extends Model
{
    use HasFactory;

    protected $table = 'summary_journaldata';

    protected $fillable = [
        'project_id', 'coa', 'notes', 'adj_code', 'dr', 'cr'
    ];

    public function financial_statement()
    {
        return FinancialStatement::whereIn('id', explode(',', $this->coa))->get();
    }

    public function lk()
    {
        return $this->belongsTo('App\Models\FinancialStatement', 'coa', 'coa');
    }

    public function financialStatement()
    {
        return $this->belongsTo('App\Models\FinancialStatement', 'coa', 'id');
    }
}
