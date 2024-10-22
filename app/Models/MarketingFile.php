<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MarketingFile extends Model
{
    use HasFactory;

    protected $fillable = 
    [
        'name',
        'file',
        'created_by',
    ];
}
