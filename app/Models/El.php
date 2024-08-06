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
        'status',
        'created_by',
    ];

    public static $status=[
        'Draft' => 'Draft',
        'Revision' => 'Revision',
        'Latest' => 'Latest',
    ];
}
