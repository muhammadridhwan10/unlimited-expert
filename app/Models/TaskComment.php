<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaskComment extends Model
{
    protected $fillable = [
        'comment',
        'task_id',
        'user_id',
        'user_type',
        'created_by',
    ];

    public function user()
    {
        return $this->hasOne('App\Models\User', 'id', 'created_by');
    }

    public function userss()
    {
        return $this->belongsTo(User::class, "user_id", "id");
    }

    public function task()
    {
        return $this->belongsTo(ProjectTask::class, 'task_id', 'id');
    }



}
