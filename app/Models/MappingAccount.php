<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MappingAccount extends Model
{
    use HasFactory;

    protected $table = 'mapping_account';

    protected $fillable = [
        'project_id', 'task_id', 'account_code', 'name', 'account_group'
    ];
}
