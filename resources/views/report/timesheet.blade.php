@extends('layouts.admin')

@section('page-title')
    {{ __('Manage Timesheet Report') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Timesheet Report') }}</li>
@endsection

@section('action-btn')
    <div class="float-end">
        @if(!empty($employeesAttendance) && count($employeesAttendance) > 0)
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

    .summary-card.total-employees {
        background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
    }

    .summary-card.total-working {
        background: linear-gradient(135deg, #66bb6a 0%, #4caf50 100%);
    }

    .summary-card.total-meeting {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    }

    .summary-card.productivity {
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

    /* Time display */
    .time-display {
        font-family: 'Courier New', monospace;
        font-weight: 600;
        color: #495057;
        background: #f8f9fa;
        padding: 0.375rem 0.75rem;
        border-radius: 8px;
        font-size: 0.9rem;
        text-align: center;
        min-width: 80px;
    }

    .working-hours {
        background: linear-gradient(135deg, #e8f5e8 0%, #c8e6c9 100%);
        color: #2e7d32;
    }

    .meeting-hours {
        background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
        color: #1976d2;
    }

    /* Action buttons */
    .action-btn {
        background: linear-gradient(135deg, #2196f3 0%, #1976d2 100%);
        color: white;
        padding: 0.5rem 1rem;
        border-radius: 6px;
        text-decoration: none;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        transition: all 0.2s ease;
        font-size: 0.875rem;
        border: none;
        cursor: pointer;
    }

    .action-btn:hover {
        color: white;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        text-decoration: none;
    }

    .action-btn.meeting {
        background: linear-gradient(135deg, #9c27b0 0%, #673ab7 100%);
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

    /* Filter section styling */
    .filter-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
        margin-bottom: 1rem;
    }

    /* Progress bars for productivity */
    .productivity-bar {
        width: 100%;
        height: 8px;
        background: #e9ecef;
        border-radius: 4px;
        overflow: hidden;
        margin-top: 0.5rem;
    }

    .productivity-fill {
        height: 100%;
        background: linear-gradient(90deg, #28a745 0%, #20c997 100%);
        transition: width 0.3s ease;
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

        .filter-grid {
            grid-template-columns: 1fr;
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
                    <h6><i class="ti ti-filter me-2"></i>{{ __('Timesheet Report Filters') }}</h6>
                </div>
                <div class="card-body">
                    {{ Form::open(['route' => ['report.timesheet'], 'method' => 'get', 'id' => 'report_timesheet']) }}
                    
                    <div class="filter-grid">
                        <div>
                            <label class="form-label">{{ __('From Date') }}</label>
                            {{ Form::date('start_date', isset($_GET['start_date']) ? $_GET['start_date'] : '', ['class' => 'form-control']) }}
                        </div>

                        <div>
                            <label class="form-label">{{ __('To Date') }}</label>
                            {{ Form::date('end_date', isset($_GET['end_date']) ? $_GET['end_date'] : '', ['class' => 'form-control']) }}
                        </div>

                        <div>
                            <label class="form-label">{{ __('Branch') }}</label>
                            {{ Form::select('branch', $branch, isset($_GET['branch']) ? $_GET['branch'] : '', ['class' => 'form-select']) }}
                        </div>

                        <div class="col-lg-4 col-md-6 d-flex align-items-end">
                            <div class="btn-group w-100" role="group">
                                <button type="submit" class="btn btn-primary" id="generateReport">
                                    <i class="ti ti-search me-1"></i>{{__('Generate Report')}}
                                </button>
                                <a href="{{route('report.timesheet')}}" class="btn btn-secondary">
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
        <p>{{ __('Generating timesheet report, please wait...') }}</p>
    </div>

    <!-- Summary Cards -->
    @if(!empty($employeesAttendance) && count($employeesAttendance) > 0)
    @php
        $totalEmployees = count($employeesAttendance);
        $totalWorkingSeconds = 0;
        $totalMeetingSeconds = 0;
        
        foreach($employeesAttendance as $attendance) {
            // Convert working hours to seconds
            $workingParts = explode(':', $attendance['total_working_hours']);
            $totalWorkingSeconds += ($workingParts[0] * 3600) + ($workingParts[1] * 60) + $workingParts[2];
            
            // Convert meeting hours to seconds
            $meetingParts = explode(':', $attendance['total_meeting_hours']);
            $totalMeetingSeconds += ($meetingParts[0] * 3600) + ($meetingParts[1] * 60) + $meetingParts[2];
        }
        
        $totalWorkingHours = sprintf('%02d:%02d:%02d', 
            floor($totalWorkingSeconds / 3600), 
            floor(($totalWorkingSeconds % 3600) / 60), 
            ($totalWorkingSeconds % 60)
        );
        
        $totalMeetingHours = sprintf('%02d:%02d:%02d', 
            floor($totalMeetingSeconds / 3600), 
            floor(($totalMeetingSeconds % 3600) / 60), 
            ($totalMeetingSeconds % 60)
        );
        
        $productivityRatio = $totalWorkingSeconds > 0 ? 
            round(($totalWorkingSeconds / ($totalWorkingSeconds + $totalMeetingSeconds)) * 100, 1) : 0;
    @endphp
    <div class="row fade-in">
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="summary-card total-employees">
                <div class="summary-number">{{ $totalEmployees }}</div>
                <div class="summary-label">{{ __('Total Employees') }}</div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="summary-card total-working">
                <div class="summary-number">{{ $totalWorkingHours }}</div>
                <div class="summary-label">{{ __('Total Working Hours') }}</div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="summary-card total-meeting">
                <div class="summary-number">{{ $totalMeetingHours }}</div>
                <div class="summary-label">{{ __('Total Meeting Hours') }}</div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="summary-card productivity">
                <div class="summary-number">{{ $productivityRatio }}%</div>
                <div class="summary-label">{{ __('Work/Meeting Ratio') }}</div>
            </div>
        </div>
    </div>
    @endif

    <!-- Report Content -->
    <div id="printableArea">
        @if(!empty($employeesAttendance) && count($employeesAttendance) > 0)
        <div class="row fade-in">
            <div class="col-12">
                <div class="clean-card">
                    <div class="card-header-clean">
                        <h6><i class="ti ti-clock me-2"></i>{{ __('Timesheet Report') }}</h6>
                        <small class="text-muted">
                            {{ __('Period') }}: 
                            {{ isset($_GET['start_date']) ? date('d M Y', strtotime($_GET['start_date'])) : __('All Time') }} 
                            @if(isset($_GET['end_date']))
                                - {{ date('d M Y', strtotime($_GET['end_date'])) }}
                            @endif
                        </small>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-clean">
                                <thead>
                                    <tr>
                                        <th>{{ __('Employee') }}</th>
                                        <th>{{ __('Working Hours') }}</th>
                                        <th>{{ __('Meeting Hours') }}</th>
                                        <th>{{ __('Productivity') }}</th>
                                        <th>{{ __('Timesheet Details') }}</th>
                                        <th>{{ __('Meeting Details') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($employeesAttendance as $attendance)
                                        @php
                                            // Calculate productivity percentage
                                            $workingParts = explode(':', $attendance['total_working_hours']);
                                            $workingSeconds = ($workingParts[0] * 3600) + ($workingParts[1] * 60) + $workingParts[2];
                                            
                                            $meetingParts = explode(':', $attendance['total_meeting_hours']);
                                            $meetingSeconds = ($meetingParts[0] * 3600) + ($meetingParts[1] * 60) + $meetingParts[2];
                                            
                                            $totalSeconds = $workingSeconds + $meetingSeconds;
                                            $productivity = $totalSeconds > 0 ? round(($workingSeconds / $totalSeconds) * 100, 1) : 0;
                                        @endphp
                                        <tr>
                                            <td data-label="Employee">
                                                <div class="employee-info">
                                                    <div class="employee-avatar">
                                                        {{ substr($attendance['name'], 0, 1) }}
                                                    </div>
                                                    <div class="employee-details">
                                                        <h6>{{ $attendance['name'] }}</h6>
                                                        <small>{{ __('Employee ID') }}: {{ $attendance['id'] }}</small>
                                                    </div>
                                                </div>
                                            </td>

                                            <td data-label="Working Hours">
                                                <div class="time-display working-hours">
                                                    {{ $attendance['total_working_hours'] }}
                                                </div>
                                            </td>

                                            <td data-label="Meeting Hours">
                                                <div class="time-display meeting-hours">
                                                    {{ $attendance['total_meeting_hours'] }}
                                                </div>
                                            </td>

                                            <td data-label="Productivity">
                                                <div class="text-center">
                                                    <strong>{{ $productivity }}%</strong>
                                                    <div class="productivity-bar">
                                                        <div class="productivity-fill" style="width: {{ $productivity }}%"></div>
                                                    </div>
                                                </div>
                                            </td>

                                            <td data-label="Timesheet Details">
                                                <a href="#" 
                                                   class="action-btn" 
                                                   data-url="{{ route('report.employee.timesheet', [
                                                           $attendance['id'], 
                                                           isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01'),
                                                           isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-t')
                                                       ]) }}" 
                                                   data-ajax-popup="true" 
                                                   data-title="{{ __('Timesheet Details') }}" 
                                                   data-size="xl" 
                                                   data-bs-toggle="tooltip" 
                                                   title="{{ __('View Timesheet Details') }}">
                                                    <i class="ti ti-clock"></i>
                                                </a>
                                            </td>

                                            <td data-label="Meeting Details">
                                                <a href="#" 
                                                   class="action-btn meeting" 
                                                   data-url="{{ route('report.employee.meeting', [
                                                           $attendance['id'], 
                                                           isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01'),
                                                           isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-t')
                                                       ]) }}" 
                                                   data-ajax-popup="true" 
                                                   data-title="{{ __('Meeting Details') }}" 
                                                   data-size="xl" 
                                                   data-bs-toggle="tooltip" 
                                                   title="{{ __('View Meeting Details') }}">
                                                    <i class="ti ti-users"></i>
                                                </a>
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
            @if(request()->hasAny(['start_date', 'end_date', 'branch']))
            <!-- Empty state for filtered results -->
            <div class="row">
                <div class="col-12">
                    <div class="empty-state">
                        <div class="empty-state-icon">
                            <i class="ti ti-search-off"></i>
                        </div>
                        <h5>{{ __('No Timesheet Data Found') }}</h5>
                        <p>{{ __('No timesheet records found for the selected period and filters. Please try adjusting your date range or branch selection.') }}</p>
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
                        <h5>{{ __('Generate Timesheet Report') }}</h5>
                        <p>{{ __('Select date range and branch above to generate your timesheet report.') }}</p>
                    </div>
                </div>
            </div>
            @endif
        @endif
    </div>

    <!-- Hidden input for filename -->
    <input type="hidden" id="filename" value="Timesheet_Report_{{ isset($_GET['start_date']) ? date('Y-m-d', strtotime($_GET['start_date'])) : date('Y-m-d') }}.pdf">
@endsection

@push('script-page')
    <script type="text/javascript" src="{{ asset('js/html2pdf.bundle.min.js') }}"></script>
    <script>
        $(document).ready(function() {
            // Initialize tooltips
            $('[data-bs-toggle="tooltip"]').tooltip();

            // Show loading spinner on form submit
            $('#report_timesheet').on('submit', function() {
                $('#loadingSpinner').show();
                $('#generateReport').prop('disabled', true).html('<i class="spinner-border spinner-border-sm me-1"></i>{{ __("Generating...") }}');
            });

            // Auto-hide loading spinner if page loads with data
            @if(!empty($employeesAttendance) && count($employeesAttendance) > 0)
                $('#loadingSpinner').hide();
            @endif

            // Form validation
            $('#report_timesheet').on('submit', function(e) {
                var startDate = $('input[name="start_date"]').val();
                var endDate = $('input[name="end_date"]').val();
                
                if (startDate && endDate && new Date(startDate) > new Date(endDate)) {
                    e.preventDefault();
                    showNotification('error', '{{ __("Start date cannot be greater than end date") }}', '{{ __("Validation Error") }}');
                    $('#loadingSpinner').hide();
                    $('#generateReport').prop('disabled', false).html('<i class="ti ti-search me-1"></i>{{ __("Generate Report") }}');
                    return false;
                }
            });

            // Counter animation for summary cards
            @if(!empty($employeesAttendance) && count($employeesAttendance) > 0)
                setTimeout(animateCounters, 200);
            @endif
        });

        // Animate counter numbers
        function animateCounters() {
            $('.summary-number').each(function() {
                var $this = $(this);
                var text = $this.text().trim();
                
                // Skip animation for time format (contains :) and percentage
                if (text.includes(':') || text.includes('%')) {
                    return;
                }
                
                var countTo = parseInt(text.replace(/,/g, ''));
                if (isNaN(countTo)) return;
                
                $({ countNum: 0 }).animate({
                    countNum: countTo
                }, {
                    duration: 1500,
                    easing: 'swing',
                    step: function() {
                        $this.text(Math.floor(this.countNum).toLocaleString());
                    },
                    complete: function() {
                        $this.text(countTo.toLocaleString());
                    }
                });
            });
        }

        // PDF Export function
        function saveAsPDF() {
            var filename = $('#filename').val() || 'Timesheet_Report.pdf';
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
            showNotification('info', '{{ __("Preparing Excel export...") }}', '{{ __("Please Wait") }}');
            
            // Create CSV data
            var csvData = [];
            csvData.push([
                '{{ __("Employee Name") }}',
                '{{ __("Working Hours") }}',
                '{{ __("Meeting Hours") }}',
                '{{ __("Productivity") }}%'
            ]);
            
            $('.table-clean tbody tr').each(function() {
                var row = [];
                var employeeName = $(this).find('.employee-details h6').text().trim();
                var workingHours = $(this).find('.working-hours').text().trim();
                var meetingHours = $(this).find('.meeting-hours').text().trim();
                var productivity = $(this).find('td:nth-child(4) strong').text().replace('%', '').trim();
                
                row.push(employeeName, workingHours, meetingHours, productivity);
                csvData.push(row);
            });
            
            // Convert to CSV string
            var csvString = csvData.map(row => row.join(',')).join('\n');
            
            // Download CSV
            var blob = new Blob([csvString], { type: 'text/csv;charset=utf-8;' });
            var link = document.createElement('a');
            var url = URL.createObjectURL(blob);
            link.setAttribute('href', url);
            link.setAttribute('download', 'timesheet_report.csv');
            link.style.visibility = 'hidden';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            
            showNotification('success', '{{ __("Excel export completed") }}', '{{ __("Success") }}');
        }

        // Notification helper
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

        // Date range validation
        $('input[type="date"]').on('change', function() {
            var startDate = $('input[name="start_date"]').val();
            var endDate = $('input[name="end_date"]').val();
            
            if (startDate && endDate) {
                var daysDiff = Math.ceil((new Date(endDate) - new Date(startDate)) / (1000 * 60 * 60 * 24));
                
                if (daysDiff > 365) {
                    showNotification('warning', '{{ __("Date range is quite large. This may affect performance.") }}', '{{ __("Notice") }}');
                } else if (daysDiff < 0) {
                    showNotification('error', '{{ __("End date must be after start date") }}', '{{ __("Invalid Date Range") }}');
                }
            }
        });

        // Auto-submit form when date range is complete
        $('input[name="end_date"]').on('change', function() {
            var startDate = $('input[name="start_date"]').val();
            var endDate = $(this).val();
            
            if (startDate && endDate && new Date(startDate) <= new Date(endDate)) {
                // Auto-submit after 1 second delay
                setTimeout(function() {
                    $('#report_timesheet').submit();
                }, 1000);
            }
        });

        // Smooth scrolling for better UX
        @if(!empty($employeesAttendance) && count($employeesAttendance) > 0)
            $(document).ready(function() {
                setTimeout(function() {
                    if ($('.summary-card').length > 0 && $(window).scrollTop() === 0) {
                        $('html, body').animate({
                            scrollTop: $('.summary-card').first().offset().top - 100
                        }, 800);
                    }
                }, 500);
            });
        @endif

        // Enhanced productivity bar animation
        $(document).ready(function() {
            $('.productivity-fill').each(function() {
                var width = $(this).css('width');
                $(this).css('width', '0%');
                $(this).animate({ width: width }, 1500);
            });
        });

        // Keyboard shortcuts
        $(document).keydown(function(e) {
            // Ctrl+P for print
            if (e.ctrlKey && e.keyCode === 80) {
                e.preventDefault();
                saveAsPDF();
            }
            
            // Ctrl+E for Excel export
            if (e.ctrlKey && e.keyCode === 69) {
                e.preventDefault();
                exportToExcel();
            }
            
            // Ctrl+R to reset form
            if (e.ctrlKey && e.keyCode === 82) {
                e.preventDefault();
                window.location.href = '{{ route("report.timesheet") }}';
            }
        });

        // Print functionality
        function printReport() {
            var printWindow = window.open('', '_blank');
            var printContent = document.getElementById('printableArea').innerHTML;
            
            printWindow.document.write(`
                <html>
                    <head>
                        <title>{{ __('Timesheet Report') }}</title>
                        <style>
                            body { font-family: Arial, sans-serif; margin: 20px; }
                            table { width: 100%; border-collapse: collapse; margin-top: 20px; }
                            th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                            th { background-color: #f8f9fa; font-weight: bold; }
                            .time-display { font-family: monospace; background: #f8f9fa; padding: 4px 8px; border-radius: 4px; }
                            .working-hours { background: #e8f5e8; color: #2e7d32; }
                            .meeting-hours { background: #e3f2fd; color: #1976d2; }
                            .no-print { display: none; }
                        </style>
                    </head>
                    <body>
                        <h2>{{ __('Timesheet Report') }}</h2>
                        <p>{{ __('Generated on') }}: ${new Date().toLocaleDateString()}</p>
                        ${printContent}
                    </body>
                </html>
            `);
            
            printWindow.document.close();
            printWindow.print();
        }

        // Table sorting functionality
        $(document).ready(function() {
            $('.table-clean th').addClass('sortable').css('cursor', 'pointer').on('click', function() {
                var table = $(this).parents('table').eq(0);
                var rows = table.find('tr:gt(0)').toArray().sort(comparer($(this).index()));
                
                this.asc = !this.asc;
                if (!this.asc) {
                    rows = rows.reverse();
                }
                
                for (var i = 0; i < rows.length; i++) {
                    table.append(rows[i]);
                }
                
                // Update sort indicators
                table.find('th').removeClass('sort-asc sort-desc');
                $(this).addClass(this.asc ? 'sort-asc' : 'sort-desc');
            });
        });

        function comparer(index) {
            return function(a, b) {
                var valA = getCellValue(a, index);
                var valB = getCellValue(b, index);
                
                // Special handling for time format (HH:MM:SS)
                if (valA.match(/^\d{2}:\d{2}:\d{2}$/) && valB.match(/^\d{2}:\d{2}:\d{2}$/)) {
                    var timeA = valA.split(':').reduce((acc, val, idx) => acc + parseInt(val) * Math.pow(60, 2 - idx), 0);
                    var timeB = valB.split(':').reduce((acc, val, idx) => acc + parseInt(val) * Math.pow(60, 2 - idx), 0);
                    return timeA - timeB;
                }
                
                return $.isNumeric(valA) && $.isNumeric(valB) ? valA - valB : valA.toString().localeCompare(valB);
            };
        }

        function getCellValue(row, index) {
            var cell = $(row).children('td').eq(index);
            var timeDisplay = cell.find('.time-display');
            
            if (timeDisplay.length) {
                return timeDisplay.text().trim();
            }
            
            return cell.text().trim();
        }

        // Add CSS for sort indicators
        $('<style>')
            .prop('type', 'text/css')
            .html(`
                .sortable:hover { background-color: #e9ecef !important; }
                .sort-asc:after { content: " ↑"; color: #007bff; }
                .sort-desc:after { content: " ↓"; color: #007bff; }
            `)
            .appendTo('head');

        // Performance monitoring
        $(document).ready(function() {
            var tableRows = $('.table-clean tbody tr').length;
            
            if (tableRows > 50) {
                showNotification('info', '{{ __("Large dataset detected. Use date filters for better performance.") }}', '{{ __("Performance Tip") }}');
            }
            
            // Monitor page load performance
            window.addEventListener('load', function() {
                var loadTime = performance.now();
                if (loadTime > 3000) {
                    console.warn('Page load time exceeded 3 seconds:', loadTime + 'ms');
                }
            });
        });

        // Auto-save form preferences
        $('#report_timesheet').on('change', 'input, select', function() {
            var formData = {
                start_date: $('input[name="start_date"]').val(),
                end_date: $('input[name="end_date"]').val(),
                branch: $('select[name="branch"]').val()
            };
            
            localStorage.setItem('timesheet_report_preferences', JSON.stringify(formData));
        });

        // Restore form preferences on page load
        $(document).ready(function() {
            var savedPreferences = localStorage.getItem('timesheet_report_preferences');
            
            if (savedPreferences && !window.location.search) {
                try {
                    var preferences = JSON.parse(savedPreferences);
                    
                    if (preferences.start_date) {
                        $('input[name="start_date"]').val(preferences.start_date);
                    }
                    if (preferences.end_date) {
                        $('input[name="end_date"]').val(preferences.end_date);
                    }
                    if (preferences.branch) {
                        $('select[name="branch"]').val(preferences.branch);
                    }
                } catch (e) {
                    console.warn('Error restoring preferences:', e);
                }
            }
        });

        // Enhanced error handling for AJAX popups
        $(document).on('click', '[data-ajax-popup]', function(e) {
            e.preventDefault();
            
            var url = $(this).data('url');
            var title = $(this).data('title');
            var size = $(this).data('size') || 'lg';
            var $btn = $(this);
            
            // Add loading state
            var originalContent = $btn.html();
            $btn.html('<i class="spinner-border spinner-border-sm me-1"></i>Loading...');
            $btn.prop('disabled', true);
            
            // Make AJAX request
            $.ajax({
                url: url,
                type: 'GET',
                success: function(response) {
                    // Create modal if it doesn't exist
                    var modalId = 'ajax-modal-' + Math.random().toString(36).substr(2, 9);
                    var modal = $(`
                        <div class="modal fade" id="${modalId}" tabindex="-1" role="dialog">
                            <div class="modal-dialog modal-${size}" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">${title}</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        ${response}
                                    </div>
                                </div>
                            </div>
                        </div>
                    `);
                    
                    $('body').append(modal);
                    modal.modal('show');
                    
                    // Clean up modal when closed
                    modal.on('hidden.bs.modal', function() {
                        modal.remove();
                    });
                },
                error: function(xhr, status, error) {
                    showNotification('error', '{{ __("Failed to load details") }}', '{{ __("Error") }}');
                    console.error('AJAX Error:', error);
                },
                complete: function() {
                    // Restore button state
                    $btn.html(originalContent);
                    $btn.prop('disabled', false);
                }
            });
        });

        // Add custom CSS for enhanced visual feedback
        $('<style>')
            .prop('type', 'text/css')
            .html(`
                .action-btn:active {
                    transform: translateY(0px) !important;
                    box-shadow: 0 2px 4px rgba(0,0,0,0.1) !important;
                }
                
                .productivity-fill {
                    position: relative;
                    overflow: hidden;
                }
                
                .productivity-fill::after {
                    content: '';
                    position: absolute;
                    top: 0;
                    left: -100%;
                    width: 100%;
                    height: 100%;
                    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
                    animation: shimmer 2s infinite;
                }
                
                @keyframes shimmer {
                    0% { left: -100%; }
                    100% { left: 100%; }
                }
                
                .table-clean tbody tr {
                    transition: background-color 0.2s ease;
                }
                
                .loading-spinner {
                    backdrop-filter: blur(2px);
                    background: rgba(255, 255, 255, 0.8);
                }
            `)
            .appendTo('head');
    </script>
@endpush