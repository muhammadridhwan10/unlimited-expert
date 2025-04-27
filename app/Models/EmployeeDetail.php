<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'pob',
        'marital_status',
        'religion',
        'blood_type',
        'citizenship',
        'last_education',
        'name_of_educational_institution',
        'major',
        'identity_type',
        'identity_number',
        'country_id',
        'state_id',
        'city_id',
        'emergency_contact',
        'emergency_phone',
        'employee_status',
        'doj',
        'branch_id',
        'department_id',
        'designation_id',

    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
