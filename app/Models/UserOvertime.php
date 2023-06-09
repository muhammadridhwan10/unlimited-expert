<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserOvertime extends Model
{
    use HasFactory;

    protected $table = 'user_overtime';

    protected $fillable = 
    [
        'project_id',
        'user_id',
        'start_time',
        'end_time',
        'total_time',
        'start_date',
        'approval',
        'status',
        'notes',
        'created_date'
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class, "user_id", "id");
    }

    public function approvals()
    {
        return $this->belongsTo(Employee::class, "approval", "id");
    }

    public function project()
    {
        return $this->belongsTo(Project::class, "project_id", "id");
    }

}
