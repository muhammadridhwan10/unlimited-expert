<?php
// app/Http/Controllers/Api/DocumentCallbackController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DocumentUpload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DocumentCallbackController extends Controller
{
    public function callback(Request $request, $id)
    {
        try {
            $document = DocumentUpload::findOrFail($id);
            
            $status = $request->input('status', 'failed');
            $extractedData = $request->input('extracted_data', []);
            $errorMessage = $request->input('error_message', null);

            $document->update([
                'status' => $status,
                'extracted_data' => $extractedData,
                'error_message' => $errorMessage
            ]);

            Log::info('Document callback received', [
                'document_id' => $id,
                'status' => $status,
                'data_count' => count($extractedData)
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Callback processed successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Callback processing error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to process callback'
            ], 500);
        }
    }
}