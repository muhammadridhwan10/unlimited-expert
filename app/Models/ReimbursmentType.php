<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReimbursmentType extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'amount',
        'created_by',
    ];
}
