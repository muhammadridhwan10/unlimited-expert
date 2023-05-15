<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ValueMaterialitas extends Model
{
    use HasFactory;

    protected $table = 'value_materialitas';

    protected $fillable = [
        'project_id', 'materialitas_id','prior_period2','prior_period','inhouse',
        'audited2022'
    ];

    public function materiality()
    {
        return $this->belongsTo('App\Models\Materialitas', 'materialitas_id', 'id');
    }
}
