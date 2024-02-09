<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name_invoice',
        'position',
        'telp',
        'npwp',
        'address',
        'country',
        'state',
        'city',
        'client_business_sector_id',
        'created_by',
    ];

    public function sector(){
        return $this->hasOne('App\Models\ClientBusinessSector', 'id', 'client_business_sector_id');
    }

}
