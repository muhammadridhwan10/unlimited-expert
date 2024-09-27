<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Assessment extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'form_type', 
        'user_id',
        'criteria_id', 
        'work_targets',
        'criteria', 
        'performance_achievements',
        'project_name',
        'supervisor_id',
        'self_assessment', 
        'supervisor_assessment', 
        'final_assessment', 
        'comment',
        'performance_progress',     
        'barriers',             
        'follow_up', 
        'advantages',
        'tiers',
        'training_plan',
        'appraisal_id',   
        'year' 
    ];

    public function criterias()
    {
        return $this->belongsTo(Criteria::class, "criteria_id", "id");
    }

    public function user()
    {
        return $this->belongsTo(User::class, "user_id", "id");
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class, "supervisor_id", "id");
    }
}
