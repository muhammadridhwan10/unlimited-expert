{{-- resources/views/projects/document-review/show.blade.php --}}
@extends('layouts.admin')
@section('page-title')
    {{__('Work/Document Details')}} - {{$document->document_name}}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
    <li class="breadcrumb-item"><a href="{{route('projects.index')}}">{{__('Project')}}</a></li>
    <li class="breadcrumb-item"><a href="{{route('projects.show', \Crypt::encrypt($project->id))}}">{{$project->project_name}}</a></li>
    <li class="breadcrumb-item"><a href="{{route('projects.document-review.index', $project->id)}}">{{__('Work/Document Review')}}</a></li>
    <li class="breadcrumb-item">{{$document->document_name}}</li>
@endsection

@section('action-btn')
    <div class="float-end">
        <a href="{{ $document->document_link }}" target="_blank" class="btn btn-sm btn-info">
            <i class="ti ti-external-link"></i> {{__('Open Work/Document')}}
        </a>
        
        @if($document->approver_id == Auth::id() || Auth::user()->can('edit project'))
            @if($document->status != 'approved')
                <div class="dropdown d-inline-block">
                    <button class="btn btn-sm btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        {{__('Review Actions')}}
                    </button>
                    <ul class="dropdown-menu">
                        <li>
                            <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#approveModal">
                                <i class="ti ti-check text-success"></i> {{__('Approve')}}
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#reviewModal">
                                <i class="ti ti-eye text-secondary"></i> {{__('Under Review')}}
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#revisionModal">
                                <i class="ti ti-edit text-warning"></i> {{__('Request Revision')}}
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#rejectModal">
                                <i class="ti ti-x text-danger"></i> {{__('Reject')}}
                            </a>
                        </li>
                    </ul>
                </div>
            @endif
        @endif
    </div>
@endsection

@push('css-page')
<style>
.document-info .info-item {
    margin-bottom: 1rem;
}
.comment-item {
    border-left: 3px solid #dee2e6;
    padding-left: 1rem;
    margin-bottom: 1rem;
}
.comment-item.approval {
    border-color: #28a745;
}
.comment-item.rejection {
    border-color: #dc3545;
}
.comment-item.revision {
    border-color: #ffc107;
}
.activity-item {
    border-left: 2px solid #e3e6f0;
    padding-left: 1rem;
    margin-bottom: 1rem;
}
.category-display {
    display: inline-flex;
    align-items: center;
    gap: 8px;
}
.custom-category-indicator {
    background: linear-gradient(45deg, #6c5ce7, #a29bfe);
    color: white;
    font-size: 10px;
    padding: 2px 6px;
    border-radius: 10px;
}
</style>
@endpush

@section('content')
<div class="row">
    <!-- Work/Document Details -->
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-1">{{__('Work/Document Information')}}</h5>
                        <small class="text-muted">{{__('Submitted')}} {{ $document->submission_date->diffForHumans() }}</small>
                    </div>
                    <div>
                        {!! $document->status_badge !!}
                    </div>
                </div>
            </div>
            <div class="card-body document-info">
                <div class="row">
                    <div class="col-md-12">
                        <div class="info-item">
                            <strong class="text-muted">{{__('Title')}}:</strong>
                            <h6 class="mb-0 mt-1">{{ $document->document_name }}</h6>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="info-item">
                            <strong class="text-muted">{{__('Category')}}:</strong>
                            <div class="category-display mt-1">
                                <span class="badge bg-light text-dark">{{ $document->category_name }}</span>
                                @if($document->is_custom_category)
                                    <span class="custom-category-indicator">{{__('Custom')}}</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="info-item">
                            <strong class="text-muted">{{__('Current Status')}}:</strong>
                            <p class="mb-0 mt-1">{!! $document->status_badge !!}</p>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="info-item">
                            <strong class="text-muted">{{__('Submitted By')}}:</strong>
                            <div class="d-flex align-items-center mt-1">
                                <img src="https://ui-avatars.com/api/?name={{ urlencode($document->submitter->name) }}&background=random" 
                                     class="rounded-circle me-2" width="35" height="35">
                                <div>
                                    <div class="fw-bold">{{ $document->submitter->name }}</div>
                                    <small class="text-muted">{{ $document->submitter->email ?? '' }}</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="info-item">
                            <strong class="text-muted">{{__('Approver')}}:</strong>
                            <div class="d-flex align-items-center mt-1">
                                <img src="https://ui-avatars.com/api/?name={{ urlencode($document->approver->name) }}&background=random" 
                                     class="rounded-circle me-2" width="35" height="35">
                                <div>
                                    <div class="fw-bold">{{ $document->approver->name }}</div>
                                    <small class="text-muted">{{ $document->approver->email ?? '' }}</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="info-item">
                            <strong class="text-muted">{{__('Submission Date')}}:</strong>
                            <p class="mb-0 mt-1">
                                {{ $document->submission_date->format('d M Y') }}
                                <small class="text-muted d-block">{{ $document->submission_date->format('l, H:i') }}</small>
                            </p>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="info-item">
                            <strong class="text-muted">{{__('Last Updated')}}:</strong>
                            <p class="mb-0 mt-1">
                                {{ $document->updated_at->format('d M Y H:i') }}
                                <small class="text-muted d-block">{{ $document->updated_at->diffForHumans() }}</small>
                            </p>
                        </div>
                    </div>
                    
                    @if($document->approved_at)
                    <div class="col-md-6">
                        <div class="info-item">
                            <strong class="text-muted">{{__('Approved At')}}:</strong>
                            <p class="mb-0 mt-1">
                                {{ $document->approved_at->format('d M Y H:i') }}
                                <small class="text-muted d-block">{{ $document->approved_at->diffForHumans() }}</small>
                            </p>
                        </div>
                    </div>
                    @endif
                    
                    @if($document->rejected_at)
                    <div class="col-md-6">
                        <div class="info-item">
                            <strong class="text-muted">{{__('Rejected At')}}:</strong>
                            <p class="mb-0 mt-1">
                                {{ $document->rejected_at->format('d M Y H:i') }}
                                <small class="text-muted d-block">{{ $document->rejected_at->diffForHumans() }}</small>
                            </p>
                        </div>
                    </div>
                    @endif

                    <div class="col-md-12">
                        <div class="info-item">
                            <strong class="text-muted">{{__('Contributors')}}:</strong>
                            <div class="mt-2">
                                @foreach($document->contributors as $contributor)
                                    <div class="d-inline-flex align-items-center me-3 mb-2">
                                        <img src="https://ui-avatars.com/api/?name={{ urlencode($contributor->user->name) }}&background=random" 
                                             class="rounded-circle me-2" width="25" height="25">
                                        <span class="badge bg-primary">
                                            {{ $contributor->user->name }}
                                            @if($contributor->role)
                                                <small>({{ $contributor->role }})</small>
                                            @endif
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    @if($document->description)
                    <div class="col-md-12">
                        <div class="info-item">
                            <strong class="text-muted">{{__('Description')}}:</strong>
                            <div class="mt-2 p-3 bg-light rounded">
                                <p class="mb-0">{{ $document->description }}</p>
                            </div>
                        </div>
                    </div>
                    @endif

                    @if($document->rejection_reason)
                    <div class="col-md-12">
                        <div class="info-item">
                            <strong class="text-muted">{{__('Rejection Reason')}}:</strong>
                            <div class="alert alert-danger mt-2">
                                <i class="ti ti-alert-circle me-2"></i>{{ $document->rejection_reason }}
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Work/Document Link -->
                    <div class="col-md-12 mt-3">
                        <div class="card bg-primary">
                            <div class="card-body text-white">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div>
                                        <h6 class="text-white mb-1">
                                            <i class="ti ti-link me-2"></i>{{__('Work/Document Link')}}
                                        </h6>
                                        <small class="text-white-50">{{__('Click to open work/document in new tab')}}</small>
                                        <div class="mt-1">
                                            <small class="text-white-50 d-block" style="word-break: break-all;">
                                                {{ Str::limit($document->document_link, 50) }}
                                            </small>
                                        </div>
                                    </div>
                                    <a href="{{ $document->document_link }}" target="_blank" class="btn btn-light btn-sm">
                                        <i class="ti ti-external-link me-1"></i>{{__('Open')}}
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Comments Section -->
        <div class="card mt-3">
            <div class="card-header">
                <h5><i class="ti ti-message-circle me-2"></i>{{__('Comments & Feedback')}}</h5>
            </div>
            <div class="card-body">
                <div id="comments-list" style="max-height: 400px; overflow-y: auto;">
                    @forelse($document->comments as $comment)
                        <div class="comment-item {{ $comment->type }}" id="comment-{{ $comment->id }}">
                            <div class="d-flex mb-3">
                                <div class="flex-shrink-0">
                                    <img src="https://ui-avatars.com/api/?name={{ urlencode($comment->user->name) }}&background=random" 
                                         class="rounded-circle" width="40" height="40">
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h6 class="mb-0">{{ $comment->user->name }}</h6>
                                            <div class="d-flex align-items-center gap-2">
                                                <span class="badge bg-secondary text-xs">{{ $comment->type_name }}</span>
                                                <small class="text-muted">{{ $comment->created_at->format('d M Y H:i') }}</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mt-2 p-2 bg-light rounded">
                                        <p class="mb-0">{{ $comment->comment }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @if(!$loop->last)<hr>@endif
                    @empty
                        <div class="text-center py-4" id="no-comments-placeholder">
                            <i class="ti ti-message-off text-muted" style="font-size: 2rem;"></i>
                            <p class="text-muted mt-2">{{__('No comments yet')}}</p>
                            <small class="text-muted">{{__('Be the first to add feedback or comments')}}</small>
                        </div>
                    @endforelse
                </div>

                <!-- Add Comment Form -->
                <div class="border-top pt-3 mt-3">
                    <form id="add-comment-form">
                        @csrf
                        <div class="d-flex">
                            <img src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name) }}&background=random" 
                                 class="rounded-circle me-3" width="40" height="40">
                            <div class="flex-grow-1">
                                <textarea name="comment" class="form-control" rows="3" 
                                          placeholder="{{__('Add your comment or feedback...')}}" required></textarea>
                                <div class="d-flex justify-content-between align-items-center mt-2">
                                    <small class="text-muted">
                                        <i class="ti ti-info-circle me-1"></i>
                                        {{__('Your comment will be visible to all contributors and the approver')}}
                                    </small>
                                    <button type="submit" class="btn btn-primary btn-sm">
                                        <i class="ti ti-send me-1"></i>{{__('Add Comment')}}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Activity Log Sidebar -->
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h5><i class="ti ti-history me-2"></i>{{__('Activity Timeline')}}</h5>
            </div>
            <div class="card-body" style="max-height: 500px; overflow-y: auto;">
                @forelse($document->logs as $log)
                    <div class="activity-item">
                        <div class="d-flex">
                            <div class="flex-shrink-0">
                                <div class="theme-avtar bg-primary">
                                    <i class="ti ti-{{ getActivityIcon($log->action) }}"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="mb-1">{{ ucwords(str_replace('_', ' ', $log->action)) }}</h6>
                                <p class="text-sm text-muted mb-1">{{ $log->user->name }}</p>
                                @if($log->details)
                                    <p class="text-sm mb-1">{{ $log->details }}</p>
                                @endif
                                <small class="text-muted">{{ $log->created_at->diffForHumans() }}</small>
                            </div>
                        </div>
                    </div>
                    @if(!$loop->last)<hr class="my-3">@endif
                @empty
                    <div class="text-center py-4">
                        <i class="ti ti-clock-off text-muted" style="font-size: 2rem;"></i>
                        <p class="text-muted mt-2">{{__('No activity yet')}}</p>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Quick Actions Card -->
        @if($document->approver_id == Auth::id() || Auth::user()->can('edit project'))
        <div class="card mt-3">
            <div class="card-header">
                <h6><i class="ti ti-zap me-2"></i>{{__('Review Actions')}}</h6>
            </div>
            <div class="card-body">
                @if($document->status == 'submitted' || $document->status == 'under_review')
                    <div class="d-grid gap-2">
                        <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#approveModal">
                            <i class="ti ti-check me-1"></i>{{__('Approve')}}
                        </button>
                        <button class="btn btn-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#reviewModal">
                            <i class="ti ti-eye me-1"></i>{{__('Under Review')}}
                        </button>
                        <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#revisionModal">
                            <i class="ti ti-edit me-1"></i>{{__('Request Revision')}}
                        </button>
                        <button class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#rejectModal">
                            <i class="ti ti-x me-1"></i>{{__('Reject')}}
                        </button>
                    </div>
                @else
                    <div class="text-center py-3">
                        <p class="text-muted mb-0">{{__('Work/Document has been')}} <strong>{{ strtolower(\App\Models\DocumentReview::$statuses[$document->status]) }}</strong></p>
                        @if($document->status == 'approved')
                            <small class="text-success">
                                <i class="ti ti-check-circle me-1"></i>{{__('Review completed successfully')}}
                            </small>
                        @elseif($document->status == 'rejected')
                            <small class="text-danger">
                                <i class="ti ti-x-circle me-1"></i>{{__('Review completed - rejected')}}
                            </small>
                        @endif
                    </div>
                @endif
            </div>
        </div>
        @endif

        <!-- Work/Document Stats -->
        <div class="card mt-3">
            <div class="card-header">
                <h6><i class="ti ti-chart-bar me-2"></i>{{__('Submission Stats')}}</h6>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-6">
                        <div class="border-end">
                            <h4 class="text-primary">{{ $document->comments->count() }}</h4>
                            <small class="text-muted">{{__('Comments')}}</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <h4 class="text-info">{{ $document->logs->count() }}</h4>
                        <small class="text-muted">{{__('Activities')}}</small>
                    </div>
                </div>
                <hr>
                <div class="row text-center">
                    <div class="col-12">
                        <small class="text-muted">{{__('Time since submission')}}</small>
                        <div class="fw-bold">{{ $document->created_at->diffForHumans() }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Approve Modal -->
<div class="modal fade" id="approveModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title text-white"><i class="ti ti-check me-2"></i>{{__('Approve Work/Document')}}</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('projects.document-review.approve', [$project->id, $document->id]) }}">
                @csrf
                <div class="modal-body">
                    <div class="text-center mb-3">
                        <div class="theme-avtar bg-success mx-auto">
                            <i class="ti ti-check"></i>
                        </div>
                        <h6 class="mt-2">{{__('Approve this work/document?')}}</h6>
                        <p class="text-muted">{{__('This action will mark the work/document as approved and notify all contributors.')}}</p>
                    </div>
                    
                    <div class="form-group">
                        <label>{{__('Approval Comment (Optional)')}}</label>
                        <textarea name="comment" class="form-control" rows="3" placeholder="{{__('Great work! / Approved with minor suggestions... / etc.')}}"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{__('Cancel')}}</button>
                    <button type="submit" class="btn btn-success">
                        <i class="ti ti-check me-1"></i>{{__('Approve Work/Document')}}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Reject Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title text-white"><i class="ti ti-x me-2"></i>{{__('Reject Work/Document')}}</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('projects.document-review.reject', [$project->id, $document->id]) }}">
                @csrf
                <div class="modal-body">
                    <div class="text-center mb-3">
                        <div class="theme-avtar bg-danger mx-auto">
                            <i class="ti ti-x"></i>
                        </div>
                        <h6 class="mt-2">{{__('Reject this work/document?')}}</h6>
                        <p class="text-muted">{{__('Please provide a clear reason for rejection to help the team understand what needs improvement.')}}</p>
                    </div>
                    
                    <div class="form-group mb-3">
                        <label>{{__('Rejection Reason')}} <span class="text-danger">*</span></label>
                        <textarea name="rejection_reason" class="form-control" rows="3" required 
                                  placeholder="{{__('Does not meet quality standards / Missing key requirements / etc.')}}"></textarea>
                    </div>
                    <div class="form-group">
                        <label>{{__('Additional Feedback')}}</label>
                        <textarea name="comment" class="form-control" rows="3" 
                                  placeholder="{{__('Detailed feedback to help improve the work...')}}"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{__('Cancel')}}</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="ti ti-x me-1"></i>{{__('Reject Work/Document')}}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="reviewModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-secondary text-white">
                <h5 class="modal-title text-white"><i class="ti ti-eye me-2"></i>{{__('Under Review')}}</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('projects.document-review.underreview', [$project->id, $document->id]) }}">
                @csrf
                <div class="modal-body">
                    <div class="text-center mb-3">
                        <div class="theme-avtar bg-secondary mx-auto">
                            <i class="ti ti-eye"></i>
                        </div>
                        <h6 class="mt-2">{{__('Document is ready for review')}}</h6>
                        <p class="text-muted">{{__('This document is waiting to be reviewed by the assigned reviewer.')}}</p>
                    </div>
                    
                    <div class="form-group">
                        <label>{{__('Comment')}} <span class="text-danger">*</span></label>
                        <textarea name="comment" class="form-control" rows="4" required 
                                  placeholder="{{__('OK, I will review it soon')}}"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{__('Cancel')}}</button>
                    <button type="submit" class="btn btn-secondary">
                        <i class="ti ti-edit me-1"></i>{{__('Send Notification to Contributor')}}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Revision Modal -->
<div class="modal fade" id="revisionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning text-white">
                <h5 class="modal-title text-white"><i class="ti ti-edit me-2"></i>{{__('Request Revision')}}</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('projects.document-review.revision', [$project->id, $document->id]) }}">
                @csrf
                <div class="modal-body">
                    <div class="text-center mb-3">
                        <div class="theme-avtar bg-warning mx-auto">
                            <i class="ti ti-edit"></i>
                        </div>
                        <h6 class="mt-2">{{__('Request revision for this work/document?')}}</h6>
                        <p class="text-muted">{{__('The work/document will be sent back for revision with your feedback.')}}</p>
                    </div>
                    
                    <div class="form-group">
                        <label>{{__('Revision Request')}} <span class="text-danger">*</span></label>
                        <textarea name="comment" class="form-control" rows="4" required 
                                  placeholder="{{__('Please revise the following sections... / Add more detail to... / Consider changing...')}}"></textarea>
                        <small class="text-muted">{{__('Be specific about what needs to be revised to help the team make the necessary improvements.')}}</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{__('Cancel')}}</button>
                    <button type="submit" class="btn btn-warning">
                        <i class="ti ti-edit me-1"></i>{{__('Request Revision')}}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@php
function getActivityIcon($action) {
    $icons = [
        'submitted' => 'upload',
        'approved' => 'check',
        'rejected' => 'x',
        'revision_required' => 'edit',
        'commented' => 'message',
        'status_changed' => 'refresh',
        'under_review' => 'eye',
        'updated' => 'pencil'
    ];
    return $icons[$action] ?? 'clock';
}
@endphp

@endsection

@push('script-page')
<script>
$(document).ready(function() {
    // Add comment functionality
    $('#add-comment-form').on('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const submitBtn = $(this).find('button[type="submit"]');
        const originalText = submitBtn.html();
        
        // Show loading state
        submitBtn.prop('disabled', true).html('<i class="ti ti-loader animate-spin me-1"></i>{{__("Adding...")}}');
        
        $.ajax({
            url: '{{ route('projects.document-review.comment', [$project->id, $document->id]) }}',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if(response.success) {
                    // Remove no comments placeholder
                    $('#no-comments-placeholder').remove();
                    
                    // Add new comment to the list
                    const commentHtml = `
                        <div class="comment-item general" id="comment-${response.comment.id}">
                            <div class="d-flex mb-3">
                                <div class="flex-shrink-0">
                                    <img src="https://ui-avatars.com/api/?name=${encodeURIComponent(response.comment.user_name)}&background=random" 
                                         class="rounded-circle" width="40" height="40">
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h6 class="mb-0">${response.comment.user_name}</h6>
                                            <div class="d-flex align-items-center gap-2">
                                                <span class="badge bg-secondary text-xs">${response.comment.type}</span>
                                                <small class="text-muted">${response.comment.created_at}</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mt-2 p-2 bg-light rounded">
                                        <p class="mb-0">${response.comment.comment}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <hr>
                    `;
                    
                    // Add to comments container
                    const commentsContainer = $('#comments-list');
                    commentsContainer.append(commentHtml);
                    
                    // Reset form
                    $('#add-comment-form')[0].reset();
                    
                    // Scroll to new comment
                    commentsContainer.animate({
                        scrollTop: commentsContainer[0].scrollHeight
                    }, 500);
                    
                    // Show success message
                    show_toastr('{{__('success')}}', '{{ __("Comment added successfully!")}}');
                }
            },
            error: function(xhr) {
                const error = xhr.responseJSON?.error || '{{__("An error occurred")}}';
                show_toastr('{{__('error')}}', error);
            },
            complete: function() {
                // Restore button
                submitBtn.prop('disabled', false).html(originalText);
            }
        });
    });
    
    // Auto-resize textarea
    $('textarea[name="comment"]').on('input', function() {
        this.style.height = 'auto';
        this.style.height = Math.min(this.scrollHeight, 120) + 'px';
    });
    
    // Modal form submissions
    $('form').on('submit', function(e) {
        if ($(this).closest('.modal').length) {
            const submitBtn = $(this).find('button[type="submit"]');
            const originalText = submitBtn.html();
            
            submitBtn.prop('disabled', true).html('<i class="ti ti-loader animate-spin me-1"></i>{{__("Processing...")}}');
            
            // Don't prevent default, let form submit naturally
        }
    });

    // Initialize tooltips
    $('[data-bs-toggle="tooltip"]').tooltip();

    // Copy link functionality
    $('.copy-link-btn').on('click', function() {
        const link = '{{ $document->document_link }}';
        navigator.clipboard.writeText(link).then(function() {
            show_toastr('{{__('success')}}', '{{__("Link copied to clipboard!")}}');
        });
    });
});
</script>
@endpush