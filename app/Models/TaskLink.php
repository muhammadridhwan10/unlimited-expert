<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskLink extends Model
{
    use HasFactory;

    protected $fillable = [
        'link',
        'task_id',
        'user_id',
        'user_type',
        'created_by',
        'project_id',
    ];

    public function user()
    {
        return $this->hasOne('App\Models\User', 'id', 'created_by');
    }

}
