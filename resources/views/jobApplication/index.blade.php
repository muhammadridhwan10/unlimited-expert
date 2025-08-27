@extends('layouts.admin')
@section('page-title')
    {{__('Manage Job Application')}}
@endsection
@push('css-page')
    <style>
        /* Clean card styling - Same as template */
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

        .summary-card.total-applications {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        }

        .summary-card.pending-review {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        }

        .summary-card.interviewed {
            background: linear-gradient(135deg, #66bb6a 0%, #4caf50 100%);
        }

        .summary-card.hired {
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

        /* Applicant info styling */
        .applicant-info {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .applicant-avatar {
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
            overflow: hidden;
        }

        .applicant-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .applicant-details h6 {
            margin: 0;
            font-weight: 600;
            color: #495057;
        }

        .applicant-details small {
            color: #6c757d;
        }

        /* Status dropdown styling */
        .status-select {
            background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
            color: #1976d2;
            border: 1px solid #90caf9;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.8rem;
            padding: 0.375rem 0.75rem;
            min-width: 120px;
        }

        .status-select:focus {
            border-color: #1976d2;
            box-shadow: 0 0 0 0.2rem rgba(25, 118, 210, 0.25);
        }

        /* Action buttons */
        .action-buttons {
            display: flex;
            gap: 0.5rem;
            align-items: center;
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
            text-decoration: none;
        }

        .action-btn.bg-primary {
            background: linear-gradient(135deg, #2196f3 0%, #1976d2 100%);
            color: white;
        }

        .action-btn.bg-danger {
            background: linear-gradient(135deg, #f44336 0%, #d32f2f 100%);
            color: white;
        }

        .action-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
            color: white;
        }

        /* Info badge styling */
        .info-badge {
            background: #f8f9fa;
            color: #495057;
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-size: 0.85rem;
            font-weight: 500;
        }

        .date-badge {
            background: linear-gradient(135deg, #fff3e0 0%, #ffe0b2 100%);
            color: #ef6c00;
            padding: 0.375rem 0.75rem;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.8rem;
            text-align: center;
        }

        .gender-badge {
            background: linear-gradient(135deg, #f3e5f5 0%, #e1bee7 100%);
            color: #7b1fa2;
            padding: 0.25rem 0.5rem;
            border-radius: 6px;
            font-weight: 600;
            font-size: 0.75rem;
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

        /* Filter grid */
        .filter-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 1rem;
            margin-bottom: 1rem;
        }

        /* Date input styling */
        .form-control[type="date"] {
            position: relative;
        }

        .form-control[type="date"]::-webkit-calendar-picker-indicator {
            background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>');
            cursor: pointer;
        }

        /* Applied date badge */
        .applied-date-badge {
            background: linear-gradient(135deg, #e8f5e8 0%, #c8e6c9 100%);
            color: #2e7d32;
            padding: 0.375rem 0.75rem;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.8rem;
            text-align: center;
        }

        /* Pagination styling */
        .pagination {
            justify-content: center;
            margin: 0;
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
            color: white;
        }

        .pagination .page-item.disabled .page-link {
            color: #6c757d;
            background-color: #fff;
            border-color: #dee2e6;
        }

        /* Loading spinner */
        .loading-spinner {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.9);
            z-index: 9999;
            justify-content: center;
            align-items: center;
            flex-direction: column;
        }

        .spinner {
            width: 40px;
            height: 40px;
            border: 4px solid #f3f3f3;
            border-top: 4px solid #007bff;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin-bottom: 1rem;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Responsive design */
        @media (max-width: 768px) {
            .filter-grid {
                grid-template-columns: 1fr;
            }
            
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

            .applicant-info {
                flex-direction: column;
                gap: 0.5rem;
                text-align: center;
            }

            .applicant-avatar {
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

        .fade-in {
            animation: fadeIn 0.5s ease-in;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
    </style>
@endpush

@push('script-page')
    <script>
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
            animateCards();
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
                    $(this).select2({
                        placeholder: $(this).attr('placeholder') || "{{__('Select an option')}}",
                        allowClear: true,
                        width: '100%',
                        theme: 'bootstrap-5'
                    });
                });

                // Add search placeholder when dropdown opens
                $('.select2').on('select2:open', function() {
                    $('.select2-search__field').attr('placeholder', '{{__("Type to search...")}}');
                });
            }
        }

        /**
         * Initialize form handlers
         */
        function initializeFormHandlers() {
            // Form submission handler
            $('#job_application').on('submit', function(e) {
                if (!validateDateRange()) {
                    e.preventDefault();
                    return false;
                }
                showLoadingSpinner();
                disableSubmitButton();
            });

            // Date range validation
            $('input[name="applied_from"], input[name="applied_to"]').on('change', validateDateRange);
        }

        /**
         * Validate date range
         */
        function validateDateRange() {
            const fromDate = $('input[name="applied_from"]').val();
            const toDate = $('input[name="applied_to"]').val();
            
            if (fromDate && toDate && new Date(fromDate) > new Date(toDate)) {
                show_toastr('Error', '{{__("Applied From date cannot be greater than Applied To date")}}', 'error');
                return false;
            }
            
            return true;
        }

        /**
         * Update stage function - matches the original controller
         */
        function updateStage(stage, id) {
            // Show loading state
            const selectElement = $(`select[onchange*="${id}"]`);
            selectElement.prop('disabled', true);
            
            // AJAX call matching original structure
            $.ajax({
                url: "{{route('update-stage-job')}}",
                type: "POST",
                data: { 
                    id: id,
                    stage: stage,
                    _token: "{{ csrf_token() }}"
                },
                success: function (data) {
                    selectElement.prop('disabled', false);
                    show_toastr('Success', '{{__("Application status updated successfully!")}}', 'success');
                    console.log(data);
                },
                error: function(xhr, status, error) {
                    selectElement.prop('disabled', false);
                    show_toastr('Error', '{{__("Failed to update status")}}', 'error');
                    console.error('Error:', error);
                }
            });
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
            $('#loadingSpinner').fadeOut(300);
        }

        /**
         * Disable submit button during form submission
         */
        function disableSubmitButton() {
            $('.btn-primary[type="submit"]')
                .prop('disabled', true)
                .html('<i class="spinner-border spinner-border-sm me-1"></i>{{__("Loading...")}}');
        }

        /**
         * Initialize counter animation for summary cards
         */
        function initializeCounterAnimation() {
            setTimeout(animateCounters, 200);
        }

        /**
         * Animate counter numbers in summary cards
         */
        function animateCounters() {
            $('.summary-number').each(function() {
                const $this = $(this);
                const text = $this.text().trim();
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
         * Animate cards on load
         */
        function animateCards() {
            $('.clean-card, .summary-card').each(function(index) {
                $(this).css({
                    opacity: 0,
                    transform: 'translateY(30px)'
                }).delay(index * 100).animate({
                    opacity: 1
                }, 600).css({
                    transform: 'translateY(0)'
                });
            });
        }

        /**
         * Keyboard shortcuts
         */
        $(document).keydown(function(e) {
            // Ctrl+F for focus on search
            if (e.ctrlKey && e.keyCode === 70) {
                e.preventDefault();
                $('input[name="university"]').focus();
            }
            
            // Ctrl+R to reset form
            if (e.ctrlKey && e.keyCode === 82) {
                e.preventDefault();
                window.location.href = '{{route("job-application.index")}}';
            }
        });

        // Add custom easing for animations
        $.easing.easeOutQuart = function(x, t, b, c, d) {
            return -c * ((t = t / d - 1) * t * t * t - 1) + b;
        };

        console.log('{{__("Job Application Management System initialized successfully!")}}');
    </script>
@endpush

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
    <li class="breadcrumb-item">{{__('Job Application')}}</li>
@endsection

@section('action-btn')
    <div class="float-end">
        {{-- Filter toggle can be added here if needed --}}
        {{-- @can('create job application')
            <a href="#" data-size="lg" data-url="{{ route('job-application.create')}}" data-ajax-popup="true" data-bs-toggle="tooltip" title="{{__('Create')}}" data-title="{{__('Create New Job Application')}}" class="btn btn-sm btn-primary">
                <i class="ti ti-plus"></i>
            </a>
        @endcan --}}
    </div>
@endsection

@section('content')
    <!-- Loading Spinner -->
    <div class="loading-spinner" id="loadingSpinner">
        <div class="spinner"></div>
        <p>{{__('Loading job applications, please wait...')}}</p>
    </div>

    <!-- Filter Section -->
    <div class="row">
        <div class="col-sm-12">
            <div class=" mt-2 " id="multiCollapseExample1">
                <div class="clean-card">
                    <div class="card-header-clean">
                        <h6><i class="ti ti-filter me-2"></i>{{__('Job Application Filters')}}</h6>
                    </div>
                    <div class="card-body">
                        {{ Form::open(array('route' => array('job-application.index'),'method'=>'get','id'=>'job_application')) }}
                        <div class="filter-grid">
                            <div>
                                {{ Form::label('university', __('University'), ['class' => 'form-label']) }}
                                {{ Form::select('university', $univercity, isset($_GET['university']) ? $_GET['university'] : null, ['class' => 'form-control select2', 'placeholder' => __('Select University')]) }}
                            </div>
                            <div>
                                {{ Form::label('ipk', __('IPK'), ['class' => 'form-label']) }}
                                {{ Form::select('ipk', $ipk, isset($_GET['ipk']) ? $_GET['ipk'] : null, ['class' => 'form-control select2', 'placeholder' => __('Select IPK')]) }}
                            </div>
                            <div>
                                {{ Form::label('gender', __('Gender'), ['class' => 'form-label']) }}
                                {{ Form::select('gender', ['' => __('Select Gender'), 'male' => __('Male'), 'female' => __('Female')], isset($_GET['gender']) ? $_GET['gender'] : '', ['class' => 'form-control']) }}
                            </div>
                            <div>
                                {{ Form::label('applied_from', __('Applied From'), ['class' => 'form-label']) }}
                                {{ Form::date('applied_from', isset($_GET['applied_from']) ? $_GET['applied_from'] : '', ['class' => 'form-control', 'placeholder' => __('Start Date')]) }}
                            </div>
                            <div>
                                {{ Form::label('applied_to', __('Applied To'), ['class' => 'form-label']) }}
                                {{ Form::date('applied_to', isset($_GET['applied_to']) ? $_GET['applied_to'] : '', ['class' => 'form-control', 'placeholder' => __('End Date')]) }}
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-12">
                                <div class="btn-group w-100" role="group">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="ti ti-search me-1"></i>{{__('Search')}}
                                    </button>
                                    <a href="{{route('job-application.index')}}" class="btn btn-secondary">
                                        <i class="ti ti-refresh me-1"></i>{{__('Reset')}}
                                    </a>
                                </div>
                            </div>
                        </div>
                        {{ Form::close() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Summary Cards -->
    @if(count($applicants) > 0)
    @php
        $totalApplications = count($applicants);
        $pendingReview = $applicants->whereIn('stage', [1, 2])->count();
        $interviewed = $applicants->where('stage', 3)->count();
        $hired = $applicants->where('stage', 4)->count();
    @endphp
    <div class="row fade-in">
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="summary-card total-applications">
                <div class="summary-number">{{ $totalApplications }}</div>
                <div class="summary-label">{{__('Total Applications')}}</div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="summary-card pending-review">
                <div class="summary-number">{{ $pendingReview }}</div>
                <div class="summary-label">{{__('Pending Review')}}</div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="summary-card interviewed">
                <div class="summary-number">{{ $interviewed }}</div>
                <div class="summary-label">{{__('Interviewed')}}</div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="summary-card hired">
                <div class="summary-number">{{ $hired }}</div>
                <div class="summary-label">{{__('Hired')}}</div>
            </div>
        </div>
    </div>
    @endif

    <!-- Main Content -->
    <div class="row">
        <div class="col-md-12">
            <div class="clean-card fade-in">
                <div class="card-header-clean">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6><i class="ti ti-users me-2"></i>{{__('Job Application Records')}}</h6>
                        @if(count($applicants) > 0)
                            <small class="text-muted">
                                {{__('Showing')}} {{ count($applicants) }} {{__('records')}}
                            </small>
                        @endif
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-clean">
                            <thead>
                                <tr>
                                    <th scope="col">{{__('Name')}}</th>
                                    <th scope="col">{{__('Applied At')}}</th>
                                    <th scope="col">{{__('DoB')}}</th>
                                    <th scope="col">{{__('Gender')}}</th>
                                    <th scope="col">{{__('Phone')}}</th>
                                    <th scope="col">{{__('Email')}}</th>
                                    <th scope="col">{{__('City')}}</th>
                                    <th scope="col">{{__('Status')}}</th>
                                    <th scope="col">{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody class="list">
                                @if(count($applicants) > 0)
                                    @foreach($applicants as $applicant)
                                        <tr>
                                            <td data-label="{{__('Name')}}">
                                                <div class="applicant-info">
                                                    <div class="applicant-avatar">
                                                        @if($applicant->profile)
                                                            <img src="{{asset('/storage/uploads/job/profile/'.$applicant->profile)}}" alt="{{ $applicant->name }}" />
                                                        @else
                                                            {{ substr($applicant->name, 0, 1) }}
                                                        @endif
                                                    </div>
                                                    <div class="applicant-details">
                                                        <h6>{{ $applicant->name }}</h6>
                                                        <small>{{ $applicant->email }}</small>
                                                    </div>
                                                </div>
                                            <td data-label="{{__('Applied At')}}">
                                                <div class="date-badge">
                                                    <div>{{ date('d M', strtotime($applicant->created_at)) }}</div>
                                                    <small>{{ date('Y H:i', strtotime($applicant->created_at)) }}</small>
                                                </div>
                                            </td>
                                            <td data-label="{{__('DoB')}}">
                                                @if(!empty($applicant->dob))
                                                    <div class="date-badge">
                                                        <div>{{ date('d M', strtotime($applicant->dob)) }}</div>
                                                        <small>{{ date('Y', strtotime($applicant->dob)) }}</small>
                                                    </div>
                                                @else
                                                    <span class="info-badge">-</span>
                                                @endif
                                            </td>
                                            <td data-label="{{__('Gender')}}">
                                                @if(!empty($applicant->gender))
                                                    <span class="gender-badge">{{ucfirst($applicant->gender)}}</span>
                                                @else
                                                    <span class="info-badge">-</span>
                                                @endif
                                            </td>
                                            <td data-label="{{__('Phone')}}">
                                                <span class="info-badge">{{!empty($applicant->phone)?$applicant->phone:'-'}}</span>
                                            </td>
                                            <td data-label="{{__('Email')}}">
                                                <span class="info-badge">{{!empty($applicant->email)?$applicant->email:'-'}}</span>
                                            </td>
                                            <td data-label="{{__('City')}}">
                                                <span class="info-badge">{{!empty($applicant->city)?$applicant->city:'-'}}</span>
                                            </td>
                                            <td data-label="{{__('Status')}}">
                                                <select class="form-control status-select" name="stage" onchange="updateStage(this.value, {{ $applicant->id }})">
                                                    <option value="0" hidden>{{$applicant->stage_status->title ?? __('Select Status')}}</option>
                                                    @foreach($stages as $stage)
                                                        <option value="{{ $stage->id }}" @if($applicant->stage == $stage->id) selected @endif>{{ $stage->title }}</option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td data-label="{{__('Action')}}">
                                                <div class="action-buttons">
                                                    @can('show job application')
                                                        <div class="action-btn bg-primary ms-2">
                                                            <a href="{{ route('job-application.show',\Crypt::encrypt($applicant->id)) }}" 
                                                            class="mx-3 btn btn-sm align-items-center text-white" data-bs-toggle="tooltip" data-bs-original-title="{{__('View ').$applicant->name}}">
                                                                <i class="ti ti-eye"></i>
                                                            </a>
                                                        </div>
                                                    @endcan
                                                    @can('delete job application')
                                                        <div class="action-btn bg-danger ms-2">
                                                        {!! Form::open(['method' => 'DELETE', 'route' => ['job-application.destroy', $applicant->id],'id'=>'delete-form-'.$applicant->id]) !!}
                                                            <a href="#" class="mx-3 btn btn-sm align-items-center bs-pass-para text-white" data-confirm="{{__('Are You Sure?').'|'.__('This action can not be undone. Do you want to continue?')}}" data-bs-toggle="tooltip" title="{{__('Delete')}}" data-original-title="{{__('Delete')}}" data-confirm-yes="document.getElementById('delete-form-{{$applicant->id}}').submit();">
                                                            <i class="ti ti-trash"></i></a>
                                                            {!! Form::close() !!}
                                                        </div>
                                                    @endcan
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="9">
                                            <div class="empty-state">
                                                <div class="empty-state-icon">
                                                    <i class="ti ti-users-off"></i>
                                                </div>
                                                <h5>{{__('No applicants found')}}</h5>
                                                <p>{{__('No job applications found for the selected criteria.')}}</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
                                    <!-- Pagination -->
                @if(count($applicants) > 0)
                    <div class="d-flex justify-content-center mt-3 p-3">
                        {{ $applicants->appends(request()->query())->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection