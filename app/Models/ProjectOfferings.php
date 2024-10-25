<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectOfferings extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'als_partners',
        'als_manager',
        'als_leader', 
        'als_associate',
        'als_senior_associate',
        'als_intern',
        'rate_partners',
        'rate_leader',
        'rate_manager',
        'rate_senior_associate',
        'rate_associate',
        'rate_intern'
    ];

    public function project()
    {
        return $this->hasOne('App\Models\Project', 'id', 'project_id');
    }
}
