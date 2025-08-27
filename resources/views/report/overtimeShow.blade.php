{{-- Overtime Detail Modal Content --}}
<style>
    .modal-header-custom {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 8px 8px 0 0;
    }

    .summary-stats {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
        border-left: 4px solid #007bff;
    }

    .stat-item {
        text-align: center;
        padding: 1rem;
    }

    .stat-number {
        font-size: 1.5rem;
        font-weight: 700;
        color: #495057;
        margin-bottom: 0.5rem;
    }

    .stat-label {
        font-size: 0.875rem;
        color: #6c757d;
        font-weight: 500;
    }

    .overtime-table {
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    .overtime-table thead th {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border: none;
        padding: 1rem 0.75rem;
        font-weight: 600;
        color: #495057;
        font-size: 0.875rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .overtime-table tbody td {
        padding: 1rem 0.75rem;
        vertical-align: middle;
        border-top: 1px solid #dee2e6;
    }

    .overtime-table tbody tr:hover {
        background-color: #f8f9fa;
    }

    .date-display {
        background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
        color: #1976d2;
        padding: 0.5rem 1rem;
        border-radius: 8px;
        font-weight: 600;
        font-size: 0.875rem;
        text-align: center;
    }

    .time-display {
        font-family: 'Courier New', monospace;
        background: #f8f9fa;
        padding: 0.375rem 0.75rem;
        border-radius: 6px;
        font-weight: 600;
        color: #495057;
        text-align: center;
        min-width: 80px;
    }

    .weekend-badge {
        background: linear-gradient(135deg, #66bb6a 0%, #4caf50 100%);
        color: white;
        padding: 0.375rem 0.75rem;
        border-radius: 12px;
        font-weight: 600;
        font-size: 0.8rem;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }

    .weekday-badge {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        color: white;
        padding: 0.375rem 0.75rem;
        border-radius: 12px;
        font-weight: 600;
        font-size: 0.8rem;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }

    .no-data-state {
        text-align: center;
        padding: 3rem 2rem;
        color: #6c757d;
    }

    .no-data-icon {
        font-size: 3rem;
        color: #cbd5e0;
        margin-bottom: 1rem;
    }

    .employee-header {
        display: flex;
        align-items: center;
        gap: 1rem;
        margin-bottom: 1.5rem;
    }

    .employee-avatar-large {
        width: 60px;
        height: 60px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 1.5rem;
    }

    .employee-info h4 {
        margin: 0;
        color: #495057;
        font-weight: 600;
    }

    .employee-info small {
        color: #6c757d;
    }

    .chart-container {
        height: 200px;
        margin: 1rem 0;
    }

    @media (max-width: 768px) {
        .stat-item {
            padding: 0.5rem;
        }
        
        .stat-number {
            font-size: 1.25rem;
        }
        
        .overtime-table {
            font-size: 0.8rem;
        }
        
        .employee-header {
            flex-direction: column;
            text-align: center;
        }
        
        .employee-avatar-large {
            width: 50px;
            height: 50px;
            font-size: 1.25rem;
        }
    }
</style>

<div class="overtime-detail-modal">
    <!-- Employee Header -->
    <div class="employee-header">
        <div class="employee-avatar-large">
            {{ isset($summaryData['employee_name']) ? substr($summaryData['employee_name'], 0, 1) : 'N' }}
        </div>
        <div class="employee-info">
            <h4>{{ $summaryData['employee_name'] ?? __('Unknown Employee') }}</h4>
            <small>{{ __('Employee ID') }}: {{ $summaryData['employee_id'] ?? 'N/A' }} | {{ __('Period') }}: {{ $summaryData['period'] ?? __('Current Month') }}</small>
        </div>
    </div>

    <!-- Summary Statistics -->
    @if(isset($summaryData))
    <div class="summary-stats">
        <div class="row">
            <div class="col-md-3 col-6">
                <div class="stat-item">
                    <div class="stat-number">{{ $summaryData['total_overtime_days'] ?? 0 }}</div>
                    <div class="stat-label">{{ __('Overtime Days') }}</div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="stat-item">
                    <div class="stat-number">{{ $summaryData['total_overtime_hours'] ?? '00:00:00' }}</div>
                    <div class="stat-label">{{ __('Total Hours') }}</div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="stat-item">
                    <div class="stat-number">{{ $summaryData['average_overtime_per_day'] ?? '00:00:00' }}</div>
                    <div class="stat-label">{{ __('Avg Per Day') }}</div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="stat-item">
                    <div class="stat-number">{{ $summaryData['total_working_days'] ?? 0 }}</div>
                    <div class="stat-label">{{ __('Working Days') }}</div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Overtime Records Table -->
    @if(!empty($employee_overtime) && count($employee_overtime) > 0)
    <div class="table-responsive">
        <table class="table overtime-table">
            <thead>
                <tr>
                    <th>{{ __('Date') }}</th>
                    <th>{{ __('Start Time') }}</th>
                    <th>{{ __('End Time') }}</th>
                    <th>{{ __('Duration') }}</th>
                    <th>{{ __('Day Type') }}</th>
                    <th>{{ __('Status') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($employee_overtime as $overtime)
                    @php
                        // Determine if it's weekend or weekday
                        $dayOfWeek = date("N", strtotime($overtime->start_date));
                        $isWeekend = $dayOfWeek >= 6;
                        $label = $isWeekend ? "Weekend" : "Weekdays";
                        
                        // Calculate duration if start and end times are available
                        $duration = '00:00:00';
                        if ($overtime->start_time && $overtime->end_time) {
                            $start = strtotime($overtime->start_time);
                            $end = strtotime($overtime->end_time);
                            if ($end > $start) {
                                $diff = $end - $start;
                                $duration = sprintf('%02d:%02d:%02d', 
                                    floor($diff / 3600), 
                                    floor(($diff % 3600) / 60), 
                                    $diff % 60
                                );
                            }
                        } elseif (isset($overtime->total_time)) {
                            $duration = $overtime->total_time;
                        }
                    @endphp
                    <tr>
                        <td>
                            <div class="date-display">
                                <div><strong>{{ date("d M", strtotime($overtime->start_date)) }}</strong></div>
                                <small>{{ date("l", strtotime($overtime->start_date)) }}</small>
                            </div>
                        </td>
                        
                        <td>
                            <div class="time-display">
                                {{ $overtime->start_time ?? '--:--' }}
                            </div>
                        </td>
                        
                        <td>
                            <div class="time-display">
                                {{ $overtime->end_time ?? '--:--' }}
                            </div>
                        </td>
                        
                        <td>
                            <div class="time-display" style="background: linear-gradient(135deg, #e8f5e8 0%, #c8e6c9 100%); color: #2e7d32;">
                                {{ $duration }}
                            </div>
                        </td>
                        
                        <td>
                            @if($isWeekend)
                                <span class="weekend-badge">
                                    <i class="ti ti-calendar"></i>
                                    {{ __('Weekend') }}
                                </span>
                            @else
                                <span class="weekday-badge">
                                    <i class="ti ti-briefcase"></i>
                                    {{ __('Weekdays') }}
                                </span>
                            @endif
                        </td>
                        
                        <td>
                            @if(isset($overtime->status))
                                @if($overtime->status == 'Approved')
                                    <span class="badge bg-success">
                                        <i class="ti ti-check"></i> {{ __('Approved') }}
                                    </span>
                                @elseif($overtime->status == 'Pending')
                                    <span class="badge bg-warning">
                                        <i class="ti ti-clock"></i> {{ __('Pending') }}
                                    </span>
                                @else
                                    <span class="badge bg-danger">
                                        <i class="ti ti-x"></i> {{ __('Rejected') }}
                                    </span>
                                @endif
                            @else
                                <span class="badge bg-info">
                                    <i class="ti ti-info-circle"></i> {{ __('N/A') }}
                                </span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Additional Statistics -->
    @if(count($employee_overtime) > 0)
    <div class="row mt-3">
        <div class="col-md-6">
            <div class="card border-0 bg-light">
                <div class="card-body text-center">
                    <h6 class="text-muted mb-2">{{ __('Weekend Overtime') }}</h6>
                    <h4 class="text-success">
                        {{ collect($employee_overtime)->filter(function($item) { 
                            return date("N", strtotime($item->start_date)) >= 6; 
                        })->count() }}
                    </h4>
                    <small class="text-muted">{{ __('days') }}</small>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card border-0 bg-light">
                <div class="card-body text-center">
                    <h6 class="text-muted mb-2">{{ __('Weekday Overtime') }}</h6>
                    <h4 class="text-primary">
                        {{ collect($employee_overtime)->filter(function($item) { 
                            return date("N", strtotime($item->start_date)) < 6; 
                        })->count() }}
                    </h4>
                    <small class="text-muted">{{ __('days') }}</small>
                </div>
            </div>
        </div>
    </div>
    @endif

    @else
    <!-- No Data State -->
    <div class="no-data-state">
        <div class="no-data-icon">
            <i class="ti ti-clock-off"></i>
        </div>
        <h5>{{ __('No Overtime Records Found') }}</h5>
        <p class="text-muted">{{ __('This employee has no overtime records for the selected period.') }}</p>
    </div>
    @endif
</div>

<script>
$(document).ready(function() {
    // Prevent any default modal behaviors that might conflict
    $('.overtime-detail-modal').on('click', function(e) {
        e.stopPropagation();
    });
    
    // Initialize DataTable if records exist with improved configuration
    @if(!empty($employee_overtime) && count($employee_overtime) > 0)
    if ($('.overtime-table').length && typeof $.fn.DataTable !== 'undefined') {
        // Destroy existing DataTable if it exists
        if ($.fn.DataTable.isDataTable('.overtime-table')) {
            $('.overtime-table').DataTable().destroy();
        }
        
        // Initialize new DataTable
        $('.overtime-table').DataTable({
            responsive: true,
            pageLength: 10,
            order: [[0, 'desc']], // Sort by date descending
            language: {
                search: "{{ __('Search') }}:",
                lengthMenu: "{{ __('Show') }} _MENU_ {{ __('entries') }}",
                info: "{{ __('Showing') }} _START_ {{ __('to') }} _END_ {{ __('of') }} _TOTAL_ {{ __('entries') }}",
                paginate: {
                    first: "{{ __('First') }}",
                    last: "{{ __('Last') }}",
                    next: "{{ __('Next') }}",
                    previous: "{{ __('Previous') }}"
                },
                emptyTable: "{{ __('No data available') }}",
                zeroRecords: "{{ __('No matching records found') }}"
            },
            columnDefs: [
                { orderable: false, targets: [4, 5] }, // Disable sorting for Day Type and Status columns
                { className: 'text-center', targets: [1, 2, 3, 5] } // Center align specific columns
            ],
            drawCallback: function() {
                // Re-apply hover effects after table redraw
                addTableHoverEffects();
            }
        });
    }
    @endif

    // Add animation to summary stats with improved timing
    animateSummaryStats();
    
    // Add hover effects to table rows
    addTableHoverEffects();
    
    // Handle export functionality
    handleExportFunctionality();
    
    // Initialize custom behaviors
    initializeCustomBehaviors();
});

// Animate summary statistics
function animateSummaryStats() {
    $('.stat-number').each(function(index) {
        var $this = $(this);
        var text = $this.text().trim();
        
        // Skip animation for time format
        if (text.includes(':')) {
            return;
        }
        
        var countTo = parseInt(text);
        if (isNaN(countTo)) return;
        
        // Delay animation based on index for staggered effect
        setTimeout(function() {
            $({ countNum: 0 }).animate({
                countNum: countTo
            }, {
                duration: 1000,
                easing: 'swing',
                step: function() {
                    $this.text(Math.floor(this.countNum));
                },
                complete: function() {
                    $this.text(countTo);
                }
            });
        }, index * 200);
    });
}

// Add hover effects to table rows
function addTableHoverEffects() {
    $('.overtime-table tbody tr').off('mouseenter mouseleave'); // Remove existing handlers
    $('.overtime-table tbody tr').hover(
        function() {
            $(this).css({
                'transform': 'translateY(-2px)',
                'box-shadow': '0 4px 8px rgba(0,0,0,0.1)',
                'transition': 'all 0.2s ease'
            });
        },
        function() {
            $(this).css({
                'transform': 'translateY(0)',
                'box-shadow': 'none'
            });
        }
    );
}

// Handle export functionality
function handleExportFunctionality() {
    $(document).off('click', '.export-individual').on('click', '.export-individual', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        var employeeName = "{{ $summaryData['employee_name'] ?? 'Employee' }}";
        var period = "{{ $summaryData['period'] ?? 'Current' }}";
        
        // Show loading state
        var $btn = $(this);
        var originalContent = $btn.html();
        $btn.html('<i class="spinner-border spinner-border-sm me-1"></i>{{ __("Exporting...") }}');
        $btn.prop('disabled', true);
        
        try {
            // Simple CSV export for individual employee
            var csvContent = "data:text/csv;charset=utf-8,";
            csvContent += "Employee: " + employeeName + "\n";
            csvContent += "Period: " + period + "\n\n";
            csvContent += "Date,Start Time,End Time,Duration,Day Type,Status\n";
            
            $('.overtime-table tbody tr').each(function() {
                var row = [];
                $(this).find('td').each(function(index) {
                    if (index < 6) { // Only first 6 columns
                        var text = $(this).text().trim().replace(/\n/g, ' ').replace(/,/g, ';');
                        row.push('"' + text + '"');
                    }
                });
                csvContent += row.join(',') + '\n';
            });
            
            var encodedUri = encodeURI(csvContent);
            var link = document.createElement("a");
            link.setAttribute("href", encodedUri);
            link.setAttribute("download", employeeName.replace(/\s+/g, '_') + "_overtime_" + period.replace(/\s+/g, '_') + ".csv");
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            
            // Show success message
            if (typeof toastr !== 'undefined') {
                toastr.success("{{ __('Overtime data exported successfully') }}", "{{ __('Export Complete') }}");
            } else {
                alert("{{ __('Overtime data exported successfully') }}");
            }
            
        } catch (error) {
            console.error('Export error:', error);
            if (typeof toastr !== 'undefined') {
                toastr.error("{{ __('Export failed. Please try again.') }}", "{{ __('Export Error') }}");
            } else {
                alert("{{ __('Export failed. Please try again.') }}");
            }
        } finally {
            // Restore button state
            setTimeout(function() {
                $btn.html(originalContent);
                $btn.prop('disabled', false);
            }, 1000);
        }
    });
}

// Initialize custom behaviors
function initializeCustomBehaviors() {
    // Add smooth scroll to table if it's long
    if ($('.overtime-table tbody tr').length > 10) {
        $('.modal-body').css({
            'max-height': '70vh',
            'overflow-y': 'auto'
        });
    }
    
    // Add keyboard navigation
    $(document).on('keydown', function(e) {
        var modal = $('.modal.show');
        if (modal.length) {
            // Escape key closes modal
            if (e.keyCode === 27) {
                modal.modal('hide');
            }
            // Ctrl+E exports data
            else if (e.ctrlKey && e.keyCode === 69) {
                e.preventDefault();
                $('.export-individual').trigger('click');
            }
        }
    });
    
    // Add loading animation to badges
    $('.weekend-badge, .weekday-badge').each(function(index) {
        $(this).css({
            'animation': 'fadeInUp 0.5s ease forwards',
            'animation-delay': (index * 0.1) + 's'
        });
    });
    
    // Enhance status badges with better styling
    $('.badge').each(function() {
        $(this).css({
            'font-size': '0.8rem',
            'padding': '0.5rem 0.75rem',
            'border-radius': '0.5rem'
        });
    });
}

// Cleanup function when modal is closed
function cleanupModalContent() {
    // Destroy DataTable if it exists
    if ($.fn.DataTable && $.fn.DataTable.isDataTable('.overtime-table')) {
        $('.overtime-table').DataTable().destroy();
    }
    
    // Clear any running animations
    $('.stat-number').stop();
    
    // Remove event handlers
    $(document).off('keydown');
    $('.export-individual').off('click');
    $('.overtime-table tbody tr').off('mouseenter mouseleave');
}

// Call cleanup when modal is about to be hidden
$(document).on('hide.bs.modal', '.modal', function() {
    cleanupModalContent();
});

// Additional CSS animations
$('<style>')
    .prop('type', 'text/css')
    .html(`
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .overtime-table tbody tr {
            transition: all 0.2s ease;
        }
        
        .export-individual:hover {
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .stat-item {
            animation: fadeInUp 0.6s ease forwards;
        }
        
        .stat-item:nth-child(1) { animation-delay: 0.1s; }
        .stat-item:nth-child(2) { animation-delay: 0.2s; }
        .stat-item:nth-child(3) { animation-delay: 0.3s; }
        .stat-item:nth-child(4) { animation-delay: 0.4s; }
    `)
    .appendTo('head');

</script>