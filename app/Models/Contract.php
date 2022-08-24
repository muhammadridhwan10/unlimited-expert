<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Contract extends Model
{
    protected $fillable = [
        'client_name',
        'subject',
        'value',
        'type',
        'start_date',
        'end_date',
        'description',
        'created_by',
    ];

    public function clients()
    {
        return $this->hasOne('App\Models\User', 'id', 'client_name');
    }

    public function types()
    {
        return $this->hasOne('App\Models\ContractType', 'id', 'type');
    }
    public static function getContractSummary($contracts)
    {
        $total = 0;

        foreach($contracts as $contract)
        {
            $total += $contract->value;
        }

        return \Auth::user()->priceFormat($total);
    }
}
