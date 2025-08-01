{{-- resources/views/documents/show.blade.php --}}
@extends('layouts.admin')

@section('title', 'Document Details')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-file-alt"></i> Document Details</h2>
            <a href="{{ route('ai-accounting.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to List
            </a>
        </div>

        <div class="row">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-info-circle"></i> Document Information</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-sm table-borderless">
                            <tr>
                                <td><strong>File Name:</strong></td>
                                <td>{{ $document->original_filename }}</td>
                            </tr>
                            <tr>
                                <td><strong>Type:</strong></td>
                                <td>
                                    <span class=" badge-info">
                                        {{ ucfirst(str_replace('_', ' ', $document->document_type)) }}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Status:</strong></td>
                                <td>
                                    <span>
                                        @if($document->status === 'uploaded')
                                            <span class=" badge-secondary">
                                                <i class="fas fa-clock"></i> Uploaded
                                            </span>
                                        @elseif($document->status === 'processing')
                                            <span class=" badge-warning">
                                                <i class="fas fa-spinner fa-spin"></i> Processing
                                            </span>
                                        @elseif($document->status === 'completed')
                                            <span class=" badge-success">
                                                <i class="fas fa-check"></i> Completed
                                            </span>
                                        @else
                                            <span class=" badge-danger">
                                                <i class="fas fa-times"></i> Failed
                                            </span>
                                        @endif
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Uploaded:</strong></td>
                                <td>{{ $document->created_at->format('M d, Y H:i') }}</td>
                            </tr>
                            @if($document->n8n_execution_id)
                            <tr>
                                <td><strong>Execution ID:</strong></td>
                                <td><code>{{ $document->n8n_execution_id }}</code></td>
                            </tr>
                            @endif
                        </table>

                        @if($document->error_message)
                        <div class="alert alert-danger mt-3">
                            <strong>Error:</strong><br>
                            {{ $document->error_message }}
                        </div>
                        @endif

                        <div class="mt-3">
                            @if($document->status === 'uploaded')
                                <button type="button" class="btn btn-primary btn-block" id="generateBtn" 
                                        onclick="startProcessing()">
                                    <i class="fas fa-cogs"></i> Generate AI Processing
                                </button>
                            @elseif($document->status === 'processing')
                                <button type="button" class="btn btn-warning btn-block" disabled>
                                    <i class="fas fa-spinner fa-spin"></i> Processing...
                                </button>
                            @elseif($document->isCompleted() && !empty($document->extracted_data))
                                <a href="{{ route('ai-accounting.download', $document->id) }}" 
                                   class="btn btn-success btn-block">
                                    <i class="fas fa-download"></i> Download Excel
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-table"></i> Extracted Data</h5>
                    </div>
                    <div class="card-body" id="extracted-data">
                        @if($document->isCompleted() && !empty($document->extracted_data))
                            <div class="table-responsive">
                                <table class="table table-striped table-sm">
                                    <thead class="thead-light">
                                        <tr>
                                            @if($document->document_type === 'rekening_koran')
                                                <th>Tanggal Efektif</th>
                                                <th>Jumlah</th>
                                                <th>Deskripsi Transaksi</th>
                                            @else
                                                <th>Tanggal Efektif</th>
                                                <th>Nomor Bukti Potong</th>
                                                <th>Masa Pajak</th>
                                                <th>Nama Pemotong</th>
                                                <th>NPWP Pemotong</th>
                                                <th>Kode Objek Pajak</th>
                                                <th>Objek Pajak</th>
                                                <th>Nomor Dokumen</th>
                                                <th>DPP</th>
                                                <th>Pajak Penghasilan</th>
                                            @endif
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($document->extracted_data as $row)
                                        <tr>
                                            @if($document->document_type === 'rekening_koran')
                                                <td>{{ $row['tanggal_efektif'] ?? '-' }}</td>
                                                <td>{{ $row['jumlah'] ?? '-' }}</td>
                                                <td>{{ $row['deskripsi_transaksi'] ?? '-' }}</td>
                                            @else
                                                <td>{{ $row['tanggal_efektif'] ?? '-' }}</td>
                                                <td>{{ $row['nomor_bukti_potong'] ?? '-' }}</td>
                                                <td>{{ $row['masa_pajak'] ?? '-' }}</td>
                                                <td>{{ $row['nama_pemotong'] ?? '-' }}</td>
                                                <td>{{ $row['npwp_pemotong'] ?? '-' }}</td>
                                                <td>{{ $row['kode_objek_pajak'] ?? '-' }}</td>
                                                <td>{{ $row['objek_pajak'] ?? '-' }}</td>
                                                <td>{{ $row['nomor_dokumen'] ?? '-' }}</td>
                                                <td>{{ $row['dpp'] ?? '-' }}</td>
                                                <td>{{ $row['pajak_penghasilan'] ?? '-' }}</td>
                                            @endif
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-5">
                                <i class="fas fa-table fa-3x text-muted mb-3"></i>
                                <h5 class="text-muted">No data extracted yet</h5>
                                <p class="text-muted">
                                    @if($document->status === 'uploaded')
                                        Click "Generate AI Processing" to start extracting data from your document.
                                    @elseif($document->status === 'processing')
                                        AI is currently processing your document. Please wait...
                                    @elseif($document->status === 'failed')
                                        Processing failed. Please try uploading the document again.
                                    @endif
                                </p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Processing Modal -->
<div class="modal fade" id="processingModal" tabindex="-1" role="dialog" data-backdrop="static">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-body text-center py-4">
                <div class="spinner-border text-primary mb-3" role="status">
                    <span class="sr-only">Loading...</span>
                </div>
                <h5>AI Processing in Progress</h5>
                <p class="text-muted mb-0">Please wait while we extract data from your document...</p>
            </div>
        </div>
    </div>
</div>
@endsection

@push('script-page')
<script>
let statusCheckInterval;

function startProcessing() {
    $('#generateBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Starting...');

    $.ajaxSetup({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        }
                    });
    
    $.ajax({
        url: '{{ route("ai-accounting.generate", $document->id) }}',
        method: 'POST',
        success: function(response) {
            if (response.success) {
                $('#processingModal').modal('show');
                updateStatus('processing');
                startStatusCheck();
            } else {
                alert('Error: ' + response.message);
                resetGenerateButton();
            }
        },
        error: function(xhr) {
            var message = xhr.responseJSON?.message || 'An error occurred';
            alert('Error: ' + message);
            resetGenerateButton();
        }
    });
}

function startStatusCheck() {
    statusCheckInterval = setInterval(function() {
        $.ajax({
            url: '{{ route("ai-accounting.status", $document->id) }}',
            method: 'GET',
            success: function(response) {
                if (response.status === 'completed') {
                    clearInterval(statusCheckInterval);
                    $('#processingModal').modal('hide');
                    updateStatus('completed');
                    if (response.has_data) {
                        location.reload(); // Reload to show extracted data
                    }
                } else if (response.status === 'failed') {
                    clearInterval(statusCheckInterval);
                    $('#processingModal').modal('hide');
                    updateStatus('failed');
                    if (response.error_message) {
                        alert('Processing failed: ' + response.error_message);
                    }
                }
            }
        });
    }, 3000); // Check every 3 seconds
}

function updateStatus(status) {
    let badge = '';
    let button = '';
    
    switch(status) {
        case 'processing':
            badge = '<span class="badge badge-warning"><i class="fas fa-spinner fa-spin"></i> Processing</span>';
            button = '<button type="button" class="btn btn-warning btn-block" disabled><i class="fas fa-spinner fa-spin"></i> Processing...</button>';
            break;
        case 'completed':
            badge = '<span class="badge badge-success"><i class="fas fa-check"></i> Completed</span>';
            button = '<a href="{{ route("ai-accounting.download", $document->id) }}" class="btn btn-success btn-block"><i class="fas fa-download"></i> Download Excel</a>';
            break;
        case 'failed':
            badge = '<span class="badge badge-danger"><i class="fas fa-times"></i> Failed</span>';
            button = '<button type="button" class="btn btn-primary btn-block" onclick="startProcessing()"><i class="fas fa-cogs"></i> Retry Processing</button>';
            break;
    }
    
    $('#status-badge').html(badge);
    $('#generateBtn').parent().html(button);
}

function resetGenerateButton() {
    $('#generateBtn').prop('disabled', false).html('<i class="fas fa-cogs"></i> Generate AI Processing');
}

// Clean up interval on page unload
$(window).on('beforeunload', function() {
    if (statusCheckInterval) {
        clearInterval(statusCheckInterval);
    }
});
</script>
@endpush