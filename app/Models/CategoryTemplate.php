<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CategoryTemplate extends Model
{

    protected $table = 'category_template';

    protected $fillable = [
        'name',
        'complete',
        'project_id',
        'order',
        'created_by',
    ];

}
