@extends('layouts.admin')

@section('page-title')
    {{__('Manage Monthly Attendance')}}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
    <li class="breadcrumb-item">{{__('Monthly Attendance')}}</li>
@endsection

@section('action-btn')
    <div class="float-end">
        @if(!empty($employeesAttendance))
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

    .summary-card.present {
        background: linear-gradient(135deg, #66bb6a 0%, #4caf50 100%);
    }

    .summary-card.leave {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    }

    .summary-card.total-employees {
        background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
    }

    .summary-card.percentage {
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

    /* Attendance table styling */
    .attendance-table-container {
        background: white;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }

    .attendance-table-responsive {
        position: relative;
        overflow-x: auto;
        max-height: 600px;
        overflow-y: auto;
    }

    .table-attendance {
        margin: 0;
        font-size: 0.875rem;
    }

    .table-attendance thead th {
        background: #f8f9fa;
        border-top: none;
        border-bottom: 2px solid #dee2e6;
        font-weight: 600;
        color: #495057;
        padding: 1rem 0.75rem;
        white-space: nowrap;
        text-align: center;
        vertical-align: middle;
        position: sticky;
        top: 0;
        z-index: 10;
    }

    .sticky-column {
        position: sticky;
        left: 0;
        z-index: 15;
        background: white !important;
        box-shadow: 2px 0 5px -2px rgba(0, 0, 0, 0.1);
        min-width: 200px;
        max-width: 200px;
    }

    .sticky-column:hover {
        z-index: 16;
    }

    .table-attendance tbody td {
        padding: 0.75rem;
        vertical-align: middle;
        border-top: 1px solid #dee2e6;
        text-align: center;
    }

    .table-attendance tbody tr:hover {
        background-color: #f8f9fa;
    }

    .table-attendance tbody tr:hover .sticky-column {
        background-color: #f8f9fa !important;
    }

    /* Employee name styling */
    .employee-name {
        font-weight: 600;
        color: #495057;
        text-align: left;
        padding-left: 1rem;
    }

    /* Attendance status styling */
    .attendance-status {
        display: inline-flex;
        flex-direction: column;
        align-items: center;
        gap: 0.25rem;
    }

    .status-present {
        color: #28a745;
        font-size: 1.25rem;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .status-present:hover {
        color: #1e7e34;
        transform: scale(1.1);
    }

    .status-absent {
        background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
        color: white;
        padding: 0.25rem 0.5rem;
        border-radius: 12px;
        font-size: 0.75rem;
        font-weight: 600;
        min-width: 24px;
    }

    .status-weekend {
        background: linear-gradient(135deg, #6c757d 0%, #5a6268 100%);
        color: white;
        padding: 0.25rem 0.5rem;
        border-radius: 12px;
        font-size: 0.75rem;
        font-weight: 600;
        min-width: 24px;
    }

    .distance-info {
        font-size: 0.7rem;
        color: #6c757d;
        font-weight: 500;
    }

    /* Date header styling */
    .date-header {
        writing-mode: vertical-rl;
        text-orientation: mixed;
        min-width: 40px;
        font-size: 0.8rem;
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

    /* Responsive design */
    @media (max-width: 768px) {
        .summary-number {
            font-size: 1.5rem;
        }
        
        .card-header-clean {
            padding: 0.75rem 1rem;
        }

        .table-attendance {
            font-size: 0.75rem;
        }

        .table-attendance thead th,
        .table-attendance tbody td {
            padding: 0.5rem 0.25rem;
        }

        .sticky-column {
            min-width: 150px;
            max-width: 150px;
        }

        .date-header {
            font-size: 0.7rem;
            min-width: 35px;
        }

        .employee-name {
            font-size: 0.8rem;
        }

        .distance-info {
            font-size: 0.6rem;
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

        .sticky-column {
            position: static !important;
        }
    }

    .fade-in {
        animation: fadeIn 0.5s ease-in;
    }

    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }

    /* Map link styling */
    .map-link {
        text-decoration: none;
        color: #28a745;
        transition: all 0.2s ease;
    }

    .map-link:hover {
        color: #1e7e34;
        text-decoration: none;
    }

    /* Weekend highlighting */
    .weekend-column {
        background-color: #f8f9fa !important;
    }

    .weekend-column th {
        background-color: #e9ecef !important;
    }
</style>
@endpush

@push('script-page')
    <script type="text/javascript" src="{{ asset('js/html2pdf.bundle.min.js') }}"></script>
    <script>
        $(document).ready(function() {
            // Initialize tooltips
            $('[data-bs-toggle="tooltip"]').tooltip();

            // Show loading spinner on form submit
            $('#report_monthly_attendance').on('submit', function() {
                $('#loadingSpinner').show();
                $('#generateReport').prop('disabled', true).html('<i class="spinner-border spinner-border-sm me-1"></i>{{__("Generating...")}}');
            });

            // Auto-hide loading spinner if page loads with data
            @if(!empty($employeesAttendance))
                $('#loadingSpinner').hide();
            @endif

            // Highlight weekends
            highlightWeekends();
        });

        function highlightWeekends() {
            $('.table-attendance thead th').each(function(index) {
                if (index === 0) return; // Skip employee name column
                
                var dateText = $(this).text().trim();
                if (dateText) {
                    var date = new Date(new Date().getFullYear(), new Date().getMonth(), parseInt(dateText));
                    var dayOfWeek = date.getDay();
                    
                    if (dayOfWeek === 0 || dayOfWeek === 6) { // Sunday or Saturday
                        $(this).addClass('weekend-column');
                        $('.table-attendance tbody tr').each(function() {
                            $(this).find('td').eq(index).addClass('weekend-column');
                        });
                    }
                }
            });
        }

        function saveAsPDF() {
            var filename = $('#filename').val() || 'Monthly_Attendance_Report.pdf';
            var element = document.getElementById('printableArea');
            var opt = {
                margin: 0.3,
                filename: filename,
                image: {type: 'jpeg', quality: 1},
                html2canvas: {scale: 2, dpi: 72, letterRendering: true},
                jsPDF: {unit: 'in', format: 'A3', orientation: 'landscape'}
            };
            html2pdf().set(opt).from(element).save();
        }

        // Auto form submission for better UX
        $('.form-control, .form-select').on('change', function() {
            var form = $('#report_monthly_attendance');
            if (form.find('input[name="start_date"]').val() && form.find('input[name="end_date"]').val()) {
                setTimeout(function() {
                    form.submit();
                }, 500);
            }
        });
    </script>
@endpush

@section('content')
    <!-- Filter Section -->
    <div class="row no-print">
        <div class="col-12">
            <div class="clean-card">
                <div class="card-header-clean">
                    <h6><i class="ti ti-filter me-2"></i>{{__('Attendance Report Filters')}}</h6>
                </div>
                <div class="card-body">
                    {{ Form::open(array('route' => array('report.monthly.attendance'),'method'=>'get','id'=>'report_monthly_attendance')) }}
                    <div class="row g-3">
                        <div class="col-lg-3 col-md-6">
                            <label class="form-label">{{__('Start Date')}}</label>
                            {{Form::date('start_date', isset($_GET['start_date']) ? $_GET['start_date'] : null, ['class'=>'form-control'])}}
                        </div>

                        <div class="col-lg-3 col-md-6">
                            <label class="form-label">{{__('End Date')}}</label>
                            {{Form::date('end_date', isset($_GET['end_date']) ? $_GET['end_date'] : null, ['class'=>'form-control'])}}
                        </div>

                        <div class="col-lg-3 col-md-6">
                            <label class="form-label">{{__('Branch')}}</label>
                            {{ Form::select('branch', $branch, isset($_GET['branch']) ? $_GET['branch'] : '', ['class' => 'form-select']) }}
                        </div>

                        <div class="col-lg-4 col-md-6 d-flex align-items-end">
                            <div class="btn-group w-100" role="group">
                                <button type="submit" class="btn btn-primary" id="generateReport">
                                    <i class="ti ti-search me-1"></i>{{__('Generate Report')}}
                                </button>
                                <a href="{{route('report.monthly.attendance')}}" class="btn btn-secondary">
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
        <p>{{__('Generating attendance report, please wait...')}}</p>
    </div>

    <!-- Summary Cards -->
    @if(!empty($employeesAttendance))
    <div class="row fade-in">
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="summary-card total-employees">
                <div class="summary-number">{{ count($employeesAttendance) }}</div>
                <div class="summary-label">{{__('Total Employees')}}</div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="summary-card present">
                <div class="summary-number">{{ $data['totalPresent'] }}</div>
                <div class="summary-label">{{__('Total Present Days')}}</div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="summary-card leave">
                <div class="summary-number">{{ $data['totalLeave'] }}</div>
                <div class="summary-label">{{__('Total Leave Days')}}</div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="summary-card percentage">
                <div class="summary-number">
                    {{ $data['totalPresent'] + $data['totalLeave'] > 0 ? round(($data['totalPresent'] / ($data['totalPresent'] + $data['totalLeave'])) * 100, 1) : 0 }}%
                </div>
                <div class="summary-label">{{__('Attendance Rate')}}</div>
            </div>
        </div>
    </div>
    @endif

    <!-- Report Content -->
    <div id="printableArea">
        @if(!empty($employeesAttendance))
        <div class="row fade-in">
            <div class="col-12">
                <div class="clean-card">
                    <div class="card-header-clean">
                        <h6><i class="ti ti-calendar-check me-2"></i>{{__('Monthly Attendance Report')}}</h6>
                        <small class="text-muted">
                            {{__('Period')}}: 
                            {{ isset($_GET['start_date']) ? date('d M Y', strtotime($_GET['start_date'])) : date('01 M Y') }} - 
                            {{ isset($_GET['end_date']) ? date('d M Y', strtotime($_GET['end_date'])) : date('t M Y') }}
                        </small>
                    </div>
                    <div class="card-body p-0">
                        <div class="attendance-table-responsive">
                            <table class="table table-attendance">
                                <thead>
                                    <tr>
                                        <th class="sticky-column">{{__('Employee Name')}}</th>
                                        @foreach($dates as $date)
                                            <th class="date-header">
                                                {{ date('d', strtotime($date)) }}<br>
                                                <small>{{ date('D', strtotime($date)) }}</small>
                                            </th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($employeesAttendance as $attendance)
                                        <tr>
                                            <td class="sticky-column employee-name">
                                                <div class="d-flex align-items-center">
                                                    <div class="employee-avatar me-2" style="width: 35px; height: 35px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 600; font-size: 0.8rem;">
                                                        {{ substr($attendance['name'], 0, 1) }}
                                                    </div>
                                                    <span>{{ $attendance['name'] }}</span>
                                                </div>
                                            </td>
                                            @foreach($dates as $date)
                                                <td>
                                                    @if($attendance['status'][$date] == 'P')
                                                        <div class="attendance-status">
                                                            <a href="https://www.google.com/maps/search/?api=1&query={{ $attendance['latitude'][$date] }},{{ $attendance['longitude'][$date] }}" 
                                                               target="_blank" 
                                                               class="map-link"
                                                               title="{{__('View location on map')}}"
                                                               data-bs-toggle="tooltip">
                                                                <i class="ti ti-map-pin status-present"></i>
                                                            </a>
                                                            <div class="distance-info">
                                                                {{ $attendance['radius'][$date] ?? '0' }} km
                                                            </div>
                                                        </div>
                                                    @elseif($attendance['status'][$date] == 'A')
                                                        <span class="status-absent">{{__('A')}}</span>
                                                    @elseif($attendance['status'][$date] == 'W')
                                                        <span class="status-weekend">{{__('W')}}</span>
                                                    @else
                                                        <span class="text-muted">-</span>
                                                    @endif
                                                </td>
                                            @endforeach
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Legend -->
        <div class="row fade-in mt-3">
            <div class="col-12">
                <div class="clean-card">
                    <div class="card-body">
                        <h6 class="mb-3"><i class="ti ti-info-circle me-2"></i>{{__('Legend')}}</h6>
                        <div class="row">
                            <div class="col-md-3 mb-2">
                                <div class="d-flex align-items-center">
                                    <i class="ti ti-map-pin status-present me-2"></i>
                                    <span>{{__('Present (with location)')}}</span>
                                </div>
                            </div>
                            <div class="col-md-3 mb-2">
                                <div class="d-flex align-items-center">
                                    <span class="status-absent me-2">A</span>
                                    <span>{{__('Absent')}}</span>
                                </div>
                            </div>
                            <div class="col-md-3 mb-2">
                                <div class="d-flex align-items-center">
                                    <span class="status-weekend me-2">W</span>
                                    <span>{{__('Weekend')}}</span>
                                </div>
                            </div>
                            <div class="col-md-3 mb-2">
                                <div class="d-flex align-items-center">
                                    <span class="text-muted me-2">-</span>
                                    <span>{{__('No data')}}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @else
            @if(request()->hasAny(['start_date', 'end_date', 'branch']))
            <!-- Empty state for filtered results -->
            <div class="row">
                <div class="col-12">
                    <div class="empty-state">
                        <div class="empty-state-icon">
                            <i class="ti ti-search-off"></i>
                        </div>
                        <h5>{{__('No Attendance Data Found')}}</h5>
                        <p>{{__('No attendance records found for the selected period and filters. Please try adjusting your date range or branch selection.')}}</p>
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
                        <h5>{{__('Generate Monthly Attendance Report')}}</h5>
                        <p>{{__('Please select date range and branch above to generate your monthly attendance report.')}}</p>
                    </div>
                </div>
            </div>
            @endif
        @endif
    </div>

    <!-- Hidden input for filename -->
    <input type="hidden" id="filename" value="Monthly_Attendance_Report_{{ isset($_GET['start_date']) ? date('Y-m-d', strtotime($_GET['start_date'])) : date('Y-m-d') }}.pdf">
@endsection