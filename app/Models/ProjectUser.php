<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectUser extends Model
{
    protected $fillable = [
        'project_id',
        'user_id',
        'invited_by',
    ];

    public function projectUsers()
    {
        return $this->hasOne('App\Models\User', 'id', 'user_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, "user_id", "id");
    }

    public function inviteby()
    {
        return $this->belongsTo(User::class, "invited_by", "id");
    }

    public function project()
    {
        return $this->belongsTo(Project::class, "project_id", "id");
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'user_id', 'user_id');
    }
    
}


