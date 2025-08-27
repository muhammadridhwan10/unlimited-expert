@extends('layouts.admin')

@section('page-title')
    {{ __('Manage Tracker') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Tracker') }}</li>
@endsection

@push('css-page')
    <link rel="stylesheet" href="{{ url('css/swiper.min.css') }}">
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

        .summary-card.total-time {
            background: linear-gradient(135deg, #66bb6a 0%, #4caf50 100%);
        }

        .summary-card.projects {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        }

        .summary-card.employees {
            background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%);
            color: #333;
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

        /* Project badge */
        .project-badge {
            background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
            color: #1976d2;
            padding: 0.375rem 0.75rem;
            border-radius: 12px;
            font-weight: 600;
            font-size: 0.8rem;
            max-width: 200px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            display: inline-block;
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

        .total-time-display {
            background: linear-gradient(135deg, #e8f5e8 0%, #c8e6c9 100%);
            color: #2e7d32;
            font-weight: 700;
        }

        /* Date display */
        .date-display {
            background: linear-gradient(135deg, #fff3e0 0%, #ffe0b2 100%);
            color: #ef6c00;
            padding: 0.375rem 0.75rem;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.8rem;
            text-align: center;
        }

        /* Action buttons */
        .action-buttons {
            display: flex;
            gap: 0.5rem;
            align-items: center;
        }

        .screenshot-btn {
            width: 35px;
            height: 35px;
            background: linear-gradient(135deg, #9c27b0 0%, #673ab7 100%);
            color: white;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s ease;
            border: none;
            cursor: pointer;
        }

        .screenshot-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
            color: white;
        }

        .delete-btn {
            width: 35px;
            height: 35px;
            background: linear-gradient(135deg, #f44336 0%, #d32f2f 100%);
            color: white;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s ease;
            border: none;
            cursor: pointer;
        }

        .delete-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
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

        /* Modal styling */
        .modal-dialog.modal-lg {
            max-width: 900px;
        }

        .image-slider-container {
            background: white;
            border-radius: 8px;
            overflow: hidden;
        }

        .product-thumbs .swiper-slide img {
            border: 2px solid transparent;
            object-fit: cover;
            cursor: pointer;
            border-radius: 4px;
        }

        .product-thumbs .swiper-slide-active img {
            border-color: #007bff;
        }

        .product-slider .swiper-button-next:after,
        .product-slider .swiper-button-prev:after {
            font-size: 20px;
            color: #007bff;
            font-weight: bold;
        }

        .no-image {
            min-height: 300px;
            align-items: center;
            display: flex;
            justify-content: center;
            background: #f8f9fa;
            border-radius: 8px;
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

        /* Filter grid */
        .filter-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1rem;
            margin-bottom: 1rem;
        }

        @media (max-width: 768px) {
            .filter-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
@endpush

@section('content')
    <!-- Filter Section -->
    <div class="row no-print">
        <div class="col-12">
            <div class="clean-card">
                <div class="card-header-clean">
                    <h6><i class="ti ti-filter me-2"></i>{{ __('Time Tracker Filters') }}</h6>
                </div>
                <div class="card-body">
                    {{ Form::open(['route' => ['time.tracker'], 'method' => 'get', 'id' => 'report_monthly_tracker']) }}
                    
                    <div class="filter-grid">
                        <div>
                            <label class="form-label">{{ __('Start Date') }}</label>
                            {{ Form::date('start_date', isset($_GET['start_date']) ? $_GET['start_date'] : '', ['class' => 'form-control']) }}
                        </div>

                        <div>
                            <label class="form-label">{{ __('End Date') }}</label>
                            {{ Form::date('end_date', isset($_GET['end_date']) ? $_GET['end_date'] : '', ['class' => 'form-control']) }}
                        </div>

                        <div>
                            <label class="form-label">{{ __('Status') }}</label>
                            {{ Form::select('status', ['' => 'Select Status'] + $status, isset($_GET['status']) ? $_GET['status'] : '', ['class' => 'form-select']) }}
                        </div>

                        <div>
                            <label class="form-label">{{ __('Label') }}</label>
                            {{ Form::select('label', ['' => 'Select Label'] + $label, isset($_GET['label']) ? $_GET['label'] : '', ['class' => 'form-select']) }}
                        </div>

                        <div>
                            <label class="form-label">{{ __('Client') }}</label>
                            {{ Form::select('client_id[]', $client, isset($_GET['client_id']) ? $_GET['client_id'] : null, ['class' => 'form-select select2', 'id' => 'choices-multiple1', 'multiple']) }}
                        </div>

                        <div>
                            <label class="form-label">{{ __('Employee') }}</label>
                            {{ Form::select('user_ids[]', $employess, isset($_GET['user_ids']) ? $_GET['user_ids'] : null, ['class' => 'form-select select2', 'id' => 'choices-multiple2', 'multiple']) }}
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-12">
                            <div class="btn-group w-100" role="group">
                                <button type="submit" class="btn btn-primary" id="generateReport">
                                    <i class="ti ti-search me-1"></i>{{ __('Search') }}
                                </button>
                                <a href="{{ route('time.tracker') }}" class="btn btn-secondary">
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
        <p>{{ __('Loading tracker data, please wait...') }}</p>
    </div>

    <!-- Summary Cards -->
    @if(!empty($employeeTimeTracker) && count($employeeTimeTracker) > 0)
    @php
        $totalRecords = $employeeTimeTracker->total();
        $totalTime = $employeeTimeTracker->sum('total_time');
        $uniqueProjects = $employeeTimeTracker->pluck('project_name')->unique()->count();
        $uniqueEmployees = $employeeTimeTracker->pluck('created_by')->unique()->count();
        $totalTimeFormatted = \App\Models\Utility::second_to_time($totalTime);
    @endphp
    <div class="row fade-in">
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="summary-card total-records">
                <div class="summary-number">{{ $totalRecords }}</div>
                <div class="summary-label">{{ __('Total Records') }}</div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="summary-card total-time">
                <div class="summary-number">{{ $totalTimeFormatted }}</div>
                <div class="summary-label">{{ __('Total Time Tracked') }}</div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="summary-card projects">
                <div class="summary-number">{{ $uniqueProjects }}</div>
                <div class="summary-label">{{ __('Active Projects') }}</div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="summary-card employees">
                <div class="summary-number">{{ $uniqueEmployees }}</div>
                <div class="summary-label">{{ __('Active Employees') }}</div>
            </div>
        </div>
    </div>
    @endif

    <!-- Main Content -->
    @if(!empty($employeeTimeTracker) && count($employeeTimeTracker) > 0)
    <div class="row fade-in">
        <div class="col-12">
            <div class="clean-card">
                <div class="card-header-clean">
                    <h6><i class="ti ti-clock me-2"></i>{{ __('Time Tracker Records') }}</h6>
                    <small class="text-muted">
                        {{ __('Showing') }} {{ $employeeTimeTracker->firstItem() }} {{ __('to') }} {{ $employeeTimeTracker->lastItem() }} 
                        {{ __('of') }} {{ $employeeTimeTracker->total() }} {{ __('records') }}
                    </small>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-clean" id="time-tracker-table">
                            <thead>
                                <tr>
                                    <th>{{ __('Date') }}</th>
                                    <th>{{ __('Employee') }}</th>
                                    <th>{{ __('Project') }}</th>
                                    <th>{{ __('Start Time') }}</th>
                                    <th>{{ __('End Time') }}</th>
                                    <th>{{ __('Total Time') }}</th>
                                    <th>{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($employeeTimeTracker as $tracker)
                                    @php
                                        $total_name = \App\Models\Utility::second_to_time($tracker->total_time);
                                    @endphp
                                    <tr>
                                        <td data-label="Date">
                                            <div class="date-display">
                                                <div>{{ date('d M', strtotime($tracker->start_time)) }}</div>
                                                <small>{{ date('Y', strtotime($tracker->start_time)) }}</small>
                                            </div>
                                        </td>

                                        <td data-label="Employee">
                                            <div class="employee-info">
                                                <div class="employee-avatar">
                                                    {{ !empty($tracker->user->name) ? substr($tracker->user->name, 0, 1) : 'N' }}
                                                </div>
                                                <div class="employee-details">
                                                    <h6>{{ !empty($tracker->user->name) ? $tracker->user->name : '-' }}</h6>
                                                    <small>{{ date('l', strtotime($tracker->start_time)) }}</small>
                                                </div>
                                            </div>
                                        </td>

                                        <td data-label="Project">
                                            <span class="project-badge" title="{{ $tracker->project_name }}">
                                                {{ !empty($tracker->project_name) ? $tracker->project_name : '-' }}
                                            </span>
                                        </td>

                                        <td data-label="Start Time">
                                            <span class="time-display">
                                                {{ date('H:i:s', strtotime($tracker->start_time)) }}
                                            </span>
                                        </td>

                                        <td data-label="End Time">
                                            <span class="time-display">
                                                {{ date('H:i:s', strtotime($tracker->end_time)) }}
                                            </span>
                                        </td>

                                        <td data-label="Total Time">
                                            <span class="time-display total-time-display">
                                                {{ $total_name }}
                                            </span>
                                        </td>

                                        <td data-label="Action">
                                            <div class="action-buttons">
                                                <button type="button" 
                                                        class="screenshot-btn view-images" 
                                                        data-bs-toggle="tooltip" 
                                                        title="{{ __('View Screenshots') }}"
                                                        data-id="{{ $tracker->id }}" 
                                                        id="track-images-{{ $tracker->id }}">
                                                    <i class="ti ti-photo"></i>
                                                </button>

                                                @if (Auth::user()->type == 'admin' || Auth::user()->type == 'company')
                                                    {!! Form::open(['method' => 'DELETE', 'route' => ['tracker.destroy', $tracker->id], 'id' => 'delete-form-' . $tracker->id, 'style' => 'display:inline;']) !!}
                                                    <button type="button" 
                                                            class="delete-btn bs-pass-para" 
                                                            data-bs-toggle="tooltip" 
                                                            title="{{ __('Delete') }}"
                                                            data-confirm="{{ __('Are You Sure?') . ' | ' . __('This action cannot be undone. Do you want to continue?') }}" 
                                                            data-confirm-yes="document.getElementById('delete-form-{{ $tracker->id }}').submit();">
                                                        <i class="ti ti-trash"></i>
                                                    </button>
                                                    {!! Form::close() !!}
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination -->
                    <div class="d-flex justify-content-center mt-3 p-3">
                        {{ $employeeTimeTracker->appends(request()->query())->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
    @else
        @if(request()->hasAny(['start_date', 'end_date', 'status', 'label', 'client_id', 'user_ids']))
        <!-- Empty state for filtered results -->
        <div class="row">
            <div class="col-12">
                <div class="empty-state">
                    <div class="empty-state-icon">
                        <i class="ti ti-search-off"></i>
                    </div>
                    <h5>{{ __('No Tracker Data Found') }}</h5>
                    <p>{{ __('No time tracking records found for the selected filters. Please try adjusting your search criteria.') }}</p>
                </div>
            </div>
        </div>
        @else
        <!-- Initial state - no filters applied -->
        <div class="row">
            <div class="col-12">
                <div class="empty-state">
                    <div class="empty-state-icon">
                        <i class="ti ti-clock"></i>
                    </div>
                    <h5>{{ __('View Time Tracker Records') }}</h5>
                    <p>{{ __('Use the filters above to view time tracking records for specific employees, projects, or date ranges.') }}</p>
                </div>
            </div>
        </div>
        @endif
    @endif

    <!-- Screenshot Modal -->
    <div class="modal fade" id="exampleModalCenter" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content image_sider_div image-slider-container">
                <!-- Dynamic content will be loaded here -->
            </div>
        </div>
    </div>
@endsection

@push('script-page')
    <script src="{{ url('js/swiper.min.js') }}"></script>
    <script type="text/javascript">
        $(document).ready(function() {
            // Initialize application
            initializeApp();
        });

        /**
         * Initialize the application
         */
        function initializeApp() {
            initializeTooltips();
            initializeSelect2();
            initializeFormHandlers();
            initializeCounterAnimation();
            initializeImageViewer();
            initializeKeyboardShortcuts();
            initializePerformanceOptimization();
            hideLoadingSpinner();
        }

        /**
         * Initialize tooltips
         */
        function initializeTooltips() {
            $('[data-bs-toggle="tooltip"]').tooltip({
                trigger: 'hover',
                placement: 'top'
            });
        }

        /**
         * Initialize Select2 dropdowns
         */
        function initializeSelect2() {
            if ($('.select2').length) {
                $('.select2').each(function() {
                    const isClient = $(this).attr('id') === 'choices-multiple1';
                    const placeholder = isClient ? "{{ __('Select Client') }}" : "{{ __('Select Employee') }}";
                    
                    $(this).select2({
                        placeholder: placeholder,
                        allowClear: true,
                        width: '100%',
                        theme: 'bootstrap-5',
                        dropdownParent: $(this).parent()
                    });
                });

                // Add search placeholder when dropdown opens
                $('.select2').on('select2:open', function() {
                    $('.select2-search__field').attr('placeholder', '{{ __("Type to search...") }}');
                });
            }
        }

        /**
         * Initialize form handlers
         */
        function initializeFormHandlers() {
            // Form submission handler
            $('#report_monthly_tracker').on('submit', function(e) {
                if (!validateForm()) {
                    e.preventDefault();
                    return false;
                }
                
                showLoadingSpinner();
                disableSubmitButton();
            });

            // Date change handlers
            $('input[type="date"]').on('change', handleDateChange);
        }

        /**
         * Validate form before submission
         */
        function validateForm() {
            const startDate = $('input[name="start_date"]').val();
            const endDate = $('input[name="end_date"]').val();
            
            // Check if start date is greater than end date
            if (startDate && endDate && new Date(startDate) > new Date(endDate)) {
                showNotification('error', '{{ __("Start date cannot be greater than end date") }}', '{{ __("Validation Error") }}');
                return false;
            }
            
            return true;
        }

        /**
         * Handle date input changes
         */
        function handleDateChange() {
            const startDate = $('input[name="start_date"]').val();
            const endDate = $('input[name="end_date"]').val();
            
            if (startDate && endDate) {
                const daysDiff = Math.ceil((new Date(endDate) - new Date(startDate)) / (1000 * 60 * 60 * 24));
                
                if (daysDiff > 90) {
                    showNotification('warning', '{{ __("Date range is quite large. This may affect performance.") }}', '{{ __("Notice") }}');
                } else if (daysDiff < 0) {
                    showNotification('error', '{{ __("End date must be after start date") }}', '{{ __("Invalid Date Range") }}');
                }
            }
        }

        /**
         * Show loading spinner
         */
        function showLoadingSpinner() {
            $('#loadingSpinner').fadeIn(300);
        }

        /**
         * Hide loading spinner
         */
        function hideLoadingSpinner() {
            @if(!empty($employeeTimeTracker) && count($employeeTimeTracker) > 0)
                $('#loadingSpinner').hide();
                smoothScrollToResults();
            @endif
        }

        /**
         * Disable submit button during form submission
         */
        function disableSubmitButton() {
            $('#generateReport')
                .prop('disabled', true)
                .html('<i class="spinner-border spinner-border-sm me-1"></i>{{ __("Loading...") }}');
        }

        /**
         * Initialize counter animation for summary cards
         */
        function initializeCounterAnimation() {
            @if(!empty($employeeTimeTracker) && count($employeeTimeTracker) > 0)
                setTimeout(animateCounters, 200);
            @endif
        }

        /**
         * Animate counter numbers in summary cards
         */
        function animateCounters() {
            $('.summary-number').each(function() {
                const $this = $(this);
                const text = $this.text().trim();
                
                // Skip animation for time format (contains :)
                if (text.includes(':') || text === '') {
                    return;
                }
                
                const countTo = parseInt(text.replace(/,/g, ''));
                if (isNaN(countTo)) return;
                
                $({ countNum: 0 }).animate({
                    countNum: countTo
                }, {
                    duration: 1500,
                    easing: 'easeOutQuart',
                    step: function() {
                        $this.text(Math.floor(this.countNum).toLocaleString());
                    },
                    complete: function() {
                        $this.text(countTo.toLocaleString());
                    }
                });
            });
        }

        /**
         * Initialize image viewer functionality
         */
        function initializeImageViewer() {
            // Modal cleanup on hide
            $('#exampleModalCenter').on('hidden.bs.modal', function() {
                $('.image_sider_div').html('');
            });
        }

        // DIRECT EVENT HANDLER - EXACTLY LIKE OLD VERSION
        $(document).on('click', '.view-images', function () {
            
            var p_url = "{{ route('tracker.image.view') }}";
            var data = {
                'id': $(this).attr('data-id')
            };
            
            // Use old style postAjax call
            postAjax(p_url, data, function (res) {
                $('.image_sider_div').html(res);
                $('#exampleModalCenter').modal('show');
                setTimeout(function(){
                    var total = $('.product-left').find('.product-slider').length;
                    if(total > 0){
                        init_slider();
                    }
                }, 200);
            });
        });

        // ============================ Remove Track Image ===============================//
        $(document).on("click", '.track-image-remove', function () {
            var rid = $(this).attr('data-pid');
            $('.confirm_yes').addClass('image_remove');
            $('.confirm_yes').attr('image_id', rid);
            $('#cModal').modal('show');
            var total = $('.product-left').find('.swiper-slide').length;
        });

        function removeImage(id){
            var p_url = "{{ route('tracker.image.remove') }}";
            var data = {id: id};
            deleteAjax(p_url, data, function (res) {
                if(res.flag){
                    $('#slide-thum-'+id).remove();
                    $('#slide-'+id).remove();
                    setTimeout(function(){
                        var total = $('.product-left').find('.swiper-slide').length;
                        if(total > 0){
                            init_slider();
                        }else{
                            $('.product-left').html('<div class="no-image"><h5 class="text-muted">Images Not Available .</h5></div>');
                        }
                    },200);
                }
                $('#cModal').modal('hide');
                show_toastr('error',res.msg,'error');
            });
        }

        /**
         * OLD SLIDER INITIALIZATION - Keep this exactly as it was working
         */
        function init_slider() {
            if ($(".product-left").length) {
                // Destroy existing instances if they exist
                if (window.productSlider && typeof window.productSlider.destroy === 'function') {
                    window.productSlider.destroy(true, true);
                }
                if (window.productThumbs && typeof window.productThumbs.destroy === 'function') {
                    window.productThumbs.destroy(true, true);
                }
                
                window.productSlider = new Swiper('.product-slider', {
                    spaceBetween: 0,
                    centeredSlides: false,
                    loop: false,
                    direction: 'horizontal',
                    loopedSlides: 5,
                    navigation: {
                        nextEl: ".swiper-button-next",
                        prevEl: ".swiper-button-prev",
                    },
                    resizeObserver: true,
                });
                
                window.productThumbs = new Swiper('.product-thumbs', {
                    spaceBetween: 0,
                    centeredSlides: true,
                    loop: false,
                    slideToClickedSlide: true,
                    direction: 'horizontal',
                    slidesPerView: 7,
                    loopedSlides: 5,
                });
                
                window.productSlider.controller.control = window.productThumbs;
                window.productThumbs.controller.control = window.productSlider;
            }
        }

        /**
         * Initialize keyboard shortcuts
         */
        function initializeKeyboardShortcuts() {
            $(document).keydown(function(e) {
                // Ctrl+F for focus on search
                if (e.ctrlKey && e.keyCode === 70) {
                    e.preventDefault();
                    $('#report_monthly_tracker input[name="start_date"]').focus();
                }
                
                // Escape to close modal
                if (e.keyCode === 27) {
                    $('#exampleModalCenter').modal('hide');
                }
                
                // Ctrl+R to reset form
                if (e.ctrlKey && e.keyCode === 82) {
                    e.preventDefault();
                    window.location.href = '{{ route("time.tracker") }}';
                }
            });
        }

        /**
         * Initialize performance optimization
         */
        function initializePerformanceOptimization() {
            const tableRows = $('#time-tracker-table tbody tr').length;
            
            if (tableRows > 100) {
                showNotification(
                    'info', 
                    '{{ __("Large dataset detected. Consider using date filters for better performance.") }}', 
                    '{{ __("Performance Tip") }}'
                );
            }
            
            // Add lazy loading for images if needed
            if ('IntersectionObserver' in window) {
                const imageObserver = new IntersectionObserver((entries, observer) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            const img = entry.target;
                            img.src = img.dataset.src;
                            img.classList.remove('lazy');
                            observer.unobserve(img);
                        }
                    });
                });

                document.querySelectorAll('img[data-src]').forEach(img => {
                    imageObserver.observe(img);
                });
            }
        }

        /**
         * Initialize delete functionality
         */
        $(document).on('click', '.bs-pass-para', function(e) {
            e.preventDefault();
            
            const confirmData = $(this).data('confirm').split('|');
            const title = confirmData[0] || '{{ __("Are You Sure?") }}';
            const message = confirmData[1] || '{{ __("This action cannot be undone. Do you want to continue?") }}';
            const confirmYes = $(this).data('confirm-yes');
            
            showConfirmDialog(title, message, function() {
                eval(confirmYes);
            });
        });

        /**
         * Smooth scroll to results
         */
        function smoothScrollToResults() {
            @if(!empty($employeeTimeTracker) && count($employeeTimeTracker) > 0)
                setTimeout(function() {
                    if ($('.clean-card').length > 1 && $(window).scrollTop() === 0) {
                        $('html, body').animate({
                            scrollTop: $('.clean-card').eq(1).offset().top - 100
                        }, 800, 'easeInOutQuart');
                    }
                }, 300);
            @endif
        }

        /**
         * Export functionality
         */
        function exportData(format) {
            const formData = $('#report_monthly_tracker').serialize();
            const url = '{{ route("time.tracker") }}?' + formData + '&export=' + format;
            
            // Show loading notification
            showNotification('info', '{{ __("Preparing export...") }}', '{{ __("Please Wait") }}');
            
            // Open in new window
            const exportWindow = window.open(url, '_blank');
            
            // Check if popup was blocked
            if (!exportWindow) {
                showNotification('error', '{{ __("Please allow popups for this site to download exports") }}', '{{ __("Popup Blocked") }}');
            }
        }

        /**
         * AJAX helper functions - SIMPLIFIED TO MATCH OLD VERSION EXACTLY
         */
        function postAjax(url, data, callback) {
            
            $.ajax({
                url: url,
                type: 'POST',
                data: data,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                timeout: 30000,
                success: function(response) {
                    if (typeof callback === 'function') {
                        callback(response);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', xhr, status, error); // Debug log
                    console.error('Response Text:', xhr.responseText); // Debug log
                }
            });
        }

        function deleteAjax(url, data, callback) {
            $.ajax({
                url: url,
                type: 'DELETE',
                data: data,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                timeout: 30000,
                success: function(response) {
                    if (typeof callback === 'function') {
                        callback(response);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Delete AJAX Error:', error);
                }
            });
        }

        /**
         * Notification helper
         */
        function showNotification(type, message, title) {
            if (typeof toastr !== 'undefined') {
                toastr.options = {
                    closeButton: true,
                    progressBar: true,
                    timeOut: type === 'error' ? 10000 : 5000,
                    positionClass: 'toast-top-right'
                };
                toastr[type](message, title);
            } else if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: title,
                    text: message,
                    icon: type === 'error' ? 'error' : type === 'warning' ? 'warning' : type === 'info' ? 'info' : 'success',
                    timer: type === 'error' ? 8000 : 4000,
                    showConfirmButton: type === 'error',
                    toast: true,
                    position: 'top-end'
                });
            } else {
                alert(`${title}: ${message}`);
            }
        }

        /**
         * Confirmation dialog helper
         */
        function showConfirmDialog(title, message, callback) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: title,
                    text: message,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: '{{ __("Yes, proceed!") }}',
                    cancelButtonText: '{{ __("Cancel") }}',
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed && typeof callback === 'function') {
                        callback();
                    }
                });
            } else {
                if (confirm(`${title}\n${message}`)) {
                    if (typeof callback === 'function') {
                        callback();
                    }
                }
            }
        }

        /**
         * Table enhancement functions
         */
        function initializeTableFeatures() {
            // Add sorting capability
            $('#time-tracker-table th').addClass('sortable').on('click', function() {
                if ($(this).hasClass('no-sort')) return;
                
                const table = $(this).parents('table').eq(0);
                const rows = table.find('tr:gt(0)').toArray().sort(comparer($(this).index()));
                
                this.asc = !this.asc;
                if (!this.asc) {
                    rows = rows.reverse();
                }
                
                for (let i = 0; i < rows.length; i++) {
                    table.append(rows[i]);
                }
                
                // Update sort indicators
                table.find('th').removeClass('sort-asc sort-desc');
                $(this).addClass(this.asc ? 'sort-asc' : 'sort-desc');
            });
        }

        function comparer(index) {
            return function(a, b) {
                const valA = getCellValue(a, index);
                const valB = getCellValue(b, index);
                return $.isNumeric(valA) && $.isNumeric(valB) ? valA - valB : valA.toString().localeCompare(valB);
            };
        }

        function getCellValue(row, index) {
            return $(row).children('td').eq(index).text().trim();
        }

        /**
         * Auto-save form state
         */
        function saveFormState() {
            const formData = $('#report_monthly_tracker').serialize();
            localStorage.setItem('tracker_form_state', formData);
        }

        function restoreFormState() {
            const savedState = localStorage.getItem('tracker_form_state');
            if (savedState) {
                // Parse and restore form values if needed
                const params = new URLSearchParams(savedState);
                params.forEach((value, key) => {
                    const field = $(`[name="${key}"]`);
                    if (field.length && !field.val()) {
                        field.val(value);
                    }
                });
            }
        }

        /**
         * Initialize on document ready
         */
        $(document).ready(function() {
            // Add table features if table exists
            if ($('#time-tracker-table').length) {
                initializeTableFeatures();
            }
            
            // Auto-save form state on change
            $('#report_monthly_tracker').on('change', 'input, select', saveFormState);
            
            // Restore form state on page load (if no current values)
            if (!window.location.search) {
                restoreFormState();
            }
        });

        /**
         * Window event handlers
         */
        $(window).on('beforeunload', function() {
            // Clean up any active processes
            if (window.productSlider && typeof window.productSlider.destroy === 'function') {
                window.productSlider.destroy(true, true);
            }
            if (window.productThumbs && typeof window.productThumbs.destroy === 'function') {
                window.productThumbs.destroy(true, true);
            }
        });

        // Add custom easing for animations
        $.easing.easeOutQuart = function(x, t, b, c, d) {
            return -c * ((t = t / d - 1) * t * t * t - 1) + b;
        };

        $.easing.easeInOutQuart = function(x, t, b, c, d) {
            if ((t /= d / 2) < 1) return c / 2 * t * t * t * t + b;
            return -c / 2 * ((t -= 2) * t * t * t - 2) + b;
        };
    </script>
@endpush
