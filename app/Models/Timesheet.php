<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Timesheet extends Model
{
    protected $fillable = [
        'project_id',
        'task_id',
        'date',
        'time',
        'description',
        'created_by',
        'platform',
        'traker_id',
    ];

    public function timeTrackers()
    {
        return $this->hasMany(TimeTracker::class, 'project_id', 'project_id')
                    ->whereRaw('created_by = ?', [$this->created_by])
                    ->whereRaw('DATE(start_time) = ?', [$this->date]);
    }

    public function project()
    {
        return $this->hasOne('App\Models\Project', 'id', 'project_id');
    }

    public function user()
    {
        return $this->hasOne('App\Models\User', 'id', 'created_by');
    }

    public function task()
    {
        return $this->hasOne('App\Models\ProjectTask', 'id', 'task_id');
    }
}
