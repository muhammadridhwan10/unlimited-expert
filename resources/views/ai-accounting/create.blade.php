@extends('layouts.admin')

@section('title', 'Upload Document')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0"><i class="fas fa-upload"></i> Upload PDF Document</h4>
            </div>
            <div class="card-body">
                <form action="{{ route('ai-accounting.store') }}" method="POST" enctype="multipart/form-data" id="uploadForm">
                    @csrf
                    
                    <div class="form-group">
                        <label for="document_type">Document Type</label>
                        <select name="document_type" id="document_type" class="form-control @error('document_type') is-invalid @enderror" required>
                            <option value="">Select Document Type</option>
                            <option value="rekening_koran" {{ old('document_type') === 'rekening_koran' ? 'selected' : '' }}>
                                Rekening Koran
                            </option>
                            <option value="ebupot" {{ old('document_type') === 'ebupot' ? 'selected' : '' }}>
                                E-Bupot
                            </option>
                        </select>
                        @error('document_type')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">
                            Choose the type of document you want to upload for processing.
                        </small>
                    </div>

                    <div class="form-group">
                        <label for="pdf_file">PDF File</label>
                        <div class="custom-file">
                            <input type="file" name="pdf_file" id="pdf_file" 
                                   class="custom-file-input @error('pdf_file') is-invalid @enderror" 
                                   accept=".pdf" required>
                            <label class="custom-file-label" for="pdf_file">Choose PDF file...</label>
                        </div>
                        @error('pdf_file')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">
                            Maximum file size: 10MB. Only PDF files are allowed.
                        </small>
                    </div>

                    <div id="document-info" class="alert alert-info" style="display: none;">
                        <h6><i class="fas fa-info-circle"></i> AI Processing Information:</h6>
                        <div id="prompt-info"></div>
                    </div>

                    <div class="form-group mb-0">
                        <button type="submit" class="btn btn-primary" id="submitBtn">
                            <i class="fas fa-upload"></i> Upload Document
                        </button>
                        <a href="{{ route('ai-accounting.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to List
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('script-page')
<script>
$(document).ready(function() {
    // Custom file input label update
    $('#pdf_file').on('change', function() {
        var fileName = $(this).val().split('\\').pop();
        $(this).next('.custom-file-label').html(fileName || 'Choose PDF file...');
    });

    // Show document type information
    $('#document_type').on('change', function() {
        var type = $(this).val();
        var info = $('#document-info');
        var promptInfo = $('#prompt-info');
        
        if (type === 'rekening_koran') {
            promptInfo.html(`
                <p><strong>Rekening Koran Processing:</strong></p>
                <p>The AI will extract the following data from your bank statement:</p>
                <ul class="mb-0">
                    <li>Tanggal Efektif (Effective Date)</li>
                    <li>Jumlah (Amount)</li>
                    <li>Deskripsi Transaksi (Transaction Description)</li>
                </ul>
            `);
            info.show();
        } else if (type === 'ebupot') {
            promptInfo.html(`
                <p><strong>E-Bupot Processing:</strong></p>
                <p>The AI will extract the following data from your tax document:</p>
                <ul class="mb-0">
                    <li>Tanggal Efektif (Effective Date)</li>
                    <li>Nomor Bukti Potong (Tax Certificate Number)</li>
                    <li>Masa Pajak (Tax Period)</li>
                    <li>Nama Pemotong (Tax Collector Name)</li>
                    <li>NPWP Pemotong (Tax Collector NPWP)</li>
                    <li>Kode Objek Pajak (Tax Object Code)</li>
                    <li>Objek Pajak (Tax Object)</li>
                    <li>Nomor Dokumen (Document Number)</li>
                    <li>DPP (Tax Base)</li>
                    <li>Pajak Penghasilan (Income Tax)</li>
                </ul>
            `);
            info.show();
        } else {
            info.hide();
        }
    });

    // Form submission
    $('#uploadForm').on('submit', function() {
        $('#submitBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Uploading...');
    });
});
</script>
@endpush