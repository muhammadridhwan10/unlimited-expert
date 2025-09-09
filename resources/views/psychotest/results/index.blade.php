@extends('layouts.admin')

@section('page-title')
    {{ __('Psychotest Results') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Psychotest Results') }}</li>
@endsection

@section('action-btn')
    <div class="float-end">
        <a href="{{ route('psychotest-schedule.index') }}" class="btn btn-sm btn-primary">
            <i class="ti ti-arrow-left"></i> {{ __('Back to Schedule') }}
        </a>
        <button type="button" class="btn btn-sm btn-success" onclick="exportToExcel()">
            <i class="ti ti-file-spreadsheet"></i> {{ __('Export Excel') }}
        </button>
    </div>
@endsection

@section('content')
    <div class="row">
        <!-- Statistics Cards -->
        <div class="col-xl-3 col-6">
            <div class="card comp-card">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <h6 class="m-b-20">{{ __('Total Tests') }}</h6>
                            <h3 class="text-primary">{{ $stats['total_tests'] }}</h3>
                        </div>
                        <div class="col-auto">
                            <i class="bg-primary-light ti ti-clipboard-list"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-6">
            <div class="card comp-card">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <h6 class="m-b-20">{{ __('Completed') }}</h6>
                            <h3 class="text-success">{{ $stats['completed_tests'] }}</h3>
                        </div>
                        <div class="col-auto">
                            <i class="bg-success-light ti ti-check"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-6">
            <div class="card comp-card">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <h6 class="m-b-20">{{ __('Completion Rate') }}</h6>
                            <h3 class="text-info">{{ $stats['completion_rate'] }}%</h3>
                        </div>
                        <div class="col-auto">
                            <i class="bg-info-light ti ti-percentage"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-6">
            <div class="card comp-card">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <h6 class="m-b-20">{{ __('Average Score') }}</h6>
                            <h3 class="text-warning">{{ $stats['average_score'] }}%</h3>
                        </div>
                        <div class="col-auto">
                            <i class="bg-warning-light ti ti-star"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-12">
            <div class="card">
                <div class="card-header">
                    <div class="row">
                        <div class="col-lg-6">
                            <h5>{{ __('Psychotest Results') }}</h5>
                            <p class="text-muted mb-0">{{ __('Filter and analyze psychotest results') }}</p>
                        </div>
                        <div class="col-lg-6">
                            <button type="button" class="btn btn-outline-primary btn-sm float-end" 
                                    data-bs-toggle="collapse" data-bs-target="#filterCollapse">
                                <i class="ti ti-filter"></i> {{ __('Advanced Filters') }}
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Advanced Filters -->
                <div class="collapse" id="filterCollapse">
                    <div class="card-body border-bottom">
                        <form method="GET" action="{{ route('psychotest-result.index') }}" id="filterForm">
                            <div class="row g-3">
                                <!-- Search Candidate -->
                                <div class="col-md-3">
                                    <label class="form-label">{{ __('Search Candidate') }}</label>
                                    <input type="text" name="candidate_search" class="form-control" 
                                           placeholder="{{ __('Name or email...') }}" 
                                           value="{{ request('candidate_search') }}">
                                </div>

                                <!-- Job Filter -->
                                <div class="col-md-3">
                                    <label class="form-label">{{ __('Job Position') }}</label>
                                    <select name="job_filter" class="form-select">
                                        <option value="">{{ __('All Positions') }}</option>
                                        @foreach($jobs as $job)
                                            <option value="{{ $job->id }}" {{ request('job_filter') == $job->id ? 'selected' : '' }}>
                                                {{ $job->title }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Status Filter -->
                                <div class="col-md-3">
                                    <label class="form-label">{{ __('Test Status') }}</label>
                                    <select name="status_filter" class="form-select">
                                        <option value="">{{ __('All Status') }}</option>
                                        <option value="completed" {{ request('status_filter') == 'completed' ? 'selected' : '' }}>
                                            {{ __('Completed') }}
                                        </option>
                                        <option value="in_progress" {{ request('status_filter') == 'in_progress' ? 'selected' : '' }}>
                                            {{ __('In Progress') }}
                                        </option>
                                    </select>
                                </div>

                                <!-- Grade Filter -->
                                <div class="col-md-3">
                                    <label class="form-label">{{ __('Grade') }}</label>
                                    <select name="grade_filter" class="form-select">
                                        <option value="">{{ __('All Grades') }}</option>
                                        <option value="A" {{ request('grade_filter') == 'A' ? 'selected' : '' }}>Grade A</option>
                                        <option value="B" {{ request('grade_filter') == 'B' ? 'selected' : '' }}>Grade B</option>
                                        <option value="C" {{ request('grade_filter') == 'C' ? 'selected' : '' }}>Grade C</option>
                                        <option value="D" {{ request('grade_filter') == 'D' ? 'selected' : '' }}>Grade D</option>
                                        <option value="E" {{ request('grade_filter') == 'E' ? 'selected' : '' }}>Grade E</option>
                                        <option value="F" {{ request('grade_filter') == 'F' ? 'selected' : '' }}>Grade F</option>
                                    </select>
                                </div>

                                <!-- Date Range -->
                                <div class="col-md-3">
                                    <label class="form-label">{{ __('Date From') }}</label>
                                    <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label">{{ __('Date To') }}</label>
                                    <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                                </div>

                                <!-- Score Range -->
                                <div class="col-md-3">
                                    <label class="form-label">{{ __('Min Score (%)') }}</label>
                                    <input type="number" name="score_min" class="form-control" 
                                           min="0" max="100" step="1" 
                                           placeholder="0" value="{{ request('score_min') }}">
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label">{{ __('Max Score (%)') }}</label>
                                    <input type="number" name="score_max" class="form-control" 
                                           min="0" max="100" step="1" 
                                           placeholder="100" value="{{ request('score_max') }}">
                                </div>
                            </div>

                            <div class="row mt-3">
                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="ti ti-search"></i> {{ __('Apply Filters') }}
                                    </button>
                                    <a href="{{ route('psychotest-result.index') }}" class="btn btn-outline-secondary">
                                        <i class="ti ti-refresh"></i> {{ __('Reset') }}
                                    </a>
                                    <button type="button" class="btn btn-outline-success" onclick="exportFilteredResults()">
                                        <i class="ti ti-download"></i> {{ __('Export Filtered') }}
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="card-body">
                    <!-- Quick Filters -->
                    <div class="mb-3">
                        <div class="btn-group" role="group">
                            <input type="radio" class="btn-check" name="quickFilter" id="quickAll" value="all" checked>
                            <label class="btn btn-outline-primary btn-sm" for="quickAll">{{ __('All') }}</label>

                            <input type="radio" class="btn-check" name="quickFilter" id="quickCompleted" value="completed">
                            <label class="btn btn-outline-success btn-sm" for="quickCompleted">{{ __('Completed') }}</label>

                            <input type="radio" class="btn-check" name="quickFilter" id="quickGradeA" value="grade_a">
                            <label class="btn btn-outline-info btn-sm" for="quickGradeA">{{ __('Grade A') }}</label>

                            <input type="radio" class="btn-check" name="quickFilter" id="quickLowScore" value="low_score">
                            <label class="btn btn-outline-warning btn-sm" for="quickLowScore">{{ __('Score < 60%') }}</label>

                            <input type="radio" class="btn-check" name="quickFilter" id="quickToday" value="today">
                            <label class="btn btn-outline-secondary btn-sm" for="quickToday">{{ __('Today') }}</label>
                        </div>
                    </div>

                    <div class="table-responsive mt-3">
                        <table class="table" id="resultsTable">
                            <thead>
                                <tr>
                                    <th>
                                        <input type="checkbox" id="selectAll" class="form-check-input">
                                    </th>
                                    <th>{{ __('Candidate') }}</th>
                                    <th>{{ __('Job Position') }}</th>
                                    <th>{{ __('Test Date') }}</th>
                                    <th>{{ __('Status') }}</th>
                                    <th>{{ __('Overall Score') }}</th>
                                    <th>{{ __('Grade') }}</th>
                                    <th>{{ __('Field Breakdown') }}</th>
                                    <th>{{ __('Categories') }}</th>
                                    <th>{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($schedules as $schedule)
                                    <tr data-schedule-id="{{ $schedule->id }}"
                                        data-status="{{ $schedule->status }}"
                                        data-grade="{{ $schedule->result->grade ?? 'N/A' }}"
                                        data-score="{{ $schedule->result->percentage ?? 0 }}"
                                        data-date="{{ $schedule->start_time->format('Y-m-d') }}">
                                        <td>
                                            <input type="checkbox" class="form-check-input row-checkbox" value="{{ $schedule->id }}">
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar avatar-sm me-2">
                                                    <span class="avatar-text">{{ substr($schedule->candidates->name, 0, 2) }}</span>
                                                </div>
                                                <div>
                                                    <h6 class="m-0">{{ $schedule->candidates->name }}</h6>
                                                    <small class="text-muted">{{ $schedule->candidates->email }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-primary">{{ $schedule->candidates->jobs->title ?? 'N/A' }}</span>
                                        </td>
                                        <td>
                                            <div>
                                                <small class="text-muted">{{ $schedule->start_time->format('d M Y') }}</small><br>
                                                <small>{{ $schedule->start_time->format('H:i') }} - {{ $schedule->end_time->format('H:i') }}</small>
                                            </div>
                                        </td>
                                        <td>
                                            @if($schedule->status == 'completed')
                                                <span class="badge bg-success">{{ __('Completed') }}</span>
                                            @elseif($schedule->status == 'in_progress')
                                                <span class="badge bg-warning">{{ __('In Progress') }}</span>
                                            @else
                                                <span class="badge bg-secondary">{{ __(ucfirst($schedule->status)) }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($schedule->result)
                                                <div class="d-flex align-items-center">
                                                    <div class="progress me-2" style="width: 60px; height: 6px;">
                                                        <div class="progress-bar 
                                                            @if($schedule->result->percentage >= 80) bg-success
                                                            @elseif($schedule->result->percentage >= 60) bg-warning
                                                            @else bg-danger
                                                            @endif" 
                                                            style="width: {{ $schedule->result->percentage }}%">
                                                        </div>
                                                    </div>
                                                    <span class="text-sm">{{ $schedule->result->percentage }}%</span>
                                                </div>
                                            @else
                                                <span class="text-muted">{{ __('Not completed') }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($schedule->result)
                                                <span class="badge 
                                                    @if($schedule->result->grade == 'A') bg-success
                                                    @elseif($schedule->result->grade == 'B') bg-info
                                                    @elseif($schedule->result->grade == 'C') bg-warning
                                                    @else bg-danger
                                                    @endif">
                                                    {{ $schedule->result->grade }}
                                                </span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if(!empty($schedule->field_breakdown))
                                                <div class="field-breakdown">
                                                    <div class="d-flex gap-1 mb-1">
                                                        <span class="badge bg-primary" title="Audit">A: {{ $schedule->field_breakdown['audit'] ?? 0 }}%</span>
                                                        <span class="badge bg-success" title="Accounting">AC: {{ $schedule->field_breakdown['accounting'] ?? 0 }}%</span>
                                                        <span class="badge bg-warning" title="Tax">T: {{ $schedule->field_breakdown['tax'] ?? 0 }}%</span>
                                                    </div>
                                                    <div class="progress" style="height: 4px;">
                                                        @php
                                                            $auditWidth = ($schedule->field_breakdown['audit'] ?? 0) / 3;
                                                            $accountingWidth = ($schedule->field_breakdown['accounting'] ?? 0) / 3;
                                                            $taxWidth = ($schedule->field_breakdown['tax'] ?? 0) / 3;
                                                        @endphp
                                                        <div class="progress-bar bg-primary" style="width: {{ $auditWidth }}%" title="Audit {{ $schedule->field_breakdown['audit'] ?? 0 }}%"></div>
                                                        <div class="progress-bar bg-success" style="width: {{ $accountingWidth }}%" title="Accounting {{ $schedule->field_breakdown['accounting'] ?? 0 }}%"></div>
                                                        <div class="progress-bar bg-warning" style="width: {{ $taxWidth }}%" title="Tax {{ $schedule->field_breakdown['tax'] ?? 0 }}%"></div>
                                                    </div>
                                                </div>
                                            @else
                                                <span class="text-muted">{{ __('No field data') }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            @php
                                                $completedSessions = $schedule->sessions()->where('status', 'completed')->count();
                                                $totalSessions = $schedule->sessions()->count();
                                            @endphp
                                            <span class="text-sm">{{ $completedSessions }}/{{ $totalSessions }}</span>
                                            @if($totalSessions > 0)
                                                <div class="progress mt-1" style="height: 4px;">
                                                    <div class="progress-bar bg-primary" style="width: {{ ($completedSessions / $totalSessions) * 100 }}%"></div>
                                                </div>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="dropdown">
                                                <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button" 
                                                        data-bs-toggle="dropdown">
                                                    <i class="ti ti-dots-vertical"></i>
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <li>
                                                        <a class="dropdown-item" href="{{ route('psychotest-result.show', $schedule->id) }}">
                                                            <i class="ti ti-eye me-2"></i>{{ __('View Details') }}
                                                        </a>
                                                    </li>
                                                    @if($schedule->status == 'completed' && $schedule->result)
                                                        <li>
                                                            <a class="dropdown-item" href="{{ route('psychotest-result.export', [$schedule->id, 'pdf']) }}">
                                                                <i class="ti ti-download me-2"></i>{{ __('Export PDF') }}
                                                            </a>
                                                        </li>
                                                    @endif
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li>
                                                        <button class="dropdown-item" onclick="showCandidateModal({{ $schedule->id }})">
                                                            <i class="ti ti-info-circle me-2"></i>{{ __('Quick Info') }}
                                                        </button>
                                                    </li>
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="10" class="text-center">
                                            <div class="py-4">
                                                <i class="ti ti-clipboard-off fs-1 text-muted"></i>
                                                <h5 class="mt-3">{{ __('No Results Found') }}</h5>
                                                <p class="text-muted">{{ __('No psychotest results available with the current filters.') }}</p>
                                                <button class="btn btn-outline-primary" onclick="resetFilters()">
                                                    <i class="ti ti-refresh me-2"></i>{{ __('Reset Filters') }}
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Bulk Actions -->
                    <div id="bulkActions" class="mt-3" style="display: none;">
                        <div class="d-flex align-items-center gap-2">
                            <span class="text-muted">
                                <span id="selectedCount">0</span> {{ __('selected') }}
                            </span>
                            <button class="btn btn-sm btn-success" onclick="bulkExport()">
                                <i class="ti ti-download me-1"></i>{{ __('Export Selected') }}
                            </button>
                            <button class="btn btn-sm btn-info" onclick="compareSelected()">
                                <i class="ti ti-compare me-1"></i>{{ __('Compare') }}
                            </button>
                            <button class="btn btn-sm btn-outline-secondary" onclick="clearSelection()">
                                <i class="ti ti-x me-1"></i>{{ __('Clear') }}
                            </button>
                        </div>
                    </div>

                    <!-- Pagination -->
                    @if($schedules->hasPages())
                        <div class="d-flex justify-content-center mt-4">
                            {{ $schedules->appends(request()->query())->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Info Modal -->
    <div class="modal fade" id="quickInfoModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('Quick Information') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="quickInfoContent">
                    <div class="text-center">
                        <div class="spinner-border" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script-page')
<script>
document.addEventListener('DOMContentLoaded', function() {
    initializeFilters();
    initializeTableFeatures();
    initializeBulkActions();
});

// Initialize filters
function initializeFilters() {
    // Quick filters
    document.querySelectorAll('input[name="quickFilter"]').forEach(radio => {
        radio.addEventListener('change', function() {
            applyQuickFilter(this.value);
        });
    });

    // Advanced filter form
    const filterForm = document.getElementById('filterForm');
    if (filterForm) {
        // Auto-submit on select changes
        filterForm.querySelectorAll('select').forEach(select => {
            select.addEventListener('change', function() {
                if (this.value) {
                    filterForm.submit();
                }
            });
        });
    }
}

// Apply quick filters
function applyQuickFilter(filterType) {
    const table = document.getElementById('resultsTable');
    const rows = table.querySelectorAll('tbody tr[data-schedule-id]');
    const today = new Date().toISOString().split('T')[0];
    
    rows.forEach(row => {
        let show = true;
        
        switch(filterType) {
            case 'completed':
                show = row.dataset.status === 'completed';
                break;
            case 'grade_a':
                show = row.dataset.grade === 'A';
                break;
            case 'low_score':
                show = parseInt(row.dataset.score) < 60;
                break;
            case 'today':
                show = row.dataset.date === today;
                break;
            case 'all':
            default:
                show = true;
                break;
        }
        
        row.style.display = show ? '' : 'none';
    });
    
    updateRowCount();
}

// Initialize table features
function initializeTableFeatures() {
    // Select all functionality
    const selectAll = document.getElementById('selectAll');
    const rowCheckboxes = document.querySelectorAll('.row-checkbox');
    
    if (selectAll) {
        selectAll.addEventListener('change', function() {
            rowCheckboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
            updateBulkActions();
        });
    }
    
    rowCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            updateBulkActions();
            updateSelectAllState();
        });
    });
    
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[title]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
}

// Initialize bulk actions
function initializeBulkActions() {
    updateBulkActions();
}

// Update bulk actions visibility
function updateBulkActions() {
    const selectedCheckboxes = document.querySelectorAll('.row-checkbox:checked');
    const bulkActions = document.getElementById('bulkActions');
    const selectedCount = document.getElementById('selectedCount');
    
    if (selectedCheckboxes.length > 0) {
        bulkActions.style.display = 'block';
        selectedCount.textContent = selectedCheckboxes.length;
    } else {
        bulkActions.style.display = 'none';
    }
}

// Update select all state
function updateSelectAllState() {
    const selectAll = document.getElementById('selectAll');
    const rowCheckboxes = document.querySelectorAll('.row-checkbox');
    const checkedBoxes = document.querySelectorAll('.row-checkbox:checked');
    
    if (checkedBoxes.length === 0) {
        selectAll.checked = false;
        selectAll.indeterminate = false;
    } else if (checkedBoxes.length === rowCheckboxes.length) {
        selectAll.checked = true;
        selectAll.indeterminate = false;
    } else {
        selectAll.checked = false;
        selectAll.indeterminate = true;
    }
}

// Update row count
function updateRowCount() {
    const visibleRows = document.querySelectorAll('#resultsTable tbody tr[data-schedule-id]:not([style*="display: none"])');
    console.log(`Showing ${visibleRows.length} results`);
}

// Export functions
function exportToExcel() {
    const currentFilters = new URLSearchParams(window.location.search);
    const exportUrl = '{{ route("psychotest-result.export-excel") }}?' + currentFilters.toString();
    
    showExportModal(() => {
        window.location.href = exportUrl;
    });
}

function exportFilteredResults() {
    const form = document.getElementById('filterForm');
    const formData = new FormData(form);
    const params = new URLSearchParams(formData);
    
    const exportUrl = '{{ route("psychotest-result.export-excel") }}?' + params.toString();
    
    showExportModal(() => {
        window.location.href = exportUrl;
    });
}

function bulkExport() {
    const selectedIds = getSelectedIds();
    if (selectedIds.length === 0) {
        alert('{{ __("Please select at least one result to export.") }}');
        return;
    }
    
    const exportUrl = '{{ route("psychotest-result.export-excel") }}?selected_ids=' + selectedIds.join(',');
    
    showExportModal(() => {
        window.location.href = exportUrl;
    });
}

// Show export modal with loading
function showExportModal(callback) {
    // Create and show loading modal
    const modal = document.createElement('div');
    modal.className = 'modal fade';
    modal.innerHTML = `
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <div class="modal-body text-center p-4">
                    <div class="spinner-border text-success mb-3" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <h6>{{ __("Generating Excel Report...") }}</h6>
                    <p class="text-muted mb-0">{{ __("Please wait while we prepare your report.") }}</p>
                </div>
            </div>
        </div>
    `;
    
    document.body.appendChild(modal);
    const bootstrapModal = new bootstrap.Modal(modal);
    bootstrapModal.show();
    
    // Execute callback after short delay
    setTimeout(() => {
        callback();
        setTimeout(() => {
            bootstrapModal.hide();
            document.body.removeChild(modal);
        }, 2000);
    }, 500);
}

// Compare selected results
function compareSelected() {
    const selectedIds = getSelectedIds();
    if (selectedIds.length < 2) {
        alert('{{ __("Please select at least 2 results to compare.") }}');
        return;
    }
    
    if (selectedIds.length > 5) {
        alert('{{ __("You can compare maximum 5 results at once.") }}');
        return;
    }
    
    const compareUrl = '{{ route("psychotest-result.compare") }}';
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = compareUrl;
    
    // Add CSRF token
    const csrfToken = document.createElement('input');
    csrfToken.type = 'hidden';
    csrfToken.name = '_token';
    csrfToken.value = '{{ csrf_token() }}';
    form.appendChild(csrfToken);
    
    // Add selected IDs
    selectedIds.forEach(id => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'schedule_ids[]';
        input.value = id;
        form.appendChild(input);
    });
    
    document.body.appendChild(form);
    form.submit();
}

// Get selected row IDs
function getSelectedIds() {
    const selectedCheckboxes = document.querySelectorAll('.row-checkbox:checked');
    return Array.from(selectedCheckboxes).map(checkbox => checkbox.value);
}

// Clear selection
function clearSelection() {
    document.querySelectorAll('.row-checkbox').forEach(checkbox => {
        checkbox.checked = false;
    });
    document.getElementById('selectAll').checked = false;
    updateBulkActions();
}

// Reset filters
function resetFilters() {
    window.location.href = '{{ route("psychotest-result.index") }}';
}

// Show candidate quick info modal
function showCandidateModal(scheduleId) {
    const modal = new bootstrap.Modal(document.getElementById('quickInfoModal'));
    const content = document.getElementById('quickInfoContent');
    
    // Show loading
    content.innerHTML = `
        <div class="text-center">
            <div class="spinner-border" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>
    `;
    
    modal.show();
    
    // Fetch quick info (you'll need to create this endpoint)
    fetch(`{{ url('psychotest-result') }}/${scheduleId}/quick-info`)
        .then(response => response.json())
        .then(data => {
            content.innerHTML = data.html;
        })
        .catch(error => {
            content.innerHTML = `
                <div class="alert alert-danger">
                    <i class="ti ti-alert-circle me-2"></i>
                    {{ __("Failed to load information.") }}
                </div>
            `;
        });
}
</script>
@endpush

@push('css-page')
<style>
.field-breakdown .badge {
    font-size: 0.7rem;
    padding: 0.2rem 0.4rem;
}

.progress-bar {
    transition: width 0.3s ease;
}

.table tbody tr:hover {
    background-color: rgba(0,0,0,0.02);
}

.dropdown-menu {
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

#bulkActions {
    background: #f8f9fa;
    padding: 12px;
    border-radius: 8px;
    border: 1px solid #dee2e6;
}

.btn-check:checked + .btn {
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.avatar-text {
    font-weight: 600;
    text-transform: uppercase;
}

@media (max-width: 768px) {
    .field-breakdown {
        font-size: 0.8rem;
    }
    
    .table-responsive {
        font-size: 0.9rem;
    }
}
</style>
@endpush