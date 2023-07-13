<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reimbursment extends Model
{
    use HasFactory;
    
    protected $table = 'reimbursment';

    protected $fillable = [
        'employee_id',
        'client_id',
        'approval',
        'reimbursment_type',
        'date',
        'amount',
        'description',
        'status',
        'reimbursment_image',
        'created_by',
    ];

    public function client()
    {
        return $this->belongsTo(User::class, "client_id", "id");
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class, "employee_id", "id");
    }

    public function approvals()
    {
        return $this->belongsTo(Employee::class, "approval", "user_id");
    }

    public static $reimbursment_type = [
        'Medical Allowance' => 'Medical Allowance',
        'Reimbursment Personal' => 'Reimbursment Personal',
        'Reimbursment Client' => 'Reimbursment Client',
    ];
}
