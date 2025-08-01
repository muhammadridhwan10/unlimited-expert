@extends('layouts.admin')

@section('title', 'Documents List')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-file-alt"></i> Documents</h2>
            <a href="{{ route('ai-accounting.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Upload New Document
            </a>
        </div>

        @if($documents->count() > 0)
            <div class="card">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th>File Name</th>
                                    <th>Document Type</th>
                                    <th>Status</th>
                                    <th>Upload Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($documents as $document)
                                <tr>
                                    <td>
                                        <i class="fas fa-file-pdf text-danger"></i>
                                        {{ $document->original_filename }}
                                    </td>
                                    <td>
                                        <span class="badge badge-info">
                                            {{ ucfirst(str_replace('_', ' ', $document->document_type)) }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($document->status === 'uploaded')
                                            <span class="badge badge-secondary status-badge">
                                                <i class="fas fa-clock"></i> Uploaded
                                            </span>
                                        @elseif($document->status === 'processing')
                                            <span class="badge badge-warning status-badge">
                                                <i class="fas fa-spinner fa-spin"></i> Processing
                                            </span>
                                        @elseif($document->status === 'completed')
                                            <span class="badge badge-success status-badge">
                                                <i class="fas fa-check"></i> Completed
                                            </span>
                                        @else
                                            <span class="badge badge-danger status-badge">
                                                <i class="fas fa-times"></i> Failed
                                            </span>
                                        @endif
                                    </td>
                                    <td>{{ $document->created_at->format('M d, Y H:i') }}</td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('ai-accounting.show', $document->id) }}" 
                                               class="btn btn-outline-primary">
                                                <i class="fas fa-eye"></i> View
                                            </a>
                                            
                                            @if($document->isCompleted() && !empty($document->extracted_data))
                                                <a href="{{ route('ai-accounting.download', $document->id) }}" 
                                                   class="btn btn-outline-success">
                                                    <i class="fas fa-download"></i> Excel
                                                </a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="mt-4">
                {{ $documents->links() }}
            </div>
        @else
            <div class="text-center py-5">
                <i class="fas fa-file-upload fa-3x text-muted mb-3"></i>
                <h4 class="text-muted">No documents uploaded yet</h4>
                <p class="text-muted">Upload your first PDF document to get started with AI automation.</p>
                <a href="{{ route('ai-accounting.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Upload Document
                </a>
            </div>
        @endif
    </div>
</div>
@endsection