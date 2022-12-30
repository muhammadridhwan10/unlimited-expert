<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PublicAccountant extends Model
{

    protected $table = 'public_accountant';

    protected $fillable = [
        'office_id',
        'name',
        'client_public_accountant_code',
        'created_by',
    ];

    public function office()
    {
        return $this->belongsTo('App\Models\Office', 'office_id', 'id');
    }

}
