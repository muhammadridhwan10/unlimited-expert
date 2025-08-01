<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentAccounting extends Model
{
    use HasFactory;

    protected $fillable = [
        'filename',
        'original_filename',
        'file_path',
        'document_type',
        'status',
        'extracted_data',
        'error_message',
        'n8n_execution_id'
    ];

    protected $casts = [
        'extracted_data' => 'array'
    ];

    public function isCompleted()
    {
        return $this->status === 'completed';
    }

    public function isFailed()
    {
        return $this->status === 'failed';
    }

    public function isProcessing()
    {
        return $this->status === 'processing';
    }

    public function getPromptAttribute()
    {
        if ($this->document_type === 'rekening_koran') {
            return "You are an AI assistant trained to extract specific data from invoices. Please extract the Tanggal Efektif, Jumlah, Deskripsi Transaksi. Ensure the data is accurate and matches the context provided in the invoice.";
        } elseif ($this->document_type === 'ebupot') {
            return "You are an AI assistant trained to extract specific data from invoices. Please extract the Tanggal Efektif, Nomor bukti potong, Masa Pajak, Nama Pemotong (ambil dari C.3), NPWP Pemotong (ambil dari C.1), Kode Objek Pajak, Objek Pajak, Nomor Dokumen, DPP, Pajak Penghasilan. Ensure the data is accurate and matches the context provided in the invoice.";
        }
        
        return '';
    }
}
