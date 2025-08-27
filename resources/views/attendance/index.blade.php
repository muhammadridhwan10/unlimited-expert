@extends('layouts.admin')

@section('page-title')
    {{ __('Manage Attendance List') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Attendance') }}</li>
@endsection

@section('action-btn')
    <div class="float-end">
        @if(!empty($attendanceEmployee) && count($attendanceEmployee) > 0)
        <a href="#" class="btn btn-primary" onclick="saveAsPDF()" data-bs-toggle="tooltip" title="{{ __('Download PDF') }}">
            <i class="ti ti-download me-1"></i>{{ __('Download PDF') }}
        </a>
        <a href="#" class="btn btn-success" onclick="exportToExcel()" data-bs-toggle="tooltip" title="{{ __('Export to Excel') }}">
            <i class="ti ti-file-spreadsheet me-1"></i>{{ __('Export Excel') }}
        </a>
        @endif
    </div>
@endsection

@push('css-page')
<style>
    /* Clean card styling */
    .clean-card {
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        border: 1px solid #e3e6f0;
        margin-bottom: 1.5rem;
    }

    .card-header-clean {
        background: #f8f9fa;
        padding: 1rem 1.5rem;
        border-bottom: 1px solid #dee2e6;
        border-radius: 8px 8px 0 0;
    }

    .card-header-clean h6 {
        margin: 0;
        font-weight: 600;
        color: #495057;
    }

    /* Summary cards */
    .summary-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 12px;
        padding: 1.5rem;
        margin-bottom: 1rem;
        text-align: center;
    }

    .summary-card.total-records {
        background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
    }

    .summary-card.present {
        background: linear-gradient(135deg, #66bb6a 0%, #4caf50 100%);
    }

    .summary-card.absent {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    }

    .summary-card.late {
        background: linear-gradient(135deg, #ffa726 0%, #ff9800 100%);
    }

    .summary-number {
        font-size: 2rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
    }

    .summary-label {
        font-size: 0.9rem;
        opacity: 0.9;
        font-weight: 500;
    }

    /* Filter section styling */
    .filter-section {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
    }

    .filter-type-radio {
        display: flex;
        gap: 1rem;
        margin-bottom: 1rem;
    }

    .radio-option {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem 1rem;
        background: white;
        border: 2px solid #e3e6f0;
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .radio-option:hover {
        border-color: #007bff;
        background: #f8f9fa;
    }

    .radio-option.active {
        border-color: #007bff;
        background: #e3f2fd;
        color: #1976d2;
    }

    .radio-option input[type="radio"] {
        margin: 0;
    }

    /* Table styling */
    .table-clean {
        margin: 0;
        background: white;
        font-size: 0.875rem;
    }

    .table-clean thead th {
        background: #f8f9fa;
        border-top: none;
        border-bottom: 2px solid #dee2e6;
        font-weight: 600;
        color: #495057;
        padding: 1rem 0.75rem;
        white-space: nowrap;
        vertical-align: middle;
    }

    .table-clean tbody td {
        padding: 1rem 0.75rem;
        vertical-align: middle;
        border-top: 1px solid #dee2e6;
    }

    .table-clean tbody tr:hover {
        background-color: #f8f9fa;
    }

    /* Employee info styling */
    .employee-info {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .employee-avatar {
        width: 40px;
        height: 40px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        font-size: 0.875rem;
        flex-shrink: 0;
    }

    .employee-details h6 {
        margin: 0;
        font-weight: 600;
        color: #495057;
    }

    .employee-details small {
        color: #6c757d;
    }

    /* Status badges */
    .status-badge {
        padding: 0.375rem 0.75rem;
        border-radius: 20px;
        font-weight: 600;
        font-size: 0.8rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .status-present {
        background: linear-gradient(135deg, #66bb6a 0%, #4caf50 100%);
        color: white;
    }

    .status-absent {
        background: linear-gradient(135deg, #f44336 0%, #d32f2f 100%);
        color: white;
    }

    .status-leave {
        background: linear-gradient(135deg, #ff9800 0%, #f57c00 100%);
        color: white;
    }

    /* Time display */
    .time-display {
        font-family: 'Courier New', monospace;
        font-weight: 600;
        color: #495057;
        background: #f8f9fa;
        padding: 0.25rem 0.5rem;
        border-radius: 4px;
        font-size: 0.85rem;
    }

    .work-duration {
        background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
        color: #1976d2;
    }

    .late-time {
        background: linear-gradient(135deg, #ffebee 0%, #ffcdd2 100%);
        color: #d32f2f;
    }

    .overtime {
        background: linear-gradient(135deg, #e8f5e8 0%, #c8e6c9 100%);
        color: #2e7d32;
    }

    /* Action buttons */
    .action-buttons {
        display: flex;
        gap: 0.5rem;
    }

    .action-btn {
        width: 35px;
        height: 35px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s ease;
        border: none;
        cursor: pointer;
    }

    .action-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.15);
    }

    .action-btn.edit {
        background: linear-gradient(135deg, #2196f3 0%, #1976d2 100%);
        color: white;
    }

    .action-btn.delete {
        background: linear-gradient(135deg, #f44336 0%, #d32f2f 100%);
        color: white;
    }

    /* Form styling */
    .form-label {
        font-weight: 600;
        color: #495057;
        margin-bottom: 0.5rem;
        font-size: 0.875rem;
    }

    .form-select, .form-control {
        border: 1px solid #ced4da;
        border-radius: 4px;
        padding: 0.5rem 0.75rem;
        font-size: 0.875rem;
        transition: border-color 0.2s ease;
    }

    .form-select:focus, .form-control:focus {
        border-color: #007bff;
        box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
    }

    /* Empty state */
    .empty-state {
        text-align: center;
        padding: 4rem 2rem;
        color: #6c757d;
    }

    .empty-state-icon {
        font-size: 4rem;
        color: #cbd5e0;
        margin-bottom: 1rem;
    }

    .empty-state h5 {
        color: #4a5568;
        margin-bottom: 0.5rem;
    }

    /* Loading spinner */
    .loading-spinner {
        display: none;
        text-align: center;
        padding: 2rem;
    }

    .spinner {
        width: 40px;
        height: 40px;
        border: 4px solid #f3f3f3;
        border-top: 4px solid #007bff;
        border-radius: 50%;
        animation: spin 1s linear infinite;
        margin: 0 auto 1rem;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    /* Pagination styling */
    .pagination {
        justify-content: center;
        margin-top: 2rem;
    }

    .pagination .page-link {
        border: 1px solid #dee2e6;
        color: #495057;
        padding: 0.5rem 0.75rem;
        margin: 0 2px;
        border-radius: 6px;
        transition: all 0.2s ease;
    }

    .pagination .page-link:hover {
        background: #f8f9fa;
        border-color: #007bff;
        color: #007bff;
    }

    .pagination .page-item.active .page-link {
        background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
        border-color: #007bff;
    }

    /* Responsive design */
    @media (max-width: 768px) {
        .summary-number {
            font-size: 1.5rem;
        }
        
        .card-header-clean {
            padding: 0.75rem 1rem;
        }

        .table-clean {
            font-size: 0.75rem;
        }

        .table-clean thead th,
        .table-clean tbody td {
            padding: 0.5rem 0.25rem;
        }

        .employee-info {
            flex-direction: column;
            gap: 0.5rem;
            text-align: center;
        }

        .employee-avatar {
            width: 35px;
            height: 35px;
        }

        .action-buttons {
            justify-content: center;
        }

        .filter-type-radio {
            flex-direction: column;
            gap: 0.5rem;
        }

        /* Mobile table responsiveness */
        .table-responsive {
            border: none;
        }

        .table-clean thead {
            display: none;
        }

        .table-clean, 
        .table-clean tbody, 
        .table-clean tr, 
        .table-clean td {
            display: block;
            width: 100%;
        }

        .table-clean tr {
            border: 1px solid #dee2e6;
            margin-bottom: 1rem;
            padding: 1rem;
            border-radius: 8px;
            background: white;
        }

        .table-clean td {
            text-align: left;
            border: none;
            padding: 0.5rem 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .table-clean td:before {
            content: attr(data-label);
            font-weight: 600;
            color: #495057;
            flex: 1;
        }

        .table-clean td > * {
            flex: 1;
            text-align: right;
        }
    }

    /* Print styles */
    @media print {
        .no-print {
            display: none !important;
        }
        
        .clean-card {
            box-shadow: none;
            border: 1px solid #ddd;
        }
    }

    .fade-in {
        animation: fadeIn 0.5s ease-in;
    }

    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }
</style>
@endpush

@section('content')
    <!-- Filter Section -->
    <div class="row no-print">
        <div class="col-12">
            <div class="clean-card">
                <div class="card-header-clean">
                    <h6><i class="ti ti-filter me-2"></i>{{ __('Attendance Filters') }}</h6>
                </div>
                <div class="card-body">
                    {{ Form::open(['route' => ['attendanceemployee.index'], 'method' => 'get', 'id' => 'attendanceemployee_filter']) }}
                    {{ Form::hidden('export_excel', 0, ['id' => 'export_excel']) }}
                    
                    <!-- Type Selection -->
                    <div class="row mb-3">
                        <div class="col-12">
                            <label class="form-label">{{ __('Report Type') }}</label>
                            <div class="filter-type-radio">
                                <div class="radio-option {{ (!isset($_GET['type']) || $_GET['type'] == 'monthly') ? 'active' : '' }}">
                                    <input type="radio" id="monthly" value="monthly" name="type" 
                                           class="form-check-input" 
                                           {{ (!isset($_GET['type']) || $_GET['type'] == 'monthly') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="monthly">{{ __('Monthly View') }}</label>
                                </div>
                                <div class="radio-option {{ (isset($_GET['type']) && $_GET['type'] == 'daily') ? 'active' : '' }}">
                                    <input type="radio" id="daily" value="daily" name="type" 
                                           class="form-check-input" 
                                           {{ (isset($_GET['type']) && $_GET['type'] == 'daily') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="daily">{{ __('Date Range') }}</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row g-3">
                        <!-- Month selector (for monthly view) -->
                        <div class="col-lg-3 col-md-6 month">
                            <label class="form-label">{{ __('Month') }}</label>
                            {{ Form::month('month', isset($_GET['month']) ? $_GET['month'] : date('Y-m'), ['class' => 'form-control']) }}
                        </div>

                        <!-- Date range (for daily view) -->
                        <div class="col-lg-3 col-md-6 date">
                            <label class="form-label">{{ __('Start Date') }}</label>
                            {{ Form::date('start_date', isset($_GET['start_date']) ? $_GET['start_date'] : null, ['class' => 'form-control']) }}
                        </div>

                        <div class="col-lg-3 col-md-6 date">
                            <label class="form-label">{{ __('End Date') }}</label>
                            {{ Form::date('end_date', isset($_GET['end_date']) ? $_GET['end_date'] : null, ['class' => 'form-control']) }}
                        </div>

                        <!-- Branch filter -->
                        <div class="col-lg-3 col-md-6">
                            <label class="form-label">{{ __('Branch') }}</label>
                            {{ Form::select('branch_id', $branch, isset($_GET['branch_id']) ? $_GET['branch_id'] : '', ['class' => 'form-select']) }}
                        </div>

                        <!-- Employee filter -->
                        <div class="col-lg-6 col-md-8">
                            <label class="form-label">{{ __('Employee') }}</label>
                            {{ Form::select('employee_id', $employees, isset($_GET['employee_id']) ? $_GET['employee_id'] : '', ['class' => 'form-select select2']) }}
                        </div>

                        <!-- Entries per page -->
                        <div class="col-lg-3 col-md-4">
                            <label class="form-label">{{ __('Show Entries') }}</label>
                            {{ Form::select('show_entries', [10 => '10', 25 => '25', 50 => '50', 100 => '100'], request('show_entries', 50), ['class' => 'form-select']) }}
                        </div>

                        <!-- Action buttons -->
                        <div class="col-lg-3 col-md-12 d-flex align-items-end">
                            <div class="btn-group w-100" role="group">
                                <button type="submit" class="btn btn-primary" id="generateReport">
                                    <i class="ti ti-search me-1"></i>{{ __('Search') }}
                                </button>
                                <a href="{{ route('attendanceemployee.index') }}" class="btn btn-secondary">
                                    <i class="ti ti-refresh me-1"></i>{{ __('Reset') }}
                                </a>
                            </div>
                        </div>
                    </div>
                    {{ Form::close() }}
                </div>
            </div>
        </div>
    </div>

    <!-- Loading Spinner -->
    <div class="loading-spinner" id="loadingSpinner">
        <div class="spinner"></div>
        <p>{{ __('Loading attendance data, please wait...') }}</p>
    </div>

    <!-- Summary Cards -->
    @if(!empty($attendanceEmployee) && count($attendanceEmployee) > 0)
    <div class="row fade-in">
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="summary-card total-records">
                <div class="summary-number">{{ $attendanceEmployee->total() }}</div>
                <div class="summary-label">{{ __('Total Records') }}</div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="summary-card present">
                <div class="summary-number">{{ $attendanceEmployee->where('status', 'Present')->count() }}</div>
                <div class="summary-label">{{ __('Present Today') }}</div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="summary-card absent">
                <div class="summary-number">{{ $attendanceEmployee->where('status', 'Absent')->count() }}</div>
                <div class="summary-label">{{ __('Absent Today') }}</div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="summary-card late">
                <div class="summary-number">{{ $attendanceEmployee->where('late', '!=', '00:00:00')->count() }}</div>
                <div class="summary-label">{{ __('Late Arrivals') }}</div>
            </div>
        </div>
    </div>
    @endif

    <!-- Main Content -->
    <div id="printableArea">
        @if(!empty($attendanceEmployee) && count($attendanceEmployee) > 0)
        <div class="row fade-in">
            <div class="col-12">
                <div class="clean-card">
                    <div class="card-header-clean">
                        <h6><i class="ti ti-calendar-check me-2"></i>{{ __('Attendance Records') }}</h6>
                        <small class="text-muted">
                            {{ __('Showing') }} {{ $attendanceEmployee->firstItem() }} {{ __('to') }} {{ $attendanceEmployee->lastItem() }} 
                            {{ __('of') }} {{ $attendanceEmployee->total() }} {{ __('records') }}
                        </small>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-clean">
                                <thead>
                                    <tr>
                                        @if (\Auth::user()->type != 'employee')
                                            <th>{{ __('Employee') }}</th>
                                        @endif
                                        <th>{{ __('Date') }}</th>
                                        <th>{{ __('Status') }}</th>
                                        <th>{{ __('Clock In') }}</th>
                                        <th>{{ __('Clock Out') }}</th>
                                        <th>{{ __('Total Work') }}</th>
                                        <th>{{ __('Late') }}</th>
                                        <th>{{ __('Location') }}</th>
                                        <th>{{ __('Early Leave') }}</th>
                                        <th>{{ __('Overtime') }}</th>
                                        @if (Gate::check('edit attendance') || Gate::check('delete attendance'))
                                            <th>{{ __('Action') }}</th>
                                        @endif
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($attendanceEmployee as $attendance)
                                        <tr>
                                            @if (\Auth::user()->type != 'employee')
                                                <td data-label="Employee">
                                                    <div class="employee-info">
                                                        <div class="employee-avatar">
                                                            {{ !empty($attendance->employee) ? substr($attendance->employee->name, 0, 1) : 'N/A' }}
                                                        </div>
                                                        <div class="employee-details">
                                                            <h6>{{ !empty($attendance->employee) ? $attendance->employee->name : 'N/A' }}</h6>
                                                            <small>{{ !empty($attendance->employee->branch) ? $attendance->employee->branch->name : 'N/A' }}</small>
                                                        </div>
                                                    </div>
                                                </td>
                                            @endif
                                            
                                            <td data-label="Date">
                                                <span class="fw-semibold">{{ \Auth::user()->dateFormat($attendance->date) }}</span>
                                            </td>
                                            
                                            <td data-label="Status">
                                                <span class="status-badge status-{{ strtolower($attendance->status) }}">
                                                    {{ $attendance->status }}
                                                </span>
                                            </td>
                                            
                                            <td data-label="Clock In">
                                                <span class="time-display">
                                                    {{ ($attendance->clock_in != '00:00:00') ? \Auth::user()->timeFormat($attendance->clock_in) : '00:00' }}
                                                </span>
                                            </td>
                                            
                                            <td data-label="Clock Out">
                                                <span class="time-display">
                                                    {{ ($attendance->clock_out != '00:00:00') ? \Auth::user()->timeFormat($attendance->clock_out) : '00:00' }}
                                                </span>
                                            </td>
                                            
                                            <td data-label="Total Work">
                                                <?php
                                                    if($attendance->clock_in != '00:00:00' && $attendance->clock_out != '00:00:00') {
                                                        $startTime = Carbon\Carbon::parse($attendance->clock_in);
                                                        $endTime = Carbon\Carbon::parse($attendance->clock_out);
                                                        $hours = $startTime->diffInHours($endTime);
                                                        $minutes = $startTime->diffInMinutes($endTime) % 60;
                                                        $total_work = $hours . 'h ' . $minutes . 'm';
                                                    } else {
                                                        $total_work = '0h 0m';
                                                    }
                                                ?>
                                                <span class="time-display work-duration">{{ $total_work }}</span>
                                            </td>
                                            
                                            <td data-label="Late">
                                                <span class="time-display {{ $attendance->late != '00:00:00' ? 'late-time' : '' }}">
                                                    {{ $attendance->late != '00:00:00' ? $attendance->late : '-' }}
                                                </span>
                                            </td>
                                            
                                            <td data-label="Location">
                                                <span class="text-muted">{{ $attendance->work_location ?: '-' }}</span>
                                            </td>
                                            
                                            <td data-label="Early Leave">
                                                <span class="time-display {{ $attendance->early_leaving != '00:00:00' ? 'late-time' : '' }}">
                                                    {{ $attendance->early_leaving != '00:00:00' ? $attendance->early_leaving : '-' }}
                                                </span>
                                            </td>
                                            
                                            <td data-label="Overtime">
                                                <span class="time-display {{ $attendance->overtime != '00:00:00' ? 'overtime' : '' }}">
                                                    {{ $attendance->overtime != '00:00:00' ? $attendance->overtime : '-' }}
                                                </span>
                                            </td>
                                            
                                            @if (Gate::check('edit attendance') || Gate::check('delete attendance'))
                                                <td data-label="Action">
                                                    <div class="action-buttons">
                                                        @can('edit attendance')
                                                            <a href="#" 
                                                               data-url="{{ URL::to('attendanceemployee/' . $attendance->id . '/edit') }}" 
                                                               data-size="lg" 
                                                               data-ajax-popup="true" 
                                                               data-title="{{ __('Edit Attendance') }}" 
                                                               class="action-btn edit" 
                                                               data-bs-toggle="tooltip" 
                                                               title="{{ __('Edit') }}">
                                                                <i class="ti ti-pencil"></i>
                                                            </a>
                                                        @endcan
                                                        
                                                        @can('delete attendance')
                                                            {!! Form::open(['method' => 'DELETE', 'route' => ['attendanceemployee.destroy', $attendance->id], 'id' => 'delete-form-' . $attendance->id, 'style' => 'display:inline;']) !!}
                                                            <button type="button" 
                                                                    class="action-btn delete bs-pass-para" 
                                                                    data-bs-toggle="tooltip" 
                                                                    title="{{ __('Delete') }}"
                                                                    data-confirm="{{ __('Are You Sure?') . '|' . __('This action cannot be undone. Do you want to continue?') }}" 
                                                                    data-confirm-yes="document.getElementById('delete-form-{{ $attendance->id }}').submit();">
                                                                <i class="ti ti-trash"></i>
                                                            </button>
                                                            {!! Form::close() !!}
                                                        @endcan
                                                    </div>
                                                </td>
                                            @endif
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Pagination -->
                        <div class="d-flex justify-content-center mt-3 p-3">
                            {{ $attendanceEmployee->appends(request()->query())->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @else
            @if(request()->hasAny(['type', 'month', 'start_date', 'end_date', 'branch_id', 'employee_id']))
            <!-- Empty state for filtered results -->
            <div class="row">
                <div class="col-12">
                    <div class="empty-state">
                        <div class="empty-state-icon">
                            <i class="ti ti-search-off"></i>
                        </div>
                        <h5>{{ __('No Attendance Records Found') }}</h5>
                        <p>{{ __('No attendance data found for the selected filters. Please try adjusting your search criteria.') }}</p>
                    </div>
                </div>
            </div>
            @else
            <!-- Initial state - no filters applied -->
            <div class="row">
                <div class="col-12">
                    <div class="empty-state">
                        <div class="empty-state-icon">
                            <i class="ti ti-calendar-check"></i>
                        </div>
                        <h5>{{ __('View Attendance Records') }}</h5>
                        <p>{{ __('Use the filters above to view attendance records for specific employees, dates, or branches.') }}</p>
                    </div>
                </div>
            </div>
            @endif
        @endif
    </div>

    <!-- Hidden input for filename -->
    <input type="hidden" id="filename" value="Attendance_List_{{ date('Y-m-d') }}.pdf">
@endsection

@push('script-page')
    <script type="text/javascript" src="{{ asset('js/html2pdf.bundle.min.js') }}"></script>
    <script>
        $(document).ready(function() {
            // Initialize tooltips
            $('[data-bs-toggle="tooltip"]').tooltip();

            // Handle type radio change
            $('input[name="type"]:radio').on('change', function (e) {
                var type = $(this).val();
                updateRadioStyles();
                
                if (type == 'monthly') {
                    $('.month').removeClass('d-none').addClass('d-block');
                    $('.date').removeClass('d-block').addClass('d-none');
                } else {
                    $('.date').removeClass('d-none').addClass('d-block');
                    $('.month').removeClass('d-block').addClass('d-none');
                }
            });

            // Initialize form state
            $('input[name="type"]:radio:checked').trigger('change');

            // Show loading spinner on form submit
            $('#attendanceemployee_filter').on('submit', function() {
                $('#loadingSpinner').show();
                $('#generateReport').prop('disabled', true).html('<i class="spinner-border spinner-border-sm me-1"></i>{{ __("Loading...") }}');
            });

            // Auto-hide loading spinner if page loads with data
            @if(!empty($attendanceEmployee) && count($attendanceEmployee) > 0)
                $('#loadingSpinner').hide();
            @endif

            // Handle radio option styling
            $('.radio-option').on('click', function() {
                var radio = $(this).find('input[type="radio"]');
                radio.prop('checked', true).trigger('change');
            });

            // Update active radio styles
            function updateRadioStyles() {
                $('.radio-option').removeClass('active');
                $('input[name="type"]:checked').closest('.radio-option').addClass('active');
            }

            // Initialize Select2 for employee dropdown
            if ($('.select2').length) {
                $('.select2').select2({
                    placeholder: "{{ __('Select Employee') }}",
                    allowClear: true
                });
            }

            // Auto-submit form when entries per page changes
            $('select[name="show_entries"]').on('change', function() {
                $(this).closest('form').submit();
            });

            updateRadioStyles();
        });

        // PDF Export function
        function saveAsPDF() {
            var filename = $('#filename').val() || 'Attendance_List_Report.pdf';
            var element = document.getElementById('printableArea');
            var opt = {
                margin: 0.3,
                filename: filename,
                image: { type: 'jpeg', quality: 1 },
                html2canvas: { scale: 2, dpi: 72, letterRendering: true },
                jsPDF: { unit: 'in', format: 'A3', orientation: 'landscape' }
            };
            html2pdf().set(opt).from(element).save();
        }

        // Excel Export function
        function exportToExcel() {
            $('#export_excel').val(1);
            document.getElementById('attendanceemployee_filter').submit();
        }

        // Enhanced DataTable initialization (if needed for advanced features)
        $(document).ready(function() {
            if ($('#report-dataTable').length && typeof $.fn.DataTable !== 'undefined') {
                var filename = $('#filename').val() || 'Attendance_Report';
                
                $('#report-dataTable').DataTable({
                    dom: 'lBfrtip',
                    buttons: [
                        {
                            extend: 'pdf',
                            title: filename,
                            orientation: 'landscape',
                            pageSize: 'A3'
                        },
                        {
                            extend: 'excel',
                            title: filename
                        },
                        {
                            extend: 'csv',
                            title: filename
                        }
                    ],
                    lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
                    pageLength: 50,
                    responsive: true,
                    order: [[1, 'desc']], // Order by date column
                    columnDefs: [
                        { 
                            targets: [-1], // Last column (actions)
                            orderable: false 
                        }
                    ]
                });
            }
        });

        // Date range picker enhancement
        $(document).ready(function() {
            if (typeof $.fn.daterangepicker !== 'undefined') {
                $('.daterangepicker').daterangepicker({
                    format: 'yyyy-mm-dd',
                    locale: { format: 'YYYY-MM-DD' },
                    autoUpdateInput: false
                });

                $('.daterangepicker').on('apply.daterangepicker', function(ev, picker) {
                    $(this).val(picker.startDate.format('YYYY-MM-DD') + ' - ' + picker.endDate.format('YYYY-MM-DD'));
                });
            }
        });

        // Smooth scrolling for better UX
        function smoothScrollToResults() {
            if ($('#printableArea').length && $(window).scrollTop() === 0) {
                $('html, body').animate({
                    scrollTop: $('#printableArea').offset().top - 100
                }, 500);
            }
        }

        // Call smooth scroll after form submission if there are results
        @if(!empty($attendanceEmployee) && count($attendanceEmployee) > 0)
            $(document).ready(function() {
                setTimeout(smoothScrollToResults, 300);
            });
        @endif

        // Enhanced confirmation dialog for delete
        $(document).on('click', '.bs-pass-para', function(e) {
            e.preventDefault();
            var confirm_data = $(this).data('confirm').split('|');
            var title = confirm_data[0];
            var message = confirm_data[1];
            var confirmYes = $(this).data('confirm-yes');
            
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: title,
                    text: message,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: '{{ __("Yes, delete it!") }}',
                    cancelButtonText: '{{ __("Cancel") }}'
                }).then((result) => {
                    if (result.isConfirmed) {
                        eval(confirmYes);
                    }
                });
            } else {
                if (confirm(title + '\n' + message)) {
                    eval(confirmYes);
                }
            }
        });

        // Print functionality
        function printReport() {
            var printWindow = window.open('', '_blank');
            var printContent = document.getElementById('printableArea').innerHTML;
            
            printWindow.document.write(`
                <html>
                    <head>
                        <title>{{ __('Attendance Report') }}</title>
                        <style>
                            body { font-family: Arial, sans-serif; margin: 20px; }
                            table { width: 100%; border-collapse: collapse; margin-top: 20px; }
                            th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                            th { background-color: #f8f9fa; font-weight: bold; }
                            .status-badge { padding: 4px 8px; border-radius: 4px; }
                            .status-present { background: #d4edda; color: #155724; }
                            .status-absent { background: #f8d7da; color: #721c24; }
                            .no-print { display: none; }
                        </style>
                    </head>
                    <body>
                        <h2>{{ __('Attendance Report') }}</h2>
                        <p>{{ __('Generated on') }}: ${new Date().toLocaleDateString()}</p>
                        ${printContent}
                    </body>
                </html>
            `);
            
            printWindow.document.close();
            printWindow.print();
        }

        // Keyboard shortcuts
        $(document).keydown(function(e) {
            // Ctrl+P for print
            if (e.ctrlKey && e.keyCode === 80) {
                e.preventDefault();
                printReport();
            }
            
            // Ctrl+E for Excel export
            if (e.ctrlKey && e.keyCode === 69) {
                e.preventDefault();
                exportToExcel();
            }
        });

        // Status summary animation
        function animateCounters() {
            $('.summary-number').each(function() {
                var $this = $(this);
                var countTo = parseInt($this.text());
                
                $({ countNum: 0 }).animate({
                    countNum: countTo
                }, {
                    duration: 1000,
                    easing: 'swing',
                    step: function() {
                        $this.text(Math.floor(this.countNum));
                    },
                    complete: function() {
                        $this.text(this.countNum);
                    }
                });
            });
        }

        // Trigger counter animation when cards are visible
        @if(!empty($attendanceEmployee) && count($attendanceEmployee) > 0)
            $(document).ready(function() {
                setTimeout(animateCounters, 200);
            });
        @endif

        // Advanced search toggle
        function toggleAdvancedSearch() {
            $('#advancedFilters').slideToggle(300);
            $('.advanced-toggle').find('i').toggleClass('ti-chevron-down ti-chevron-up');
        }

        // Real-time search for employee dropdown
        if ($('#employee_search').length) {
            $('#employee_search').on('input', function() {
                var searchTerm = $(this).val().toLowerCase();
                $('#employee_id option').each(function() {
                    var optionText = $(this).text().toLowerCase();
                    $(this).toggle(optionText.includes(searchTerm) || $(this).val() === '');
                });
            });
        }
    </script>
@endpush