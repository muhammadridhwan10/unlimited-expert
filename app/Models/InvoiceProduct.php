<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvoiceProduct extends Model
{
    protected $fillable = [
        'product_id',
        'invoice_id',
        'quantity',
        'tax',
        'discount',
        'total',
    ];

    public function product(){
        return $this->hasOne('App\Models\ProductService', 'id', 'product_id')->first();
    }

    public function products(){
        return $this->hasOne('App\Models\ProductService', 'id', 'product_id');
    }

    public function project(){
        return $this->hasOne('App\Models\Project', 'id', 'product_id')->first();
    }

    public function projects(){
        return $this->hasOne('App\Models\Project', 'id', 'product_id');
    }

    public function tax(){
        return $this->hasOne('App\Models\Tax', 'id', 'tax');
    }

    public function productService()
    {
        return $this->belongsTo(ProductService::class, 'product_id');
    }

    public function invoice(){
        return $this->belongsTo(Invoice::class, "invoice_id", "id");
    }

}
