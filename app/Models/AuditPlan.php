<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuditPlan extends Model
{
    use HasFactory;

    protected $table = 'audit_plan';

    protected $fillable = [
        'project_id',
        'task_id',
        'user_id',
        'start_date',
        'created_by',
    ];

    public function project()
    {
        return $this->belongsTo('App\Models\Project', 'project_id', 'id');
    }

    public function task()
    {
        return $this->belongsTo('App\Models\ProjectTask', 'task_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo('App\Models\User', 'user_id', 'id');
    }
}
