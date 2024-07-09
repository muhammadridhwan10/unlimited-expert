<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'employee_id',
        'approval',
        'document_type',
        'client_name',
        'email_attention',
        'name_attention',
        'position_attention',
        'address',
        'service_type',
        'period',
        'termin1',
        'termin2',
        'termin3',
        'fee',
        'pph23',
        'no_pic',
        'file',
        'file_feedback',
        'note',
        'status',
        'sender_or_receiver',
        'created_by',
    ];

    public static $document_type=[
        'Proposal' => 'Proposal', 
        'Invoice' => 'Invoice', 
        'EL' => 'EL', 
        'Barcode LAI' => 'Barcode LAI', 
        'Contract Employee' => 'Contract Employee', 
        'Other Letters' => 'Other Letters'
    ];

    public static $to =[
        'Pengirim' => 'Pengirim', 
        'Penerima' => 'Penerima', 
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class, "employee_id", "id");
    }

    public function user()
    {
        return $this->belongsTo(User::class, "approval", "id");
    }

    public function service()
    {
        return $this->belongsTo(ProductServiceCategory::class, "service_type", "id");
    }
}
