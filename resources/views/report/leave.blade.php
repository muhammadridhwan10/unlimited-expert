@extends('layouts.admin')
@section('page-title')
    {{__('Manage Leave Report')}}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
    <li class="breadcrumb-item">{{__('Leave Report')}}</li>
@endsection

@section('action-btn')
    <div class="float-end">
        @if(!empty($leaves))
        <a href="#" class="btn btn-primary" onclick="saveAsPDF()" data-bs-toggle="tooltip" title="{{ __('Download PDF') }}">
            <i class="ti ti-download me-1"></i>{{__('Download PDF')}}
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
        transition: transform 0.2s ease;
    }

    .summary-card:hover {
        transform: translateY(-2px);
    }

    .summary-card.approved {
        background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
    }

    .summary-card.rejected {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    }

    .summary-card.pending {
        background: linear-gradient(135deg, #ffecd2 0%, #fcb69f 100%);
        color: #333;
    }

    .summary-card.duration {
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
    }

    .table-clean thead th {
        background: #f8f9fa;
        border-top: none;
        border-bottom: 2px solid #dee2e6;
        font-weight: 600;
        color: #495057;
        font-size: 0.875rem;
        padding: 1rem 0.75rem;
        white-space: nowrap;
    }

    .table-clean tbody td {
        padding: 1rem 0.75rem;
        vertical-align: middle;
        border-top: 1px solid #dee2e6;
    }

    .table-clean tbody tr:hover {
        background-color: #f8f9fa;
    }

    /* Employee display */
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

    .employee-id-badge {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 0.25rem 0.75rem;
        border-radius: 15px;
        font-size: 0.75rem;
        font-weight: 600;
        margin-top: 0.25rem;
        display: inline-block;
    }

    /* Leave status badges */
    .leave-status-badge {
        padding: 0.5rem 1rem;
        border-radius: 20px;
        font-weight: 600;
        font-size: 0.875rem;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        margin-bottom: 0.5rem;
    }

    .leave-status-badge.approved {
        background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        color: white;
    }

    .leave-status-badge.rejected {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        color: white;
    }

    .leave-status-badge.pending {
        background: linear-gradient(135deg, #ffecd2 0%, #fcb69f 100%);
        color: #333;
    }

    .leave-status-badge.remaining {
        background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%);
        color: #333;
    }

    /* View button */
    .view-btn {
        background: transparent;
        color: #007bff;
        border: 1px solid #007bff;
        padding: 0.25rem 0.75rem;
        border-radius: 4px;
        text-decoration: none;
        font-weight: 500;
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
        transition: all 0.2s ease;
        font-size: 0.8rem;
    }

    .view-btn:hover {
        background: #007bff;
        color: white;
        text-decoration: none;
    }

    .view-btn.approved:hover {
        background: #28a745;
        border-color: #28a745;
    }

    .view-btn.rejected:hover {
        background: #dc3545;
        border-color: #dc3545;
    }

    .view-btn.pending:hover {
        background: #ffc107;
        border-color: #ffc107;
        color: #333;
    }

    /* Filter form styling */
    .filter-type-group {
        display: flex;
        gap: 1rem;
        align-items: center;
    }

    .form-check {
        margin: 0;
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

    /* Responsive design */
    @media (max-width: 768px) {
        .summary-number {
            font-size: 1.5rem;
        }
        
        .table-responsive {
            font-size: 0.875rem;
        }

        .card-header-clean {
            padding: 0.75rem 1rem;
        }

        .table-clean thead th,
        .table-clean tbody td {
            padding: 0.75rem 0.5rem;
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

        .filter-type-group {
            flex-direction: column;
            align-items: flex-start;
            gap: 0.5rem;
        }

        /* Stack table content on mobile */
        .table-responsive table,
        .table-responsive thead,
        .table-responsive tbody,
        .table-responsive th,
        .table-responsive td,
        .table-responsive tr {
            display: block;
        }

        .table-responsive thead tr {
            position: absolute;
            top: -9999px;
            left: -9999px;
        }

        .table-responsive tr {
            border: 1px solid #ccc;
            margin-bottom: 0.5rem;
            padding: 0.5rem;
            border-radius: 4px;
            background: white;
        }

        .table-responsive td {
            border: none;
            position: relative;
            padding-left: 50% !important;
            text-align: left;
        }

        .table-responsive td:before {
            content: attr(data-label);
            position: absolute;
            left: 6px;
            width: 45%;
            padding-right: 10px;
            white-space: nowrap;
            font-weight: 600;
            color: #495057;
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

    /* Report header */
    .report-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 12px;
        padding: 2rem;
        margin-bottom: 2rem;
        text-align: center;
    }

    .report-header h3 {
        margin-bottom: 0.5rem;
        font-weight: 700;
    }

    .report-header p {
        margin: 0;
        opacity: 0.9;
    }

    /* Additional info cards */
    .info-cards {
        margin-bottom: 2rem;
    }

    .info-card {
        background: white;
        border-radius: 8px;
        padding: 1.5rem;
        text-align: center;
        border: 1px solid #e3e6f0;
        height: 100%;
    }

    .info-card-icon {
        font-size: 2rem;
        margin-bottom: 1rem;
        color: #667eea;
    }

    .info-card h6 {
        color: #495057;
        margin-bottom: 0.5rem;
        font-weight: 600;
    }

    .info-card p {
        color: #6c757d;
        margin: 0;
        font-weight: 500;
    }
</style>
@endpush

@push('script-page')
    <script type="text/javascript" src="{{ asset('js/jszip.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/pdfmake.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/vfs_fonts.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/dataTables.buttons.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/buttons.html5.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/html2pdf.bundle.min.js') }}"></script>
    
    <script>
        $(document).ready(function () {
            // Initialize tooltips
            $('[data-bs-toggle="tooltip"]').tooltip();

            // DataTable initialization
            var filename = $('#filename').val();
            $('#report-dataTable').DataTable({
                dom: 'lBfrtip',
                responsive: true,
                scrollX: true,
                buttons: [
                    {
                        extend: 'pdf',
                        title: filename,
                        className: 'btn btn-danger btn-sm'
                    },
                    {
                        extend: 'excel',
                        title: filename,
                        className: 'btn btn-success btn-sm'
                    }, 
                    {
                        extend: 'csv',
                        title: filename,
                        className: 'btn btn-info btn-sm'
                    }
                ],
                language: {
                    search: "{{__('Search')}}:",
                    lengthMenu: "{{__('Show')}} _MENU_ {{__('entries')}}",
                    info: "{{__('Showing')}} _START_ {{__('to')}} _END_ {{__('of')}} _TOTAL_ {{__('entries')}}",
                    paginate: {
                        first: "{{__('First')}}",
                        last: "{{__('Last')}}",
                        next: "{{__('Next')}}",
                        previous: "{{__('Previous')}}"
                    }
                }
            });

            // Show loading spinner on form submit
            $('#report_leave').on('submit', function() {
                $('#loadingSpinner').show();
                $('#generateReport').prop('disabled', true).html('<i class="spinner-border spinner-border-sm me-1"></i>{{__("Generating...")}}');
            });

            // Auto-hide loading spinner if page loads with data
            @if(!empty($leaves))
                $('#loadingSpinner').hide();
            @endif
        });

        // Handle radio button changes
        $('input[name="type"]:radio').on('change', function (e) {
            var type = $(this).val();
            if (type == 'monthly') {
                $('.month').removeClass('d-none').addClass('d-block');
                $('.year').removeClass('d-block').addClass('d-none');
            } else {
                $('.year').removeClass('d-none').addClass('d-block');
                $('.month').removeClass('d-block').addClass('d-none');
            }
        });

        // Trigger initial state
        $('input[name="type"]:radio:checked').trigger('change');

        function saveAsPDF() {
            var filename = $('#filename').val();
            var element = document.getElementById('printableArea');
            var opt = {
                margin: 0.3,
                filename: filename,
                image: {type: 'jpeg', quality: 1},
                html2canvas: {scale: 4, dpi: 72, letterRendering: true},
                jsPDF: {unit: 'in', format: 'A4', orientation: 'portrait'}
            };
            html2pdf().set(opt).from(element).save();
        }
    </script>
@endpush

@section('content')
    <!-- Filter Section -->
    <div class="row no-print">
        <div class="col-12">
            <div class="clean-card">
                <div class="card-header-clean">
                    <h6><i class="ti ti-filter me-2"></i>{{__('Report Filters')}}</h6>
                </div>
                <div class="card-body">
                    {{ Form::open(array('route' => array('report.leave'),'method'=>'get','id'=>'report_leave')) }}
                    <div class="row g-3">
                        <!-- Report Type -->
                        <div class="col-lg-3 col-md-6">
                            <label class="form-label">{{__('Report Type')}}</label>
                            <div class="filter-type-group">
                                <div class="form-check">
                                    <input type="radio" id="monthly" value="monthly" name="type" class="form-check-input" {{isset($_GET['type']) && $_GET['type']=='monthly' ?'checked':'checked'}}>
                                    <label class="form-check-label" for="monthly">{{__('Monthly')}}</label>
                                </div>
                                <div class="form-check">
                                    <input type="radio" id="yearly" value="yearly" name="type" class="form-check-input" {{isset($_GET['type']) && $_GET['type']=='yearly' ?'checked':''}}>
                                    <label class="form-check-label" for="yearly">{{__('Yearly')}}</label>
                                </div>
                            </div>
                        </div>

                        <!-- Month Filter -->
                        <div class="col-lg-3 col-md-6 month">
                            <label class="form-label">{{__('Month')}}</label>
                            {{Form::month('month',isset($_GET['month'])?$_GET['month']:date('Y-m'),array('class'=>'form-control'))}}
                        </div>

                        <!-- Year Filter -->
                        <div class="col-lg-3 col-md-6 year d-none">
                            <label class="form-label">{{__('Year')}}</label>
                            <select class="form-control" id="year" name="year">
                                @for($filterYear['starting_year']; $filterYear['starting_year'] <= $filterYear['ending_year']; $filterYear['starting_year']++)
                                    <option {{(isset($_GET['year']) && $_GET['year'] == $filterYear['starting_year'] ?'selected':'')}} {{(!isset($_GET['year']) && date('Y') == $filterYear['starting_year'] ?'selected':'')}} value="{{$filterYear['starting_year']}}">{{$filterYear['starting_year']}}</option>
                                @endfor
                            </select>
                        </div>

                        <!-- Leave Type Filter -->
                        <div class="col-lg-3 col-md-6">
                            <label class="form-label">{{__('Leave Type')}}</label>
                            {{ Form::select('leave_type_id', $leave_type,isset($_GET['leave_type_id'])?$_GET['leave_type_id']:'', array('class' => 'form-control')) }}
                        </div>

                        <!-- Branch Filter -->
                        <div class="col-lg-3 col-md-6">
                            <label class="form-label">{{__('Branch')}}</label>
                            {{ Form::select('branch', $branch,isset($_GET['branch'])?$_GET['branch']:'', array('class' => 'form-control')) }}
                        </div>

                        <!-- Action Buttons -->
                        <div class="col-lg-4 col-md-6 d-flex align-items-end">
                            <div class="btn-group w-100" role="group">
                                <button type="submit" class="btn btn-primary" id="generateReport">
                                    <i class="ti ti-search me-1"></i>{{__('Generate Report')}}
                                </button>
                                <a href="{{route('report.leave')}}" class="btn btn-secondary">
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

    <!-- Loading Spinner -->
    <div class="loading-spinner" id="loadingSpinner">
        <div class="spinner"></div>
        <p>{{__('Generating leave report, please wait...')}}</p>
    </div>

    <div id="printableArea">
        @if(!empty($leaves))
        <!-- Report Header -->
        <div class="report-header fade-in">
            <input type="hidden" value="{{ $filterYear['branch'] . ' ' . __('Branch') . ' ' . $filterYear['dateYearRange'] . ' ' . $filterYear['type'] . ' ' . __('Leave Report of') . ' ' . $filterYear['department'] . ' ' . 'Department' }}" id="filename">
            <h3 class="text-white"><i class="ti ti-report me-2"></i>{{__('Leave Report')}}</h3>
            <p>{{ $filterYear['type'] . ' ' . __('Leave Summary') }} - {{ $filterYear['dateYearRange'] }}</p>
        </div>

        <!-- Summary Cards -->
        <div class="row fade-in">
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="summary-card approved">
                    <div class="summary-number">{{ $filter['totalApproved'] }}</div>
                    <div class="summary-label">{{__('Approved Leaves')}}</div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="summary-card rejected">
                    <div class="summary-number">{{ $filter['totalReject'] }}</div>
                    <div class="summary-label">{{__('Rejected Leaves')}}</div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="summary-card pending">
                    <div class="summary-number">{{ $filter['totalPending'] }}</div>
                    <div class="summary-label">{{__('Pending Leaves')}}</div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="summary-card duration">
                    <div class="summary-number">{{ count($leaves) }}</div>
                    <div class="summary-label">{{__('Total Employees')}}</div>
                </div>
            </div>
        </div>

        <!-- Additional Info Cards -->
        @if ($filterYear['branch'] != 'All' || $filterYear['department'] != 'All')
        <div class="row info-cards fade-in">
            @if ($filterYear['branch'] != 'All')
            <div class="col-lg-4 col-md-6 mb-3">
                <div class="info-card">
                    <div class="info-card-icon">
                        <i class="ti ti-sitemap"></i>
                    </div>
                    <h6>{{__('Branch')}}</h6>
                    <p>{{ $filterYear['branch'] }}</p>
                </div>
            </div>
            @endif

            @if ($filterYear['department'] != 'All')
            <div class="col-lg-4 col-md-6 mb-3">
                <div class="info-card">
                    <div class="info-card-icon">
                        <i class="ti ti-template"></i>
                    </div>
                    <h6>{{__('Department')}}</h6>
                    <p>{{ $filterYear['department'] }}</p>
                </div>
            </div>
            @endif

            <div class="col-lg-4 col-md-6 mb-3">
                <div class="info-card">
                    <div class="info-card-icon">
                        <i class="ti ti-calendar"></i>
                    </div>
                    <h6>{{__('Duration')}}</h6>
                    <p>{{ $filterYear['dateYearRange'] }}</p>
                </div>
            </div>
        </div>
        @endif

        <!-- Data Table -->
        <div class="row fade-in">
            <div class="col-12">
                <div class="clean-card">
                    <div class="card-header-clean">
                        <h6><i class="ti ti-users me-2"></i>{{__('Employee Leave Details')}}</h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-clean" id="report-dataTable">
                                <thead>
                                <tr>
                                    <th>{{__('Employee')}}</th>
                                    <th>{{__('Approved Leaves')}}</th>
                                    <th>{{__('Rejected Leaves')}}</th>
                                    <th>{{__('Pending Leaves')}}</th>
                                    <th>{{__('Remaining Leaves')}}</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($leaves as $leave)
                                <tr>
                                    <td data-label="Employee">
                                        <div class="employee-info">
                                            <div class="employee-avatar">
                                                {{ substr($leave['employee'], 0, 1) }}
                                            </div>
                                            <div>
                                                <div class="fw-semibold">{{$leave['employee']}}</div>
                                                <span class="employee-id-badge">{{ \Auth::user()->employeeIdFormat($leave['employee_id']) }}</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td data-label="Approved">
                                        <div class="leave-status-badge approved">
                                            <i class="ti ti-circle-check"></i>
                                            {{$leave['approved']}} {{__('days')}}
                                        </div>
                                        <br>
                                        <a href="#" class="view-btn approved" data-url="{{ route('report.employee.leave',[$leave['id'],'Approved',isset($_GET['type']) ?$_GET['type']:'no',isset($_GET['month'])?$_GET['month']:date('Y-m'),isset($_GET['year'])?$_GET['year']:date('Y')]) }}" data-ajax-popup="true" data-title="{{__('Approved Leave Detail')}}" data-size="xl">
                                            <i class="ti ti-eye"></i>{{__('View Details')}}
                                        </a>
                                    </td>
                                    <td data-label="Rejected">
                                        <div class="leave-status-badge rejected">
                                            <i class="ti ti-circle-x"></i>
                                            {{$leave['reject']}} {{__('days')}}
                                        </div>
                                        <br>
                                        <a href="#" class="view-btn rejected" data-url="{{ route('report.employee.leave',[$leave['id'],'Reject',isset($_GET['type']) ?$_GET['type']:'no',isset($_GET['month'])?$_GET['month']:date('Y-m'),isset($_GET['year'])?$_GET['year']:date('Y')]) }}" data-ajax-popup="true" data-title="{{__('Rejected Leave Detail')}}" data-size="xl">
                                            <i class="ti ti-eye"></i>{{__('View Details')}}
                                        </a>
                                    </td>
                                    <td data-label="Pending">
                                        <div class="leave-status-badge pending">
                                            <i class="ti ti-circle-minus"></i>
                                            {{$leave['pending']}} {{__('days')}}
                                        </div>
                                        <br>
                                        <a href="#" class="view-btn pending" data-url="{{ route('report.employee.leave',[$leave['id'],'Pending',isset($_GET['type']) ?$_GET['type']:'no',isset($_GET['month'])?$_GET['month']:date('Y-m'),isset($_GET['year'])?$_GET['year']:date('Y')]) }}" data-ajax-popup="true" data-title="{{__('Pending Leave Detail')}}" data-size="xl">
                                            <i class="ti ti-eye"></i>{{__('View Details')}}
                                        </a>
                                    </td>
                                    <td data-label="Remaining">
                                        <div class="leave-status-badge remaining">
                                            <i class="ti ti-calendar-time"></i>
                                            {{$leave['remaining']}} {{__('days')}}
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @else
            @if(request()->hasAny(['month', 'year', 'branch', 'leave_type_id', 'type']))
            <!-- Empty state for filtered results -->
            <div class="row">
                <div class="col-12">
                    <div class="empty-state">
                        <div class="empty-state-icon">
                            <i class="ti ti-search-off"></i>
                        </div>
                        <h5>{{__('No Leave Data Found')}}</h5>
                        <p>{{__('No leave records found for the selected period and filters. This could mean no employees took leave during this time.')}}</p>
                    </div>
                </div>
            </div>
            @else
            <!-- Initial state - no filters applied -->
            <div class="row">
                <div class="col-12">
                    <div class="empty-state">
                        <div class="empty-state-icon">
                            <i class="ti ti-report"></i>
                        </div>
                        <h5>{{__('Generate Leave Report')}}</h5>
                        <p>{{__('Please select filters above to generate your leave report.')}}</p>
                    </div>
                </div>
            </div>
            @endif
        @endif
    </div>

    <!-- Hidden input for filename -->
    <input type="hidden" id="filename" value="Leave_Report_{{ isset($_GET['month']) ? $_GET['month'] : (isset($_GET['year']) ? $_GET['year'] : date('Y-m')) }}.pdf">
@endsection