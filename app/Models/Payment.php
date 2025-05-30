<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'date',
        'amount',
        'account_id',
        'vender_id',
        'description',
        'category_id',
        'payment_method',
        'reference',
        'created_by',
        'add_bill',
        'user_id',
        'tax',
        'currency',
        'kurs',
        'operator',
        'approval',
        'status',
    ];

    public static $statues = [
        'Pending',
        'Approved',
        'Not Approved',
        'Paid',
    ];

    public static $currency =[
        'Rp' => 'Rp',
        '€' => '€',
        'S$' => 'S$',
    ];

    public static $operator =[
        '-' => '-',
        '+' => '+',
    ];

    public function category()
    {
        return $this->hasOne('App\Models\ProductServiceCategory', 'id', 'category_id');
    }

    public function user()
    {
        return $this->hasOne('App\Models\User', 'id', 'user_id');
    }

    public function approval()
    {
        return $this->hasOne('App\Models\User', 'id', 'approval');
    }

    public function vender()
    {
        return $this->hasOne('App\Models\Vender', 'id', 'vender_id');
    }

    public function account()
    {
        return $this->hasOne('App\Models\ChartOfAccount', 'id', 'account_id');
    }


    public function bankAccount()
    {
        return $this->hasOne('App\Models\BankAccount', 'id', 'account_id');
    }

}
