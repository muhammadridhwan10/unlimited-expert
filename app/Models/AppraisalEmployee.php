<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AppraisalEmployee extends Model
{
    use HasFactory;

    protected $table = 'appraisal_employee';

    protected $fillable = [
        'employee_id',
        'project_id',
        'rating',
        'date',
        'remark',
        'created_by',
    ];

    public static $technical = [
        'None',
        'Beginner',
        'Intermediate',
        'Advanced',
        'Expert / Leader',
    ];

    public static $organizational = [
        'None',
        'Beginner',
        'Intermediate',
        'Advanced',
    ];


    public function employees()
    {
        return $this->hasOne('App\Models\Employee', 'id', 'employee_id');
    }

    public function project()
    {
        return $this->hasOne('App\Models\Project', 'id', 'project_id');
    }
}
