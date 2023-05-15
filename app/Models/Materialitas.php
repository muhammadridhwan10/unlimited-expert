<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Materialitas extends Model
{
    use HasFactory;

    protected $table = 'materialitas';

    protected $fillable = [
        'name', 'code'
    ];
    
}
