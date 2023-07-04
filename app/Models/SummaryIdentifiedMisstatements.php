<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SummaryIdentifiedMisstatements extends Model
{
    use HasFactory;

    protected $table = 'identified_misstatements';

    protected $fillable = [
        'project_id', 'task_id', 'description', 'period', 'type_misstatement', 'corrected', 
        'assets', 'liability', 'equity','income', 're', 'cause_of_misstatement','managements_reason', 'summary'
    ];
}
