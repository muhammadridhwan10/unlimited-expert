@extends('layouts.admin')
@section('page-title')
    {{__('Manage Job Candidates')}}
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

        .summary-card.psychotest {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        }

        .summary-card.interview1 {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        }

        .summary-card.interview2 {
            background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
        }

        .summary-card.hired {
            background: linear-gradient(135deg, #66bb6a 0%, #4caf50 100%);
        }

        .summary-card.rejected {
            background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%);
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

        /* Candidate info styling */
        .candidate-info {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .candidate-avatar {
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

        .candidate-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .candidate-details h6 {
            margin: 0;
            font-weight: 600;
            color: #495057;
        }

        .candidate-details small {
            color: #6c757d;
        }

        /* Status badges */
        .stage-badge {
            padding: 0.375rem 0.75rem;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.8rem;
            text-align: center;
            min-width: 100px;
        }

        .stage-2 {
            background: linear-gradient(135deg, #fff3e0 0%, #ffe0b2 100%);
            color: #ef6c00;
        }

        .stage-3 {
            background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
            color: #1976d2;
        }

        .stage-4 {
            background: linear-gradient(135deg, #e8f5e8 0%, #c8e6c9 100%);
            color: #2e7d32;
        }

        .stage-5 {
            background: linear-gradient(135deg, #e1f5fe 0%, #b3e5fc 100%);
            color: #0277bd;
        }

        .stage-6 {
            background: linear-gradient(135deg, #ffebee 0%, #ffcdd2 100%);
            color: #c62828;
        }

        /* Rating stars */
        .rating-stars {
            display: flex;
            gap: 2px;
            align-items: center;
        }

        .rating-stars i {
            font-size: 0.875rem;
            color: #ffc107;
        }

        .rating-stars i.empty {
            color: #dee2e6;
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

        .action-btn.bg-success {
            background: linear-gradient(135deg, #4caf50 0%, #388e3c 100%);
            color: white;
        }

        .action-btn.bg-warning {
            background: linear-gradient(135deg, #ff9800 0%, #f57c00 100%);
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

        /* Info badge styling with status colors */
        .info-badge {
            background: #f8f9fa;
            color: #495057;
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-size: 0.85rem;
            font-weight: 500;
            text-align: center;
        }

        .info-badge.text-warning {
            background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%);
            color: #856404;
            border: 1px solid #ffeaa7;
        }

        .info-badge.text-primary {
            background: linear-gradient(135deg, #cce5ff 0%, #b3d9ff 100%);
            color: #004085;
            border: 1px solid #b3d9ff;
        }

        .info-badge.text-success {
            background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .info-badge.text-danger {
            background: linear-gradient(135deg, #f8d7da 0%, #f1b0b7 100%);
            color: #721c24;
            border: 1px solid #f1b0b7;
        }

        .info-badge.text-muted {
            background: linear-gradient(135deg, #e9ecef 0%, #dee2e6 100%);
            color: #6c757d;
            border: 1px solid #dee2e6;
        }

        .info-badge i {
            font-size: 0.75rem;
        }

        .info-badge small {
            display: block;
            line-height: 1.2;
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

            .candidate-info {
                flex-direction: column;
                gap: 0.5rem;
                text-align: center;
            }

            .candidate-avatar {
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
            initializeApp();
        });

        function initializeApp() {
            initializeTooltips();
            initializeSelect2();
            initializeFormHandlers();
            initializeCounterAnimation();
            animateCards();
        }

        function initializeTooltips() {
            $('[data-bs-toggle="tooltip"]').tooltip({
                trigger: 'hover',
                placement: 'top'
            });
        }

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
            }
        }

        function initializeFormHandlers() {
            $('#candidate_filter').on('submit', function(e) {
                if (!validateDateRange()) {
                    e.preventDefault();
                    return false;
                }
            });

            $('input[name="applied_from"], input[name="applied_to"]').on('change', validateDateRange);
        }

        function validateDateRange() {
            const fromDate = $('input[name="applied_from"]').val();
            const toDate = $('input[name="applied_to"]').val();
            
            if (fromDate && toDate && new Date(fromDate) > new Date(toDate)) {
                show_toastr('Error', '{{__("Applied From date cannot be greater than Applied To date")}}', 'error');
                return false;
            }
            
            return true;
        }

        function initializeCounterAnimation() {
            setTimeout(animateCounters, 200);
        }

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

        $.easing.easeOutQuart = function(x, t, b, c, d) {
            return -c * ((t = t / d - 1) * t * t * t - 1) + b;
        };

        console.log('{{__("Job Candidate Management System initialized successfully!")}}');
    </script>
@endpush

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
    <li class="breadcrumb-item">{{__('Job Candidates')}}</li>
@endsection

@section('content')
    <!-- Filter Section -->
    <div class="row">
        <div class="col-sm-12">
            <div class="mt-2">
                <div class="clean-card">
                    <div class="card-header-clean">
                        <h6><i class="ti ti-filter me-2"></i>{{__('Job Candidate Filters')}}</h6>
                    </div>
                    <div class="card-body">
                        {{ Form::open(array('route' => array('job.application.candidate'),'method'=>'get','id'=>'candidate_filter')) }}
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
                                {{ Form::label('status', __('Stage'), ['class' => 'form-label']) }}
                                {{ Form::select('status', ['' => __('Select Stage')] + collect($stages)->where('id', '>', 1)->pluck('title', 'id')->toArray(), isset($_GET['status']) ? $_GET['status'] : '', ['class' => 'form-control']) }}
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
                                    <a href="{{route('job.application.candidate')}}" class="btn btn-secondary">
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
    @if(count($candidates) > 0)
    @php
        $psychotest = $candidates->where('stage', 2)->count();
        $interview1 = $candidates->where('stage', 3)->count();
        $interview2 = $candidates->where('stage', 4)->count();
        $hired = $candidates->where('stage', 5)->count();
        $rejected = $candidates->where('stage', 6)->count();
    @endphp
    <div class="row fade-in">
        <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
            <div class="summary-card psychotest">
                <div class="summary-number">{{ $psychotest }}</div>
                <div class="summary-label">{{__('Psychotest')}}</div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
            <div class="summary-card interview1">
                <div class="summary-number">{{ $interview1 }}</div>
                <div class="summary-label">{{__('Interview 1')}}</div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
            <div class="summary-card interview2">
                <div class="summary-number">{{ $interview2 }}</div>
                <div class="summary-label">{{__('Interview 2')}}</div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
            <div class="summary-card hired">
                <div class="summary-number">{{ $hired }}</div>
                <div class="summary-label">{{__('Hired')}}</div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
            <div class="summary-card rejected">
                <div class="summary-number">{{ $rejected }}</div>
                <div class="summary-label">{{__('Rejected')}}</div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
            <div class="summary-card">
                <div class="summary-number">{{ count($candidates) }}</div>
                <div class="summary-label">{{__('Total Candidates')}}</div>
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
                        <h6><i class="ti ti-users me-2"></i>{{__('Job Candidate Records')}}</h6>
                        @if(count($candidates) > 0)
                            <small class="text-muted">
                                {{__('Showing')}} {{ count($candidates) }} {{__('candidates')}}
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
                                    <th scope="col">{{__('Applied Job')}}</th>
                                    <th scope="col">{{__('Psychotest Status')}}</th>
                                    <th scope="col">{{__('Applied At')}}</th>
                                    <th scope="col">{{__('Stage')}}</th>
                                    <th scope="col">{{__('University')}}</th>
                                    <th scope="col">{{__('IPK')}}</th>
                                    <th scope="col">{{__('Documents')}}</th>
                                    <th scope="col">{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody class="list">
                                @if(count($candidates) > 0)
                                    @foreach($candidates as $candidate)
                                        <tr>
                                            <td data-label="{{__('Name')}}">
                                                <div class="candidate-info">
                                                    <div class="candidate-avatar">
                                                        @if($candidate->profile)
                                                            <img src="{{asset('/storage/uploads/job/profile/'.$candidate->profile)}}" alt="{{ $candidate->name }}" />
                                                        @else
                                                            {{ substr($candidate->name, 0, 1) }}
                                                        @endif
                                                    </div>
                                                    <div class="candidate-details">
                                                        <h6>{{ $candidate->name }}</h6>
                                                        <small>{{ $candidate->email }}</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td data-label="{{__('Applied Job')}}">
                                                <span class="info-badge">{{ !empty($candidate->jobs)?$candidate->jobs->title:'-' }}</span>
                                            </td>
                                            <td data-label="{{__('Psychotest Status')}}">
                                                @if($candidate->stage == 2)
                                                    @php
                                                        $psychotestStatus = $candidate->psychotest_status;
                                                    @endphp
                                                    @if($psychotestStatus)
                                                        <div class="info-badge {{ $psychotestStatus['class'] }}">
                                                            <i class="ti {{ $psychotestStatus['icon'] }} me-1"></i>
                                                            <small>{{ $psychotestStatus['text'] }}</small>
                                                            @if($psychotestStatus['status'] == 'scheduled')
                                                                <br><small>{{ date('d M Y, H:i', strtotime($psychotestStatus['start_time'])) }} - {{ date('H:i', strtotime($psychotestStatus['end_time'])) }}</small>
                                                            @elseif($psychotestStatus['status'] == 'in_progress')
                                                                <br><small>{{__('Started')}}: {{ date('H:i', strtotime($psychotestStatus['started_at'])) }}</small>
                                                            @elseif($psychotestStatus['status'] == 'completed')
                                                                <br><small>{{ date('d M, H:i', strtotime($psychotestStatus['started_at'])) }} - {{ date('H:i', strtotime($psychotestStatus['completed_at'])) }}</small>
                                                            @endif
                                                        </div>
                                                    @endif
                                                @else
                                                    <div class="info-badge">
                                                        <small>-</small>
                                                    </div>
                                                @endif
                                            </td>
                                            <td data-label="{{__('Applied At')}}">
                                                <div class="date-badge">
                                                    <div>{{ date('d M', strtotime($candidate->created_at)) }}</div>
                                                    <small>{{ date('Y H:i', strtotime($candidate->created_at)) }}</small>
                                                </div>
                                            </td>
                                            <td data-label="{{__('Stage')}}">
                                                @php
                                                    $stageClass = 'stage-' . $candidate->stage;
                                                    $stageText = $candidate->stage_status->title ?? 'Unknown';
                                                @endphp
                                                <span class="stage-badge {{ $stageClass }}">{{ $stageText }}</span>
                                            </td>
                                            <td data-label="{{__('University')}}">
                                                <span class="info-badge">{{ !empty($candidate->university) ? $candidate->university : '-' }}</span>
                                            </td>
                                            <td data-label="{{__('IPK')}}">
                                                <span class="info-badge">{{ !empty($candidate->ipk) ? $candidate->ipk : '-' }}</span>
                                            </td>
                                            <td data-label="{{__('Documents')}}">
                                                <div class="action-buttons">
                                                    @if(!empty($candidate->resume))
                                                        <a href="{{asset(Storage::url('uploads/job/resume')).'/'.$candidate->resume}}" target="_blank" 
                                                           class="action-btn bg-primary" data-bs-toggle="tooltip" title="{{__('Resume')}}">
                                                            <i class="ti ti-file-text"></i>
                                                        </a>
                                                    @endif
                                                    @if(!empty($candidate->kk))
                                                        <a href="{{asset(Storage::url('uploads/job/kk')).'/'.$candidate->kk}}" target="_blank" 
                                                           class="action-btn bg-success" data-bs-toggle="tooltip" title="{{__('KK')}}">
                                                            <i class="ti ti-id"></i>
                                                        </a>
                                                    @endif
                                                    @if(!empty($candidate->ktp))
                                                        <a href="{{asset(Storage::url('uploads/job/ktp')).'/'.$candidate->ktp}}" target="_blank" 
                                                           class="action-btn bg-warning" data-bs-toggle="tooltip" title="{{__('KTP')}}">
                                                            <i class="ti ti-credit-card"></i>
                                                        </a>
                                                    @endif
                                                    @if(!empty($candidate->ijazah))
                                                        <a href="{{asset(Storage::url('uploads/job/ijazah')).'/'.$candidate->ijazah}}" target="_blank" 
                                                           class="action-btn bg-primary" data-bs-toggle="tooltip" title="{{__('Ijazah')}}">
                                                            <i class="ti ti-certificate"></i>
                                                        </a>
                                                    @endif
                                                </div>
                                            </td>
                                            <td data-label="{{__('Action')}}">
                                                <div class="action-buttons">
                                                    @can('show job application')
                                                        <div class="action-btn bg-primary">
                                                            <a href="{{ route('job-application.show',\Crypt::encrypt($candidate->id)) }}" 
                                                               class="mx-3 btn btn-sm align-items-center text-white" 
                                                               data-bs-toggle="tooltip" data-bs-original-title="{{__('View ').$candidate->name}}">
                                                                <i class="ti ti-eye"></i>
                                                            </a>
                                                        </div>
                                                    @endcan
                                                    @if($candidate->stage != 6)
                                                        <div class="action-btn bg-danger">
                                                            <a href="#" onclick="updateCandidateStage(6, {{ $candidate->id }})" 
                                                               class="mx-3 btn btn-sm align-items-center text-white" 
                                                               data-bs-toggle="tooltip" data-bs-original-title="{{__('Reject Candidate')}}">
                                                                <i class="ti ti-x"></i>
                                                            </a>
                                                        </div>
                                                    @endif
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
                                                <h5>{{__('No candidates found')}}</h5>
                                                <p>{{__('No job candidates found for the selected criteria. Candidates appear here after passing the initial application stage.')}}</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
                <!-- Pagination -->
                @if(count($candidates) > 0)
                    <div class="d-flex justify-content-center mt-3 p-3">
                        {{ $candidates->appends(request()->query())->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Additional JavaScript for candidate-specific actions -->
    <script>
        function updateCandidateStage(stage, candidateId) {
            if (confirm("{!! __('Are you sure you want to update this candidate\'s stage?') !!}")) {
                // AJAX call to update candidate stage
                $.ajax({
                    url: "{{route('update-stage-job')}}",
                    type: "POST",
                    data: { 
                        id: candidateId,
                        stage: stage,
                        _token: "{{ csrf_token() }}"
                    },
                    success: function (data) {
                        show_toastr('Success', '{{__("Candidate stage updated successfully!")}}', 'success');
                        location.reload();
                    },
                    error: function(xhr, status, error) {
                        show_toastr('Error', '{{__("Failed to update candidate stage")}}', 'error');
                        console.error('Error:', error);
                    }
                });
            }
        }
    </script>
@endsection