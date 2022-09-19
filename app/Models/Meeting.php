<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Meeting extends Model
{
    protected $fillable = [
        'branch_id',
        'department_id',
        'employee_id',
        'title',
        'date',
        'time',
        'note',
        'created_by',
    ];

    public function employee()
    {
        return $this->hasMany(Employee::class, 'employee_id', 'id');
    }
    

}
