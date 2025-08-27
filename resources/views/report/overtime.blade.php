@extends('layouts.admin')

@section('page-title')
    {{ __('Manage Report Overtime') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Report Overtime') }}</li>
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

    /* Metrics display */
    .metric-display {
        font-weight: 600;
        color: #495057;
        background: #f8f9fa;
        padding: 0.375rem 0.75rem;
        border-radius: 8px;
        font-size: 0.9rem;
        text-align: center;
        min-width: 60px;
    }

    .working-days {
        background: linear-gradient(135deg, #e8f5e8 0%, #c8e6c9 100%);
        color: #2e7d32;
    }

    .overtime-days {
        background: linear-gradient(135deg, #fff3e0 0%, #ffe0b2 100%);
        color: #ef6c00;
    }

    .overtime-hours {
        background: linear-gradient(135deg, #fce4ec 0%, #f8bbd9 100%);
        color: #c2185b;
    }

    /* Action button */
    .detail-btn {
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

    .detail-btn:hover {
        color: white;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        text-decoration: none;
    }

    /* Progress bars */
    .efficiency-bar {
        width: 100%;
        height: 8px;
        background: #e9ecef;
        border-radius: 4px;
        overflow: hidden;
        margin-top: 0.5rem;
    }

    .efficiency-fill {
        height: 100%;
        background: linear-gradient(90deg, #28a745 0%, #20c997 100%);
        transition: width 0.3s ease;
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

    /* Performance indicator */
    .performance-indicator {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.25rem 0.5rem;
        border-radius: 12px;
        font-size: 0.75rem;
        font-weight: 600;
    }

    .performance-high {
        background: #d4edda;
        color: #155724;
    }

    .performance-medium {
        background: #fff3cd;
        color: #856404;
    }

    .performance-low {
        background: #f8d7da;
        color: #721c24;
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

    /* High overtime warning */
    .overtime-warning {
        position: relative;
    }

    .overtime-warning::after {
        content: '⚠️';
        position: absolute;
        top: -5px;
        right: -5px;
        font-size: 12px;
    }
</style>
@endpush

@section('content')
    <!-- Filter Section -->
    <div class="row no-print">
        <div class="col-12">
            <div class="clean-card">
                <div class="card-header-clean">
                    <h6><i class="ti ti-filter me-2"></i>{{ __('Overtime Report Filters') }}</h6>
                </div>
                <div class="card-body">
                    {{ Form::open(['route' => ['report.overtime'], 'method' => 'get', 'id' => 'report_overtime']) }}
                    
                    <div class="filter-grid">
                        <div>
                            <label class="form-label">{{ __('Month') }}</label>
                            {{ Form::month('month', isset($_GET['month']) ? $_GET['month'] : date('Y-m'), ['class' => 'form-control']) }}
                        </div>

                        <div>
                            <label class="form-label">{{ __('Branch') }}</label>
                            {{ Form::select('branch', $branch, isset($_GET['branch']) ? $_GET['branch'] : '', ['class' => 'form-select']) }}
                        </div>

                        <div class="d-flex align-items-end">
                            <div class="btn-group w-100" role="group">
                                <button type="submit" class="btn btn-primary" id="generateReport">
                                    <i class="ti ti-search me-1"></i>{{ __('Generate Report') }}
                                </button>
                                <a href="{{ route('report.overtime') }}" class="btn btn-secondary">
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
        <p>{{ __('Generating overtime report, please wait...') }}</p>
    </div>

    <!-- Summary Cards -->
    @if(!empty($employeesAttendance) && count($employeesAttendance) > 0)
    @php
        $totalEmployees = count($employeesAttendance);
        $totalOvertimeDays = array_sum(array_column($employeesAttendance, 'overtime'));
        $totalWorkingDays = array_sum(array_column($employeesAttendance, 'present'));
        
        // Calculate total overtime hours
        $totalOvertimeSeconds = 0;
        foreach($employeesAttendance as $attendance) {
            $timeParts = explode(':', $attendance['total_overtime']);
            $seconds = ($timeParts[0] * 3600) + ($timeParts[1] * 60) + ($timeParts[2] ?? 0);
            $totalOvertimeSeconds += $seconds;
        }
        
        $totalOvertimeFormatted = sprintf('%02d:%02d:%02d', 
            floor($totalOvertimeSeconds / 3600), 
            floor(($totalOvertimeSeconds % 3600) / 60), 
            ($totalOvertimeSeconds % 60)
        );
        
        $efficiencyRatio = $totalWorkingDays > 0 ? 
            round((($totalWorkingDays - $totalOvertimeDays) / $totalWorkingDays) * 100, 1) : 0;
    @endphp
    <div class="row fade-in">
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="summary-card total-employees">
                <div class="summary-number">{{ $totalEmployees }}</div>
                <div class="summary-label">{{ __('Total Employees') }}</div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="summary-card overtime-days">
                <div class="summary-number">{{ $totalOvertimeDays }}</div>
                <div class="summary-label">{{ __('Total Overtime Days') }}</div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="summary-card overtime-hours">
                <div class="summary-number">{{ $totalOvertimeFormatted }}</div>
                <div class="summary-label">{{ __('Total Overtime Hours') }}</div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="summary-card efficiency">
                <div class="summary-number">{{ $efficiencyRatio }}%</div>
                <div class="summary-label">{{ __('Regular Work Efficiency') }}</div>
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
                        <h6><i class="ti ti-clock me-2"></i>{{ __('Overtime Report') }}</h6>
                        <small class="text-muted">
                            {{ __('Period') }}: 
                            {{ isset($_GET['month']) ? date('M Y', strtotime($_GET['month'])) : date('M Y') }}
                            @if(isset($_GET['branch']) && $_GET['branch'])
                                | {{ __('Branch') }}: {{ $branch[$_GET['branch']] ?? __('All Branches') }}
                            @endif
                        </small>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-clean">
                                <thead>
                                    <tr>
                                        <th>{{ __('Employee') }}</th>
                                        <th>{{ __('Working Days') }}</th>
                                        <th>{{ __('Overtime Days') }}</th>
                                        <th>{{ __('Overtime Hours') }}</th>
                                        <th>{{ __('Performance') }}</th>
                                        <th>{{ __('Details') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($employeesAttendance as $attendance)
                                        @php
                                            // Calculate performance indicator
                                            $workingDays = $attendance['present'];
                                            $overtimeDays = $attendance['overtime'];
                                            $overtimeRatio = $workingDays > 0 ? ($overtimeDays / $workingDays) * 100 : 0;
                                            
                                            if ($overtimeRatio <= 10) {
                                                $performanceClass = 'performance-high';
                                                $performanceText = __('Excellent');
                                            } elseif ($overtimeRatio <= 25) {
                                                $performanceClass = 'performance-medium';
                                                $performanceText = __('Good');
                                            } else {
                                                $performanceClass = 'performance-low';
                                                $performanceText = __('Needs Review');
                                            }
                                            
                                            // Parse overtime hours for formatting
                                            $timeParts = explode(':', $attendance['total_overtime']);
                                            $overtimeSeconds = ($timeParts[0] * 3600) + ($timeParts[1] * 60) + ($timeParts[2] ?? 0);
                                            $formattedTime = sprintf('%02d:%02d:%02d', 
                                                floor($overtimeSeconds / 3600), 
                                                floor(($overtimeSeconds % 3600) / 60), 
                                                ($overtimeSeconds % 60)
                                            );
                                        @endphp
                                        <tr>
                                            <td data-label="Employee">
                                                <div class="employee-info">
                                                    <div class="employee-avatar">
                                                        {{ substr($attendance['name'], 0, 1) }}
                                                    </div>
                                                    <div class="employee-details">
                                                        <h6>{{ $attendance['name'] }}</h6>
                                                        <small>{{ __('ID') }}: {{ $attendance['id'] }}</small>
                                                    </div>
                                                </div>
                                            </td>

                                            <td data-label="Working Days">
                                                <div class="metric-display working-days">
                                                    {{ $attendance['present'] }} {{ __('days') }}
                                                </div>
                                            </td>

                                            <td data-label="Overtime Days">
                                                <div class="metric-display overtime-days {{ $attendance['overtime'] > 10 ? 'overtime-warning' : '' }}">
                                                    {{ $attendance['overtime'] }} {{ __('days') }}
                                                </div>
                                            </td>

                                            <td data-label="Overtime Hours">
                                                <div class="metric-display overtime-hours">
                                                    {{ $formattedTime }}
                                                </div>
                                            </td>

                                            <td data-label="Performance">
                                                <div class="text-center">
                                                    <div class="performance-indicator {{ $performanceClass }}">
                                                        {{ $performanceText }}
                                                    </div>
                                                    <div class="efficiency-bar">
                                                        <div class="efficiency-fill" style="width: {{ 100 - $overtimeRatio }}%"></div>
                                                    </div>
                                                    <small class="text-muted">{{ round($overtimeRatio, 1) }}% overtime</small>
                                                </div>
                                            </td>

                                            <td data-label="Details">
                                                <a href="#" 
                                                   class="detail-btn" 
                                                   data-url="{{ route('report.employee.overtime', [
                                                           $attendance['id'], 
                                                           isset($_GET['month']) ? $_GET['month'] : date('Y-m')
                                                       ]) }}" 
                                                   data-ajax-popup="true" 
                                                   data-title="{{ __('Overtime Details') }}" 
                                                   data-size="xl" 
                                                   data-bs-toggle="tooltip" 
                                                   title="{{ __('View Overtime Details') }}">
                                                    <i class="ti ti-eye"></i>
                                                    {{ __('View Details') }}
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

        <!-- Performance Legend -->
        <div class="row fade-in mt-3">
            <div class="col-12">
                <div class="clean-card">
                    <div class="card-body">
                        <h6 class="mb-3"><i class="ti ti-info-circle me-2"></i>{{ __('Performance Legend') }}</h6>
                        <div class="row">
                            <div class="col-md-4 mb-2">
                                <div class="d-flex align-items-center">
                                    <div class="performance-indicator performance-high me-2">{{ __('Excellent') }}</div>
                                    <span>{{ __('≤ 10% overtime ratio') }}</span>
                                </div>
                            </div>
                            <div class="col-md-4 mb-2">
                                <div class="d-flex align-items-center">
                                    <div class="performance-indicator performance-medium me-2">{{ __('Good') }}</div>
                                    <span>{{ __('11-25% overtime ratio') }}</span>
                                </div>
                            </div>
                            <div class="col-md-4 mb-2">
                                <div class="d-flex align-items-center">
                                    <div class="performance-indicator performance-low me-2">{{ __('Needs Review') }}</div>
                                    <span>{{ __('> 25% overtime ratio') }}</span>
                                </div>
                            </div>
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
                        <h5>{{ __('No Overtime Data Found') }}</h5>
                        <p>{{ __('No overtime records found for the selected period and filters. This could mean no employees worked overtime during this time.') }}</p>
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
                        <h5>{{ __('Generate Overtime Report') }}</h5>
                        <p>{{ __('Select month and branch above to generate your overtime report.') }}</p>
                    </div>
                </div>
            </div>
            @endif
        @endif
    </div>

    <!-- Hidden input for filename -->
    <input type="hidden" id="filename" value="Overtime_Report_{{ isset($_GET['month']) ? date('Y-m', strtotime($_GET['month'])) : date('Y-m') }}.pdf">
@endsection

@push('script-page')
    <script type="text/javascript" src="{{ asset('js/html2pdf.bundle.min.js') }}"></script>
    <script>
        $(document).ready(function() {
            // Initialize tooltips
            $('[data-bs-toggle="tooltip"]').tooltip();

            // Show loading spinner on form submit
            $('#report_overtime').on('submit', function() {
                $('#loadingSpinner').show();
                $('#generateReport').prop('disabled', true).html('<i class="spinner-border spinner-border-sm me-1"></i>{{ __("Generating...") }}');
            });

            // Auto-hide loading spinner if page loads with data
            @if(!empty($employeesAttendance) && count($employeesAttendance) > 0)
                $('#loadingSpinner').hide();
            @endif

            // Counter animation for summary cards
            @if(!empty($employeesAttendance) && count($employeesAttendance) > 0)
                setTimeout(animateCounters, 200);
                setTimeout(animateProgressBars, 400);
            @endif

            // Clean up any stuck modals from previous page loads
            $('.modal').remove();
            $('.modal-backdrop').remove();
            $('body').removeClass('modal-open');
            
            // Reset all buttons
            $('[data-ajax-popup]').prop('disabled', false);
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

        // Animate progress bars
        function animateProgressBars() {
            $('.efficiency-fill').each(function() {
                var width = $(this).css('width');
                $(this).css('width', '0%');
                $(this).animate({ width: width }, 1500);
            });
        }

        // PDF Export function
        function saveAsPDF() {
            var filename = $('#filename').val() || 'Overtime_Report.pdf';
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
                '{{ __("Working Days") }}',
                '{{ __("Overtime Days") }}',
                '{{ __("Overtime Hours") }}',
                '{{ __("Performance") }}',
                '{{ __("Overtime Ratio") }}%'
            ]);
            
            $('.table-clean tbody tr').each(function() {
                var row = [];
                var employeeName = $(this).find('.employee-details h6').text().trim();
                var workingDays = $(this).find('.working-days').text().replace(' days', '').trim();
                var overtimeDays = $(this).find('.overtime-days').text().replace(' days', '').trim();
                var overtimeHours = $(this).find('.overtime-hours').text().trim();
                var performance = $(this).find('.performance-indicator').text().trim();
                var overtimeRatio = $(this).find('small').text().replace('% overtime', '').trim();
                
                row.push(employeeName, workingDays, overtimeDays, overtimeHours, performance, overtimeRatio);
                csvData.push(row);
            });
            
            // Convert to CSV string
            var csvString = csvData.map(row => row.join(',')).join('\n');
            
            // Download CSV
            var blob = new Blob([csvString], { type: 'text/csv;charset=utf-8;' });
            var link = document.createElement('a');
            var url = URL.createObjectURL(blob);
            link.setAttribute('href', url);
            link.setAttribute('download', 'overtime_report.csv');
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

        // FIXED: Enhanced AJAX popups for overtime details - prevent duplicate modals
        $(document).off('click', '[data-ajax-popup]').on('click', '[data-ajax-popup]', function(e) {
            e.preventDefault();
            e.stopPropagation(); // Prevent event bubbling
            
            var url = $(this).data('url');
            var title = $(this).data('title');
            var size = $(this).data('size') || 'lg';
            var $btn = $(this);
            
            // Check if modal is already open - prevent duplicate
            if ($('.modal.show').length > 0) {
                console.log('Modal already open, ignoring click');
                return false;
            }
            
            // Add loading state
            var originalContent = $btn.html();
            $btn.html('<i class="spinner-border spinner-border-sm me-1"></i>{{ __("Loading...") }}');
            $btn.prop('disabled', true);
            
            // Clear any existing modals first
            $('.modal').remove();
            $('.modal-backdrop').remove();
            $('body').removeClass('modal-open');
            
            // Make AJAX request
            $.ajax({
                url: url,
                type: 'GET',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                timeout: 10000, // 10 second timeout
                success: function(response) {
                    // Ensure clean slate
                    $('.modal').remove();
                    $('.modal-backdrop').remove();
                    
                    // Create unique modal ID
                    var modalId = 'overtime-modal-' + Date.now();
                    
                    // Create modal with improved structure
                    var modal = $(`
                        <div class="modal fade" id="${modalId}" tabindex="-1" role="dialog" data-bs-backdrop="static" data-bs-keyboard="false">
                            <div class="modal-dialog modal-${size} modal-dialog-scrollable" role="document">
                                <div class="modal-content">
                                    <div class="modal-header bg-primary text-white">
                                        <h5 class="modal-title">${title}</h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body p-3">
                                        ${response}
                                    </div>
                                    <div class="modal-footer bg-light">
                                        <button type="button" class="btn btn-outline-primary export-individual me-2">
                                            <i class="ti ti-download me-1"></i>{{ __('Export Data') }}
                                        </button>
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                            <i class="ti ti-x me-1"></i>{{ __('Close') }}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `);
                    
                    // Append to body
                    $('body').append(modal);
                    
                    // Initialize modal with proper event handling
                    var modalInstance = new bootstrap.Modal(document.getElementById(modalId), {
                        backdrop: 'static',
                        keyboard: false
                    });
                    
                    // Show modal
                    modalInstance.show();
                    
                    // Handle modal close events
                    modal.on('hidden.bs.modal', function() {
                        // Clean up completely
                        modalInstance.dispose();
                        modal.remove();
                        $('.modal-backdrop').remove();
                        $('body').removeClass('modal-open');
                        
                        // Restore button state
                        $btn.html(originalContent);
                        $btn.prop('disabled', false);
                        
                        console.log('Modal cleaned up successfully');
                    });
                    
                    // Prevent multiple modal opening
                    modal.on('show.bs.modal', function() {
                        // Disable all other ajax-popup buttons temporarily
                        $('[data-ajax-popup]').not($btn).prop('disabled', true);
                    });
                    
                    modal.on('shown.bs.modal', function() {
                        // Re-enable buttons after modal is fully shown
                        setTimeout(function() {
                            $('[data-ajax-popup]').prop('disabled', false);
                        }, 500);
                    });
                    
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', error, xhr.responseText);
                    
                    let errorMessage = '{{ __("Failed to load overtime details") }}';
                    if (status === 'timeout') {
                        errorMessage = '{{ __("Request timed out. Please try again.") }}';
                    } else if (xhr.status === 404) {
                        errorMessage = '{{ __("Overtime details not found.") }}';
                    } else if (xhr.status === 500) {
                        errorMessage = '{{ __("Server error occurred. Please contact administrator.") }}';
                    }
                    
                    showNotification('error', errorMessage, '{{ __("Error") }}');
                },
                complete: function() {
                    // Always restore button state
                    setTimeout(function() {
                        $btn.html(originalContent);
                        $btn.prop('disabled', false);
                    }, 1000);
                }
            });
        });

        // Prevent default modal behavior conflicts
        $(document).on('click', '.modal-backdrop', function(e) {
            e.stopPropagation();
        });

        // Handle escape key properly
        $(document).keydown(function(e) {
            if (e.keyCode === 27) { // Escape key
                var openModal = $('.modal.show');
                if (openModal.length) {
                    openModal.modal('hide');
                }
            }
        });

        // Additional cleanup when leaving page
        $(window).on('beforeunload', function() {
            $('.modal').modal('hide');
            $('.modal').remove();
            $('.modal-backdrop').remove();
        });

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
                
                // Handle numbers (including "X days" format)
                var numA = parseInt(valA.replace(/[^\d]/g, ''));
                var numB = parseInt(valB.replace(/[^\d]/g, ''));
                
                if (!isNaN(numA) && !isNaN(numB)) {
                    return numA - numB;
                }
                
                return valA.toString().localeCompare(valB);
            };
        }

        function getCellValue(row, index) {
            var cell = $(row).children('td').eq(index);
            var metricDisplay = cell.find('.metric-display');
            
            if (metricDisplay.length) {
                return metricDisplay.text().trim();
            }
            
            var employeeName = cell.find('.employee-details h6');
            if (employeeName.length) {
                return employeeName.text().trim();
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

        // Smooth scrolling
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

        // Auto-save form preferences
        $('#report_overtime').on('change', 'input, select', function() {
            var formData = {
                month: $('input[name="month"]').val(),
                branch: $('select[name="branch"]').val()
            };
            
            localStorage.setItem('overtime_report_preferences', JSON.stringify(formData));
        });

        // Restore form preferences on page load
        $(document).ready(function() {
            var savedPreferences = localStorage.getItem('overtime_report_preferences');
            
            if (savedPreferences && !window.location.search) {
                try {
                    var preferences = JSON.parse(savedPreferences);
                    
                    if (preferences.month) {
                        $('input[name="month"]').val(preferences.month);
                    }
                    if (preferences.branch) {
                        $('select[name="branch"]').val(preferences.branch);
                    }
                } catch (e) {
                    console.warn('Error restoring preferences:', e);
                }
            }
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
                window.location.href = '{{ route("report.overtime") }}';
            }
        });

        // Performance warnings
        $(document).ready(function() {
            var highOvertimeCount = 0;
            $('.overtime-warning').each(function() {
                highOvertimeCount++;
            });
            
            if (highOvertimeCount > 0) {
                showNotification('warning', 
                    `{{ __("Found") }} ${highOvertimeCount} {{ __("employees with high overtime days. Consider reviewing workload distribution.") }}`, 
                    '{{ __("Performance Alert") }}'
                );
            }
        });

        // Enhanced visual feedback
        $('<style>')
            .prop('type', 'text/css')
            .html(`
                .detail-btn:active {
                    transform: translateY(0px) !important;
                    box-shadow: 0 2px 4px rgba(0,0,0,0.1) !important;
                }
                
                .efficiency-fill {
                    position: relative;
                    overflow: hidden;
                }
                
                .efficiency-fill::after {
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
                    transition: all 0.2s ease;
                }
                
                .table-clean tbody tr:hover {
                    transform: translateY(-1px);
                    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
                }
                
                .overtime-warning {
                    animation: pulse 2s infinite;
                }
                
                @keyframes pulse {
                    0% { transform: scale(1); }
                    50% { transform: scale(1.05); }
                    100% { transform: scale(1); }
                }
            `)
            .appendTo('head');

        // Month change auto-submit
        $('input[name="month"]').on('change', function() {
            var selectedMonth = $(this).val();
            var currentMonth = new Date().toISOString().slice(0, 7);
            
            // Auto-submit if month is changed and is not future date
            if (selectedMonth && selectedMonth <= currentMonth) {
                setTimeout(function() {
                    $('#report_overtime').submit();
                }, 1000);
            }
        });

        // Performance monitoring
        $(document).ready(function() {
            var tableRows = $('.table-clean tbody tr').length;
            
            if (tableRows > 100) {
                showNotification('info', '{{ __("Large dataset detected. Consider using month filters for better performance.") }}', '{{ __("Performance Tip") }}');
            }
        });
    </script>
@endpush