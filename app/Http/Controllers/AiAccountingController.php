<?php

namespace App\Http\Controllers;

use App\Models\DocumentAccounting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiAccountingController extends Controller
{
    public function index()
    {
        $documents = DocumentAccounting::orderBy('created_at', 'desc')->paginate(10);
        return view('ai-accounting.index', compact('documents'));
    }

    public function create()
    {
        return view('ai-accounting.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'document_type' => 'required|in:rekening_koran,ebupot',
            'pdf_file' => 'required|file|mimes:pdf|max:10240' // Max 10MB
        ]);

        try {
            $file = $request->file('pdf_file');
            $filename = Str::uuid() . '.pdf';
            $filePath = $file->storeAs('accounting-files/', $filename, 'public');

            $document = DocumentAccounting::create([
                'filename' => $filename,
                'original_filename' => $file->getClientOriginalName(),
                'file_path' => $filePath,
                'document_type' => $request->document_type,
                'status' => 'uploaded'
            ]);

            return redirect()->route('ai-accounting.show', $document->id)
                           ->with('success', 'Document uploaded successfully!');
        } catch (\Exception $e) {
            Log::error('File upload error: ' . $e->getMessage());
            return back()->with('error', 'Failed to upload document. Please try again.');
        }
    }

    public function show($id)
    {
        $document = DocumentAccounting::findOrFail($id);
        return view('ai-accounting.show', compact('document'));
    }

    public function generate(Request $request, $id)
    {
        $document = DocumentAccounting::findOrFail($id);
        
        if ($document->isProcessing()) {
            return response()->json([
                'success' => false,
                'message' => 'Document is already being processed'
            ], 400);
        }

        try {
            // Update status to processing
            $document->update(['status' => 'processing']);

            // Fix callback URL - pastikan menggunakan full URL yang benar
            $callbackUrl = url("api/ai-accounting/{$document->id}/callback");
            
            // Prepare data for N8N workflow
            $n8nData = [
                'document_id' => $document->id,
                'file_path' => storage_path('app/public/' . $document->file_path),
                'document_type' => $document->document_type,
                'prompt' => $document->prompt,
                'callback_url' => $callbackUrl
            ];

            // Log untuk debugging
            Log::info('Sending to N8N webhook:', [
                'document_id' => $document->id,
                'file_path' => $n8nData['file_path'],
                'callback_url' => $callbackUrl,
                'webhook_url' => config('services.n8n.webhook_url')
            ]);

            // Call N8N workflow
            $response = Http::timeout(30)->post(config('services.n8n.webhook_url'), $n8nData);

            if ($response->successful()) {
                $executionId = $response->json('execution_id') ?? null;
                $document->update(['n8n_execution_id' => $executionId]);

                Log::info('N8N webhook successful:', [
                    'document_id' => $document->id,
                    'execution_id' => $executionId,
                    'response' => $response->json()
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Processing started successfully',
                    'execution_id' => $executionId,
                    'callback_url' => $callbackUrl
                ]);
            } else {
                throw new \Exception('N8N webhook failed: ' . $response->body());
            }

        } catch (\Exception $e) {
            Log::error('Document processing error:', [
                'document_id' => $document->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            $document->update([
                'status' => 'failed',
                'error_message' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to start processing: ' . $e->getMessage()
            ], 500);
        }
    }

    public function status($id)
    {
        $document = DocumentAccounting::findOrFail($id);
        
        return response()->json([
            'status' => $document->status,
            'has_data' => !empty($document->extracted_data),
            'error_message' => $document->error_message
        ]);
    }

    public function downloadExcel($id)
    {
        $document = DocumentAccounting::findOrFail($id);
        
        if (!$document->isCompleted() || empty($document->extracted_data)) {
            return back()->with('error', 'No data available for download');
        }

        try {
            $excelData = $this->generateExcelData($document);
            
            $headers = [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Content-Disposition' => 'attachment; filename="' . $document->original_filename . '_extracted.xlsx"',
            ];

            return response()->streamDownload(function() use ($excelData) {
                echo $excelData;
            }, $document->original_filename . '_extracted.xlsx', $headers);

        } catch (\Exception $e) {
            Log::error('Excel generation error: ' . $e->getMessage());
            return back()->with('error', 'Failed to generate Excel file');
        }
    }

    private function generateExcelData($document)
    {
        // Simple CSV format for Excel compatibility
        $data = $document->extracted_data;
        $csv = '';
        
        if ($document->document_type === 'rekening_koran') {
            $csv .= "Tanggal Efektif,Jumlah,Deskripsi Transaksi\n";
            foreach ($data as $row) {
                $csv .= '"' . ($row['tanggal_efektif'] ?? '') . '",';
                $csv .= '"' . ($row['jumlah'] ?? '') . '",';
                $csv .= '"' . ($row['deskripsi_transaksi'] ?? '') . '"' . "\n";
            }
        } else {
            $csv .= "Tanggal Efektif,Nomor Bukti Potong,Masa Pajak,Nama Pemotong,NPWP Pemotong,Kode Objek Pajak,Objek Pajak,Nomor Dokumen,DPP,Pajak Penghasilan\n";
            foreach ($data as $row) {
                $csv .= '"' . ($row['tanggal_efektif'] ?? '') . '",';
                $csv .= '"' . ($row['nomor_bukti_potong'] ?? '') . '",';
                $csv .= '"' . ($row['masa_pajak'] ?? '') . '",';
                $csv .= '"' . ($row['nama_pemotong'] ?? '') . '",';
                $csv .= '"' . ($row['npwp_pemotong'] ?? '') . '",';
                $csv .= '"' . ($row['kode_objek_pajak'] ?? '') . '",';
                $csv .= '"' . ($row['objek_pajak'] ?? '') . '",';
                $csv .= '"' . ($row['nomor_dokumen'] ?? '') . '",';
                $csv .= '"' . ($row['dpp'] ?? '') . '",';
                $csv .= '"' . ($row['pajak_penghasilan'] ?? '') . '"' . "\n";
            }
        }
        
        return $csv;
    }
}
