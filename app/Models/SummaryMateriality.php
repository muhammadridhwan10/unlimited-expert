<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SummaryMateriality extends Model
{
    use HasFactory;

    protected $table = 'summary_materialitas';

    protected $fillable = [
        'project_id', 'materialitas_id','value_materialitas_id','rate','initialmaterialityom',
        'finalmaterialityom','pmrate','initialmaterialitypm','finalmaterialitypm','terate',
        'initialmaterialityte','finalmaterialityte','description',
    ];

    public function materiality()
    {
        return $this->belongsTo('App\Models\Materialitas', 'materialitas_id', 'id');
    }

    public function value_materialitas()
    {
        return $this->belongsTo('App\Models\ValueMaterialitas', 'value_materialitas_id', 'id');
    }
}
