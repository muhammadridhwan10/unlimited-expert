<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class El extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'client_id',
        'el_number',
        'file',
        'created_by',
    ];
}
