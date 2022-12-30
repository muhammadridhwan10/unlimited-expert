<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClientBusinessSector extends Model
{

    protected $table = 'client_business_sector';

    protected $fillable = [
        'name',
        'client_business_sector_code',
        'created_by',
    ];

}
