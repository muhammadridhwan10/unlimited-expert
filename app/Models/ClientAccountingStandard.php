<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClientAccountingStandard extends Model
{

    protected $table = 'client_accounting_standard';

    protected $fillable = [
        'name',
        'created_by',
    ];

}
