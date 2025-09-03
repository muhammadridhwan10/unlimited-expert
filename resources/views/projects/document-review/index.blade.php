{{-- resources/views/projects/document-review/index.blade.php --}}
@extends('layouts.admin')
@section('page-title')
    {{__('Work/Document Review & Approval')}} - {{$project->project_name}}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
    <li class="breadcrumb-item"><a href="{{route('projects.index')}}">{{__('Project')}}</a></li>
    <li class="breadcrumb-item"><a href="{{route('projects.show', \Crypt::encrypt($project->id))}}">{{$project->project_name}}</a></li>
    <li class="breadcrumb-item">{{__('Work/Document Review')}}</li>
@endsection

@section('action-btn')
    <div class="float-end">
        @can('create project')
        <a href="{{ route('projects.document-review.create', $project->id) }}" class="btn btn-sm btn-primary">
            <i class="ti ti-plus"></i> {{__('Submit Work/Document')}}
        </a>
        @endcan
    </div>
@endsection

@push('css-page')
<style>
.category-filter {
    border: 1px solid #dee2e6;
    border-radius: 20px;
    padding: 5px 15px;
    margin: 2px;
    background: #f8f9fa;
    font-size: 12px;
    cursor: pointer;
    transition: all 0.2s;
}
.category-filter:hover,
.category-filter.active {
    background: #007bff;
    color: white;
    border-color: #007bff;
}
.stats-card {
    transition: transform 0.2s;
    cursor: pointer;
}
.stats-card:hover {
    transform: translateY(-2px);
}
.work-type-badge {
    font-size: 10px;
    padding: 2px 6px;
}
</style>
@endpush

@section('content')
<div class="row">
    <div class="col-lg-12">
        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-2">
                <div class="card bg-primary text-white stats-card" data-filter="all">
                    <div class="card-body text-center">
                        <h3 class="text-white">{{ $documents->count() }}</h3>
                        <p class="mb-0">{{__('Total Submissions')}}</p>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card bg-info text-white stats-card" data-filter="submitted">
                    <div class="card-body text-center">
                        <h3 class="text-white">{{ $documents->where('status', 'submitted')->count() }}</h3>
                        <p class="mb-0">{{__('Submitted')}}</p>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card bg-warning text-white stats-card" data-filter="under_review">
                    <div class="card-body text-center">
                        <h3 class="text-white">{{ $documents->where('status', 'under_review')->count() }}</h3>
                        <p class="mb-0">{{__('Under Review')}}</p>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card bg-success text-white stats-card" data-filter="approved">
                    <div class="card-body text-center">
                        <h3 class="text-white">{{ $documents->where('status', 'approved')->count() }}</h3>
                        <p class="mb-0">{{__('Approved')}}</p>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card bg-danger text-white stats-card" data-filter="rejected">
                    <div class="card-body text-center">
                        <h3 class="text-white">{{ $documents->where('status', 'rejected')->count() }}</h3>
                        <p class="mb-0">{{__('Rejected')}}</p>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card bg-secondary text-white stats-card" data-filter="revision_required">
                    <div class="card-body text-center">
                        <h3 class="text-white">{{ $documents->where('status', 'revision_required')->count() }}</h3>
                        <p class="mb-0">{{__('Needs Revision')}}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Category Filter -->
        
        {{-- Fixed Category Filter Section --}}
        @php
            // Get unique categories from the documents (objects, not strings)
            $uniqueCategories = $documents->pluck('category')->filter()->unique('id');
        @endphp
        @if($uniqueCategories->count() > 0)
        <div class="card mb-3">
            <div class="card-body">
                <h6 class="mb-2">{{__('Filter by Category:')}}</h6>
                <div class="d-flex flex-wrap align-items-center">
                    <span class="category-filter active" data-category="all">
                        <i class="ti ti-list me-1"></i>{{__('All Categories')}}
                    </span>
                    @foreach($uniqueCategories as $categoryObj)
                        @if($categoryObj) {{-- Ensure category object exists --}}
                            @php
                                $count = $documents->where('category_id', $categoryObj->id)->count();
                            @endphp
                            <span class="category-filter" 
                                data-category="{{ $categoryObj->id }}" 
                                style="background-color: {{ $categoryObj->color }}20; color: {{ $categoryObj->color }}; border-color: {{ $categoryObj->color }};">
                                <i class="{{ $categoryObj->icon }} me-1"></i>
                                {{ $categoryObj->name }} ({{ $count }})
                                @if(!$categoryObj->is_predefined)
                                    <small class="badge bg-light text-dark ms-1">Custom</small>
                                @endif
                            </span>
                        @endif
                    @endforeach
                </div>
            </div>
        </div>
        @endif

        <!-- Main Table -->
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h5>{{__('Work/Document Submissions')}}</h5>
                    <div class="d-flex align-items-center">
                        <input type="text" id="search-input" class="form-control form-control-sm me-2" 
                               placeholder="{{__('Search by title, submitter, or category...')}}" style="width: 250px;">
                        <select id="status-filter" class="form-select form-select-sm" style="width: 150px;">
                            <option value="">{{__('All Status')}}</option>
                            <option value="submitted">{{__('Submitted')}}</option>
                            <option value="under_review">{{__('Under Review')}}</option>
                            <option value="approved">{{__('Approved')}}</option>
                            <option value="rejected">{{__('Rejected')}}</option>
                            <option value="revision_required">{{__('Needs Revision')}}</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped" id="documents-table">
                        <thead>
                            <tr>
                                <th>{{__('Title & Description')}}</th>
                                <th>{{__('Category')}}</th>
                                <th>{{__('Submitted By')}}</th>
                                <th>{{__('Approver')}}</th>
                                <th>{{__('Contributors')}}</th>
                                <th>{{__('Date')}}</th>
                                <th>{{__('Status')}}</th>
                                <th>{{__('Action')}}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($documents as $document)
                                <tr data-status="{{ $document->status }}" data-category-id="{{ $document->category_id }}">
                                    <td>
                                        <div>
                                            <strong>{{ $document->document_name }}</strong>
                                            @if($document->description)
                                                <br><small class="text-muted">{{ Str::limit($document->description, 60) }}</small>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark work-type-badge">
                                            {{ $document->category_name }}
                                        </span>
                                        @if($document->is_custom_category)
                                            <br><small class="text-muted">({{__('Custom')}})</small>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img src="https://ui-avatars.com/api/?name={{ urlencode($document->submitter->name) }}&background=random" 
                                                class="rounded-circle me-2" width="30" height="30">
                                            <div>
                                                <small class="fw-bold">{{ $document->submitter->name }}</small>
                                                <br><small class="text-muted">{{ $document->submission_date->diffForHumans() }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img src="https://ui-avatars.com/api/?name={{ urlencode($document->approver->name) }}&background=random" 
                                                class="rounded-circle me-2" width="30" height="30">
                                            <small>{{ $document->approver->name }}</small>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-wrap">
                                            @foreach($document->contributors->take(3) as $contributor)
                                                <small class="badge bg-primary me-1 mb-1">{{ $contributor->user->name }}</small>
                                            @endforeach
                                            @if($document->contributors->count() > 3)
                                                <small class="badge bg-secondary">+{{ $document->contributors->count() - 3 }}</small>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <small>{{ $document->submission_date->format('d M Y') }}</small>
                                        <br><small class="text-muted">{{ $document->created_at->format('H:i') }}</small>
                                    </td>
                                    <td>{!! $document->status_badge !!}</td>
                                    <td>
                                        <div class="d-flex">
                                            <div class="action-btn bg-warning ms-1">
                                                <a href="{{ route('projects.document-review.show', [$project->id, $document->id]) }}" 
                                                class="btn btn-sm" data-bs-toggle="tooltip" title="{{__('View Details')}}">
                                                    <i class="ti ti-eye text-white"></i> 
                                                </a>
                                            </div>

                                            <div class="action-btn bg-info ms-1">
                                                <a href="{{ $document->document_link }}" target="_blank" 
                                                class="btn btn-sm" data-bs-toggle="tooltip" title="{{__('Open Work/Document')}}">
                                                    <i class="ti ti-external-link text-white"></i>
                                                </a>
                                            </div>

                                            @if($document->status !== 'approved' && Auth::user()->can('delete project'))
                                                <div class="action-btn bg-danger ms-1">
                                                    {!! Form::open(['method' => 'DELETE', 'route' => ['projects.document-review.destroy', $project->id, $document->id], 'class' => 'delete-form']) !!}
                                                    <a href="#" class="btn btn-sm delete-btn" data-bs-toggle="tooltip" title="{{__('Delete')}}">
                                                        <i class="ti ti-trash text-white"></i>
                                                    </a>
                                                    {!! Form::close() !!}
                                                </div>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr id="no-data-row">
                                    <td colspan="8" class="text-center py-5">
                                        <div class="d-flex flex-column align-items-center">
                                            <i class="ti ti-file-search text-muted mb-3" style="font-size: 3rem;"></i>
                                            <h6>{{__('No work/documents found')}}</h6>
                                            <p class="text-muted mb-3">{{__('Submit your first work/document for review to get started')}}</p>
                                            @can('create project')
                                            <a href="{{ route('projects.document-review.create', $project->id) }}" class="btn btn-sm btn-primary">
                                                <i class="ti ti-plus"></i> {{__('Submit Work/Document')}}
                                            </a>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination or Load More could go here -->
                @if($documents->count() > 0)
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <small class="text-muted">
                        {{__('Showing')}} <span id="showing-count">{{ $documents->count() }}</span> {{__('of')}} <span id="total-count">{{ $documents->count() }}</span> {{__('submissions')}}
                    </small>
                    <div>
                        <button type="button" class="btn btn-sm btn-outline-primary" id="export-btn">
                            <i class="ti ti-download me-1"></i>{{__('Export Data')}}
                        </button>
                    </div>
                </div>
                @endif
            </div>
        </div>

        <!-- Quick Actions Panel for Approvers -->
        @if($documents->where('approver_id', Auth::id())->whereIn('status', ['submitted', 'under_review'])->count() > 0)
        <div class="card mt-4">
            <div class="card-header bg-primary text-white">
                <h6 class="text-white mb-0">
                    <i class="ti ti-clock me-2"></i>{{__('Pending Your Review')}}
                </h6>
            </div>
            <div class="card-body">
                <div class="row">
                    @foreach($documents->where('approver_id', Auth::id())->whereIn('status', ['submitted', 'under_review'])->take(3) as $pendingDoc)
                    <div class="col-md-4">
                        <div class="card border">
                            <div class="card-body p-3">
                                <h6 class="card-title">{{ Str::limit($pendingDoc->document_name, 30) }}</h6>
                                <p class="card-text">
                                    <small class="text-muted">{{__('by')}} {{ $pendingDoc->submitter->name }}</small><br>
                                    <span class="badge bg-light text-dark">{{ $pendingDoc->category_name }}</span>
                                </p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <small class="text-muted">{{ $pendingDoc->submission_date->diffForHumans() }}</small>
                                    <a href="{{ route('projects.document-review.show', [$project->id, $pendingDoc->id]) }}" class="btn btn-sm btn-primary">{{__('Review')}}</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                @if($documents->where('approver_id', Auth::id())->whereIn('status', ['submitted', 'under_review'])->count() > 3)
                <div class="text-center mt-3">
                    <small class="text-muted">{{__('and')}} {{ $documents->where('approver_id', Auth::id())->whereIn('status', ['submitted', 'under_review'])->count() - 3 }} {{__('more awaiting your review')}}</small>
                </div>
                @endif
            </div>
        </div>
        @endif

        <!-- Recent Activity -->
        @php
            $recentActivity = $documents->sortByDesc('updated_at')->take(5);
        @endphp
        @if($recentActivity->count() > 0)
        <div class="card mt-4">
            <div class="card-header">
                <h6 class="mb-0"><i class="ti ti-activity me-2"></i>{{__('Recent Activity')}}</h6>
            </div>
            <div class="card-body">
                @foreach($recentActivity as $activity)
                <div class="d-flex align-items-center mb-3">
                    <div class="flex-shrink-0">
                        @php
                            $statusColor = \App\Models\DocumentReview::$status_colors[$activity->status] ?? 'secondary';
                        @endphp
                        <div class="theme-avtar bg-{{ $statusColor }}">
                            <i class="ti ti-{{ $activity->status == 'approved' ? 'check' : ($activity->status == 'rejected' ? 'x' : 'clock') }}"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h6 class="mb-1">{{ $activity->document_name }}</h6>
                        <p class="mb-0">
                            <small class="text-muted">
                                {{__('by')}} {{ $activity->submitter->name }} • 
                                {{ $activity->updated_at->diffForHumans() }} • 
                                <span class="badge bg-{{ $statusColor }}">{{ \App\Models\DocumentReview::$statuses[$activity->status] }}</span>
                            </small>
                        </p>
                    </div>
                    <div class="flex-shrink-0">
                        <a href="{{ route('projects.document-review.show', [$project->id, $activity->id]) }}" class="btn btn-sm btn-outline-primary">{{__('View')}}</a>
                    </div>
                </div>
                @if(!$loop->last)<hr>@endif
                @endforeach
            </div>
        </div>
        @endif
    </div>
</div>
@endsection

@push('script-page')
<script>
$(document).ready(function() {
    // Search functionality
    $('#search-input').on('keyup', function() {
        filterTable();
    });

    // Status filter
    $('#status-filter').on('change', function() {
        filterTable();
    });

    // Category filter
    $('.category-filter').on('click', function() {
        $('.category-filter').removeClass('active');
        $(this).addClass('active');
        filterTable();
    });

    // Stats card filter
    $('.stats-card').on('click', function() {
        const filter = $(this).data('filter');
        $('#status-filter').val(filter === 'all' ? '' : filter);
        filterTable();
    });

    // Filter table function - FIXED VERSION
    // Tambahkan debug script ini di console browser atau ganti filterTable function dengan ini:

function filterTable() {
    const searchTerm = $('#search-input').val().toLowerCase();
    const statusFilter = $('#status-filter').val();
    const categoryFilter = $('.category-filter.active').data('category');
    
    let visibleCount = 0;
    let totalRows = 0;
    
    $('#documents-table tbody tr').each(function() {
        if ($(this).attr('id') === 'no-data-row' || $(this).attr('id') === 'no-results-row') return;
        
        totalRows++;
        const row = $(this);
        const text = row.text().toLowerCase();
        const status = row.data('status');
        const categoryId = row.data('category-id');
        
        // DEBUG: Log row data
        console.log(`Row ${totalRows}:`, {
            text: text.substring(0, 50) + '...',
            status: status,
            categoryId: categoryId,
            categoryIdType: typeof categoryId
        });
        
        let show = true;
        let hideReasons = [];
        
        // Search filter
        if (searchTerm && !text.includes(searchTerm)) {
            show = false;
            hideReasons.push('search');
        }
        
        // Status filter
        if (statusFilter && status !== statusFilter) {
            show = false;
            hideReasons.push('status');
        }
        
        // Category filter
        if (categoryFilter && categoryFilter !== 'all') {
            if (String(categoryId) !== String(categoryFilter)) {
                show = false;
                hideReasons.push('category');
            }
        }
        
        // DEBUG: Log decision
        console.log(`Row ${totalRows} decision:`, {
            show: show,
            hideReasons: hideReasons
        });
        
        if (show) {
            row.show();
            visibleCount++;
        } else {
            row.hide();
        }
    });
    
    // DEBUG: Final counts
    console.log('=== FILTER RESULTS ===');
    console.log('Total rows processed:', totalRows);
    console.log('Visible count:', visibleCount);
    console.log('====================');
    
    // Update showing count
    $('#showing-count').text(visibleCount);
    
    // Show/hide no data message
    if (visibleCount === 0) {
        if ($('#no-results-row').length === 0) {
            $('#documents-table tbody').append(`
                <tr id="no-results-row">
                    <td colspan="8" class="text-center py-4">
                        <i class="ti ti-search-off text-muted mb-2" style="font-size: 2rem;"></i>
                        <h6>No results found</h6>
                        <p class="text-muted mb-0">Try adjusting your search or filter criteria</p>
                    </td>
                </tr>
            `);
        }
    } else {
        $('#no-results-row').remove();
    }
}

    // Delete confirmation
    $('.delete-btn').on('click', function(e) {
        e.preventDefault();
        const form = $(this).closest('form');
        
        if (confirm('{{__("Are you sure you want to delete this work/document submission?")}}')) {
            form.submit();
        }
    });

    // Export functionality - ENHANCED VERSION
    $('#export-btn').on('click', function() {
        let csv = 'Title,Category,Submitted By,Status,Date\n';
        
        $('#documents-table tbody tr:visible').each(function() {
            if ($(this).attr('id') === 'no-data-row' || $(this).attr('id') === 'no-results-row') return;
            
            const cells = $(this).find('td');
            
            // More robust text extraction
            const title = $(cells[0]).find('strong').text().trim() || 'N/A';
            const category = $(cells[1]).find('.work-type-badge').text().trim() || 'N/A';
            const submitter = $(cells[2]).find('.fw-bold').text().trim() || 
                             $(cells[2]).text().split('\n')[0].trim() || 'N/A';
            const status = $(cells[6]).text().replace(/\s+/g, ' ').trim() || 'N/A';
            const date = $(cells[5]).find('small').first().text().trim() || 'N/A';
            
            // Escape quotes in CSV
            const escapeCSV = (text) => text.replace(/"/g, '""');
            
            csv += `"${escapeCSV(title)}","${escapeCSV(category)}","${escapeCSV(submitter)}","${escapeCSV(status)}","${escapeCSV(date)}"\n`;
        });
        
        // Download CSV
        const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = 'work_document_submissions_' + new Date().toISOString().split('T')[0] + '.csv';
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        window.URL.revokeObjectURL(url);
    });

    // Initialize tooltips
    $('[data-bs-toggle="tooltip"]').tooltip();

    // Enhanced auto-refresh functionality
    let refreshInterval;
    function startAutoRefresh() {
        refreshInterval = setInterval(function() {
            // Only refresh if user hasn't interacted recently
            const lastActivity = localStorage.getItem('lastActivity');
            const now = Date.now();
            
            if (!lastActivity || (now - parseInt(lastActivity)) > 60000) { // 1 minute idle
                // Refresh pending items or reload data
                // You can implement AJAX refresh here
                console.log('Auto-refreshing pending items...');
            }
        }, 30000);
    }

    // Track user activity
    $(document).on('mousemove keypress click', function() {
        localStorage.setItem('lastActivity', Date.now());
    });

    // Start auto-refresh
    startAutoRefresh();

    // Cleanup on page unload
    $(window).on('beforeunload', function() {
        if (refreshInterval) {
            clearInterval(refreshInterval);
        }
    });

    // Initialize filter on page load
    filterTable();
});
</script>
@endpush