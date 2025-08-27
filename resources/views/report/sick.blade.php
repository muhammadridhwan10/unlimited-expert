@extends('layouts.admin')

@section('page-title')
    {{__('Manage Report Sick')}}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
    <li class="breadcrumb-item">{{__('Report Sick')}}</li>
@endsection

@section('action-btn')
    <div class="float-end">
        @if(!empty($employeesSick))
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
    }

    .summary-card.sick-days {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    }

    .summary-card.sick-letters {
        background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
    }

    .summary-card.average {
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

    /* Sick days badge */
    .sick-days-badge {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        color: white;
        padding: 0.5rem 1rem;
        border-radius: 20px;
        font-weight: 600;
        font-size: 0.875rem;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }

    .sick-days-badge.high {
        background: linear-gradient(135deg, #ff4757 0%, #ff3742 100%);
    }

    .sick-days-badge.medium {
        background: linear-gradient(135deg, #ffa726 0%, #ff9800 100%);
    }

    .sick-days-badge.low {
        background: linear-gradient(135deg, #66bb6a 0%, #4caf50 100%);
    }

    /* View button */
    .view-btn {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border: none;
        padding: 0.5rem 1rem;
        border-radius: 6px;
        text-decoration: none;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        transition: all 0.2s ease;
        font-size: 0.875rem;
    }

    .view-btn:hover {
        color: white;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }

    .sick-letter-count {
        background: #e3f2fd;
        color: #1976d2;
        padding: 0.25rem 0.75rem;
        border-radius: 15px;
        font-size: 0.8rem;
        font-weight: 600;
        margin-right: 0.5rem;
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

    /* Filter section */
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
</style>
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
                    {{ Form::open(array('route' => array('report.sick'),'method'=>'get','id'=>'report_sick')) }}
                    <div class="row g-3">
                        <div class="col-lg-4 col-md-6">
                            <label class="form-label">{{__('Month')}}</label>
                            {{Form::month('month', request('month', date('Y-m')), ['class'=>'form-control'])}}
                        </div>

                        <div class="col-lg-4 col-md-6">
                            <label class="form-label">{{__('Branch')}}</label>
                            {{ Form::select('branch', $branch, request('branch'), ['class' => 'form-select']) }}
                        </div>

                        <div class="col-lg-4 col-md-6 d-flex align-items-end">
                            <div class="btn-group w-100" role="group">
                                <button type="submit" class="btn btn-primary" id="generateReport">
                                    <i class="ti ti-search me-1"></i>{{__('Generate Report')}}
                                </button>
                                <a href="{{route('report.sick')}}" class="btn btn-secondary">
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
        <p>{{__('Generating sick report, please wait...')}}</p>
    </div>

    <!-- Summary Cards -->
    @if(!empty($employeesSick))
    <div class="row fade-in">
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="summary-card">
                <div class="summary-number">{{ $summaryData['total_employees'] }}</div>
                <div class="summary-label">{{__('Employees with Sick Leave')}}</div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="summary-card sick-days">
                <div class="summary-number">{{ $summaryData['total_sick_days'] }}</div>
                <div class="summary-label">{{__('Total Sick Days')}}</div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="summary-card sick-letters">
                <div class="summary-number">{{ $summaryData['total_sick_letters'] }}</div>
                <div class="summary-label">{{__('Total Sick Letters')}}</div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="summary-card average">
                <div class="summary-number">{{ $summaryData['avg_sick_days'] }}</div>
                <div class="summary-label">{{__('Average Sick Days')}}</div>
            </div>
        </div>
    </div>
    @endif

    <!-- Report Content -->
    <div id="printableArea">
        @if(!empty($employeesSick))
        <div class="row fade-in">
            <div class="col-12">
                <div class="clean-card">
                    <div class="card-header-clean">
                        <h6><i class="ti ti-medical-cross me-2"></i>{{__('Sick Leave Report')}}</h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-clean">
                                <thead>
                                    <tr>
                                        <th>{{__('Employee Name')}}</th>
                                        <th>{{__('Total Sick Days')}}</th>
                                        <th>{{__('Sick Letter Details')}}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($employeesSick as $sick)
                                        <tr>
                                            <td data-label="Employee">
                                                <div class="employee-info">
                                                    <div class="employee-avatar">
                                                        {{ substr($sick['name'], 0, 1) }}
                                                    </div>
                                                    <span class="fw-semibold">{{ $sick['name'] }}</span>
                                                </div>
                                            </td>
                                            <td data-label="Sick Days">
                                                <span class="sick-days-badge 
                                                    @if($sick['sick'] >= 10) high 
                                                    @elseif($sick['sick'] >= 5) medium 
                                                    @else low @endif">
                                                    <i class="ti ti-calendar-x"></i>
                                                    {{ $sick['sick'] }} {{__('days')}}
                                                </span>
                                            </td>
                                            <td data-label="Sick Letters">
                                                <div class="d-flex align-items-center">
                                                    <span class="sick-letter-count">
                                                        {{ $sick['total_sick_letter'] }} {{__('letters')}}
                                                    </span>
                                                    <a href="#" 
                                                       class="view-btn" 
                                                       data-url="{{ route('report.employee.sick',[$sick['id'], request('month', date('Y-m'))]) }}" 
                                                       data-ajax-popup="true" 
                                                       data-title="{{__('Sick Leave Details')}}" 
                                                       data-size="xl" 
                                                       data-bs-toggle="tooltip" 
                                                       title="{{__('View Details')}}">
                                                        <i class="ti ti-eye"></i>
                                                        {{__('View Details')}}
                                                    </a>
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
            @if(request()->hasAny(['month', 'branch']))
            <!-- Empty state for filtered results -->
            <div class="row">
                <div class="col-12">
                    <div class="empty-state">
                        <div class="empty-state-icon">
                            <i class="ti ti-search-off"></i>
                        </div>
                        <h5>{{__('No Sick Leave Data Found')}}</h5>
                        <p>{{__('No sick leave records found for the selected period and filters. This could mean no employees took sick leave during this time.')}}</p>
                    </div>
                </div>
            </div>
            @else
            <!-- Initial state - no filters applied -->
            <div class="row">
                <div class="col-12">
                    <div class="empty-state">
                        <div class="empty-state-icon">
                            <i class="ti ti-medical-cross"></i>
                        </div>
                        <h5>{{__('Generate Sick Leave Report')}}</h5>
                        <p>{{__('Please select a month and branch above to generate your sick leave report.')}}</p>
                    </div>
                </div>
            </div>
            @endif
        @endif
    </div>

    <!-- Hidden input for filename -->
    <input type="hidden" id="filename" value="Sick_Leave_Report_{{ request('month', date('Y-m')) }}.pdf">
@endsection

@push('script-page')
    <script type="text/javascript" src="{{ asset('js/html2pdf.bundle.min.js') }}"></script>
    <script>
        $(document).ready(function() {
            // Initialize tooltips
            $('[data-bs-toggle="tooltip"]').tooltip();

            // Show loading spinner on form submit
            $('#report_sick').on('submit', function() {
                $('#loadingSpinner').show();
                $('#generateReport').prop('disabled', true).html('<i class="spinner-border spinner-border-sm me-1"></i>{{__("Generating...")}}');
            });

            // Auto-hide loading spinner if page loads with data
            @if(!empty($employeesSick))
                $('#loadingSpinner').hide();
            @endif
        });

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