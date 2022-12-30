<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceType extends Model
{

    protected $table = 'service_type';

    protected $fillable = [
        'name',
        'service_type_code',
        'created_by',
    ];

}
