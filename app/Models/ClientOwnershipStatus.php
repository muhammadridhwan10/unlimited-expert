<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClientOwnershipStatus extends Model
{

    protected $table = 'client_ownership_status';

    protected $fillable = [
        'name',
        'created_by',
    ];

}
