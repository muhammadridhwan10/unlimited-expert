<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Office extends Model
{

    protected $table = 'office';

    protected $fillable = [
        'name',
        'service_type_id',
        'created_by',
    ];

    public function servicetype()
    {
        return $this->belongsTo('App\Models\ServiceType', 'service_type_id', 'id');
    }
}
