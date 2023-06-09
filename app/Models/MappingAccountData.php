<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MappingAccountData extends Model
{
    use HasFactory;

    protected $table = 'mapping_account_data';

    protected $fillable = [
        'account_group', 'code', 'name'
    ];

    public function materialitas()
    {
        return $this->belongsTo('App\Models\Materialitas', 'account_group', 'id');
    }


}
