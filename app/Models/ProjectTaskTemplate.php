<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectTaskTemplate extends Model
{

    protected $table = 'task_template';

    protected $fillable = [
        'category_id',
        'category_template_id',
        'name',
        'description',
        'estimated_hrs',
        'start_date',
        'end_date',
        'priority',
        'priority_color',
        'assign_to',
        'project_id',
        'milestone_id',
        'stage_id',
        'order',
        'created_by',
        'is_favourite',
        'is_complete',
        'marked_at',
        'progress',
    ];

    public function category(){
        return $this->belongsTo('App\Models\CategoryTemplate', 'category_id', 'id');
    }
}
