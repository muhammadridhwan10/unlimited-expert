<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaskChecklist extends Model
{
    protected $fillable = [
        'name',
        'link',
        'description',
        'task_id',
        'user_type',
        'created_by',
        'status',
        'parent_id',
        'project_id',
    ];

    public function subtasks()
    {
        return $this->hasMany(TaskChecklist::class, 'parent_id','id');
    }

    public function user()
    {
        return $this->hasOne('App\Models\User', 'id', 'created_by');
    }
}
