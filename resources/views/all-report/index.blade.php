@extends('layouts.admin')

@section('page-title')
    {{ __('All Report HR') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item active">{{ __('All Report HR') }}</li>
@endsection

@section('content')
<div class="container-fluid">
    <!-- Main Report Card -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="report-card">
                <div class="card-header-custom">
                    <div class="header-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div class="header-content">
                        <h5 class="header-title">{{ __('HR Comprehensive Report Generator') }}</h5>
                        <p class="header-subtitle">{{ __('Generate detailed reports including attendance, time tracking, timesheets, and leave management data') }}</p>
                    </div>
                </div>

                <div class="card-body-custom">
                    <form id="reportForm" action="{{ route('all-report.export') }}" method="POST">
                        @csrf
                        <div class="filter-container">
                            <!-- Filter Row -->
                            <div class="row g-4">
                                <!-- Branch Filter -->
                                <div class="col-lg-3 col-md-6">
                                    <label class="filter-label">
                                        <i class="fas fa-building me-2"></i>
                                        {{ __('Branch') }}
                                    </label>
                                    <select name="branch_id" id="branch_id" class="form-select select2">
                                        <option value="">{{ __('All Branches') }}</option>
                                        @foreach($branches as $branch)
                                            <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Employee Filter -->
                                <div class="col-lg-3 col-md-6">
                                    <label class="filter-label">
                                        <i class="fas fa-user me-2"></i>
                                        {{ __('Employee') }}
                                    </label>
                                    <select name="employee_id" id="employee_id" class="form-select select2">
                                        <option value="">{{ __('All Employees') }}</option>
                                        @foreach($employees as $employee)
                                            <option value="{{ $employee->id }}" data-branch="{{ $employee->branch_id }}">
                                                {{ $employee->name }} ({{ $employee->employee_id }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Start Date Filter -->
                                <div class="col-lg-3 col-md-6">
                                    <label class="filter-label required">
                                        <i class="fas fa-calendar-alt me-2"></i>
                                        {{ __('Start Date') }}
                                    </label>
                                    <input type="date" name="start_date" id="start_date" class="form-control" required>
                                </div>

                                <!-- End Date Filter -->
                                <div class="col-lg-3 col-md-6">
                                    <div class="filter-group">
                                        <label class="filter-label required">
                                            <i class="fas fa-calendar-check me-2"></i>
                                            {{ __('End Date') }}
                                        </label>
                                        <input type="date" name="end_date" id="end_date" class="form-control" required>
                                    </div>
                                </div>
                            </div>

                            <!-- Quick Date Presets -->
                            <div class="quick-filters">
                                <label class="quick-filter-label">{{ __('Quick Select:') }}</label>
                                <div class="quick-filter-buttons">
                                    <button type="button" class="quick-btn" data-days="7">{{ __('Last 7 Days') }}</button>
                                    <button type="button" class="quick-btn" data-days="30">{{ __('Last 30 Days') }}</button>
                                    <button type="button" class="quick-btn" data-days="90">{{ __('Last 3 Months') }}</button>
                                    <button type="button" class="quick-btn" data-period="current-month">{{ __('This Month') }}</button>
                                    <button type="button" class="quick-btn" data-period="last-month">{{ __('Last Month') }}</button>
                                </div>
                            </div>

                            <!-- Generate Button -->
                            <div class="generate-section">
                                <button type="submit" class="btn-generate" id="generateBtn">
                                    <span class="btn-icon">
                                        <i class="fas fa-download"></i>
                                    </span>
                                    <span class="btn-text">{{ __('Generate & Download Report') }}</span>
                                    <span class="btn-ripple"></span>
                                </button>
                                <p class="generate-note">
                                    <i class="fas fa-info-circle me-1"></i>
                                    {{ __('Report will be downloaded as Excel file') }}
                                </p>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Information Cards -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="info-section">
                <h5 class="info-title">
                    <i class="fas fa-info-circle me-2"></i>
                    {{ __('Report Contents') }}
                </h5>
                
                <div class="row g-4">
                    <!-- Attendance Card -->
                    <div class="col-lg-3 col-md-6">
                        <div class="info-card attendance">
                            <div class="info-icon">
                                <i class="fas fa-clock"></i>
                            </div>
                            <div class="info-content">
                                <h6>{{ __('Attendance Data') }}</h6>
                                <p>{{ __('Clock in/out times, attendance status, and daily presence tracking') }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Time Tracking Card -->
                    <div class="col-lg-3 col-md-6">
                        <div class="info-card tracking">
                            <div class="info-icon">
                                <i class="fas fa-stopwatch"></i>
                            </div>
                            <div class="info-content">
                                <h6>{{ __('Time Tracking') }}</h6>
                                <p>{{ __('Active trackers, total tracked hours, and productivity monitoring') }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Timesheet Card -->
                    <div class="col-lg-3 col-md-6">
                        <div class="info-card timesheet">
                            <div class="info-icon">
                                <i class="fas fa-calendar-check"></i>
                            </div>
                            <div class="info-content">
                                <h6>{{ __('Timesheet Analysis') }}</h6>
                                <p>{{ __('Total working hours, work shortage calculations, and time allocation') }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Leave Management Card -->
                    <div class="col-lg-3 col-md-6">
                        <div class="info-card leave">
                            <div class="info-icon">
                                <i class="fas fa-file-medical"></i>
                            </div>
                            <div class="info-content">
                                <h6>{{ __('Leave & Medical') }}</h6>
                                <p>{{ __('Leave requests, sick letters, absence types, and medical documentation') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Loading Overlay -->
    <div class="loading-overlay" id="loadingOverlay">
        <div class="loading-content">
            <div class="loading-spinner"></div>
            <h4>{{ __('Generating Report...') }}</h4>
            <p>{{ __('Please wait while we compile your HR data') }}</p>
            <div class="loading-progress">
                <div class="progress-bar"></div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('script-page')
<script>
    $(document).ready(function() {
        // Set default dates (last 7 days)
        setQuickDate(7);

        // Store original employee options for filtering
        const originalEmployeeOptions = $('#employee_id').html();

        // Branch change handler - filter employees by branch
        $('#branch_id').on('change', function() {
            const branchId = $(this).val();
            filterEmployeesByBranch(branchId);
        });

        // Quick filter buttons
        $('.quick-btn').on('click', function() {
            const days = $(this).data('days');
            const period = $(this).data('period');
            
            $('.quick-btn').removeClass('active');
            $(this).addClass('active');
            
            if (days) {
                setQuickDate(days);
            } else if (period) {
                setQuickPeriod(period);
            }
        });

        // Form submission
        $('#reportForm').on('submit', function(e) {
            if (!validateForm()) {
                e.preventDefault();
                return false;
            }

            showLoading();
            
            // Hide loading after 30 seconds (timeout)
            setTimeout(hideLoading, 30000);
        });

        // Date change validation
        $('#start_date, #end_date').on('change', function() {
            validateDates();
            $('.quick-btn').removeClass('active');
        });
    });

    function filterEmployeesByBranch(branchId) {
        const employeeSelect = $('#employee_id');
        
        if (!branchId) {
            // Show all employees
            employeeSelect.html(originalEmployeeOptions);
        } else {
            // Filter employees by branch
            let filteredOptions = '<option value="">{{ __("All Employees") }}</option>';
            let hasEmployees = false;
            
            // Create a temporary container to parse the original options
            const tempDiv = $('<div>').html(originalEmployeeOptions);
            tempDiv.find('option[data-branch]').each(function() {
                if ($(this).data('branch') == branchId) {
                    filteredOptions += this.outerHTML;
                    hasEmployees = true;
                }
            });
            
            if (!hasEmployees) {
                employeeSelect.html('<option value="">{{ __("No employees in this branch") }}</option>');
            } else {
                employeeSelect.html(filteredOptions);
            }
        }
        
        // Reset employee selection
        employeeSelect.val('');
    }

    function setQuickDate(days) {
        const today = new Date();
        const startDate = new Date(today.getTime() - (days - 1) * 24 * 60 * 60 * 1000);
        
        $('#end_date').val(formatDate(today));
        $('#start_date').val(formatDate(startDate));
    }

    function setQuickPeriod(period) {
        const today = new Date();
        let startDate, endDate;
        
        if (period === 'current-month') {
            startDate = new Date(today.getFullYear(), today.getMonth(), 1);
            endDate = today;
        } else if (period === 'last-month') {
            startDate = new Date(today.getFullYear(), today.getMonth() - 1, 1);
            endDate = new Date(today.getFullYear(), today.getMonth(), 0);
        }
        
        $('#start_date').val(formatDate(startDate));
        $('#end_date').val(formatDate(endDate));
    }

    function formatDate(date) {
        return date.toISOString().split('T')[0];
    }

    function validateForm() {
        const startDate = $('#start_date').val();
        const endDate = $('#end_date').val();
        
        if (!startDate || !endDate) {
            showNotification('error', '{{ __("Please select both start and end dates") }}');
            return false;
        }

        return validateDates();
    }

    function validateDates() {
        const startDate = new Date($('#start_date').val());
        const endDate = new Date($('#end_date').val());
        
        if (startDate > endDate) {
            showNotification('error', '{{ __("Start date cannot be greater than end date") }}');
            return false;
        }

        const diffTime = Math.abs(endDate - startDate);
        const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
        
        if (diffDays > 90) {
            return confirm('{{ __("Date range exceeds 90 days. This may take longer to generate. Continue?") }}');
        }

        return true;
    }

    function showLoading() {
        $('#loadingOverlay').addClass('active');
        $('body').addClass('loading');
    }

    function hideLoading() {
        $('#loadingOverlay').removeClass('active');
        $('body').removeClass('loading');
    }

    function showNotification(type, message) {
        // You can replace this with your preferred notification system
        if (typeof toastr !== 'undefined') {
            toastr[type](message);
        } else {
            alert(message);
        }
    }

    // Set initial active quick button
    $('.quick-btn[data-days="7"]').addClass('active');

    // Debug function to check if everything is loaded properly
    function debugFilters() {
        console.log('=== Filter Debug Info ===');
        console.log('Employee select exists:', $('#employee_id').length > 0);
        console.log('Branch select exists:', $('#branch_id').length > 0);
        console.log('Original employee options length:', originalEmployeeOptions ? originalEmployeeOptions.length : 0);
        console.log('Current employee options:', $('#employee_id').html().substring(0, 100) + '...');
        console.log('========================');
    }

    // Call debug on page load (remove in production)
    // debugFilters();

    // Backup initialization if DOM is not ready
    $(window).on('load', function() {
        // Double-check initialization
        if (!originalEmployeeOptions) {
            console.warn('Re-initializing employee options on window load');
            initializeFilters();
        }
    });
</script>
@endpush

@push('css-page')
<style>
    /* ==========================================================================
    HR Report Styles - Professional & Modern Design
    ========================================================================== */

    :root {
        --primary-color: #4f46e5;
        --primary-dark: #3730a3;
        --secondary-color: #06b6d4;
        --success-color: #10b981;
        --warning-color: #f59e0b;
        --danger-color: #ef4444;
        --dark-color: #1f2937;
        --light-color: #f8fafc;
        --border-color: #e5e7eb;
        --text-color: #374151;
        --text-muted: #6b7280;
        --white: #ffffff;
        --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
        --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        --shadow-xl: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        --radius-sm: 6px;
        --radius-md: 8px;
        --radius-lg: 12px;
        --radius-xl: 16px;
    }

    body.loading {
        overflow: hidden;
    }

    /* Main Report Card */
    .report-card {
        background: var(--white);
        border-radius: var(--radius-xl);
        box-shadow: var(--shadow-xl);
        overflow: hidden;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .report-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
    }

    .card-header-custom {
        background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
        padding: 1.5rem 2rem;
        border-bottom: 1px solid var(--border-color);
        display: flex;
        align-items: center;
    }

    .header-icon {
        width: 60px;
        height: 60px;
        background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
        border-radius: var(--radius-lg);
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 1rem;
        color: var(--white);
        font-size: 1.5rem;
    }

    .header-title {
        font-size: 1.25rem;
        font-weight: 700;
        color: var(--dark-color);
        margin-bottom: 0.25rem;
    }

    .header-subtitle {
        color: var(--text-muted);
        margin-bottom: 0;
        font-size: 0.875rem;
    }

    .card-body-custom {
        padding: 2rem;
    }

    /* Filter Container */
    .filter-container {
        max-width: 100%;
    }

    .filter-label {
        display: flex;
        align-items: center;
        font-weight: 600;
        color: var(--dark-color);
        margin-bottom: 0.75rem;
        font-size: 0.875rem;
    }

    .filter-label.required::after {
        content: '*';
        color: var(--danger-color);
        margin-left: 0.25rem;
    }

    /* Form Controls */
    .select-wrapper {
        position: relative;
    }

    .select-icon {
        position: absolute;
        right: 1rem;
        top: 50%;
        transform: translateY(-50%);
        color: var(--text-muted);
        pointer-events: none;
        transition: transform 0.3s ease;
    }

    .form-control-date {
        width: 100%;
        padding: 0.875rem 3rem 0.875rem 1rem;
        border: 2px solid var(--border-color);
        border-radius: var(--radius-md);
        background: var(--white);
        font-size: 0.875rem;
        transition: all 0.3s ease;
    }

    .form-control-date:focus {
        outline: none;
        border-color: var(--primary-color);
        box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
    }

    .date-icon {
        position: absolute;
        right: 1rem;
        top: 50%;
        transform: translateY(-50%);
        color: var(--text-muted);
        pointer-events: none;
    }

    /* Quick Filters */
    .quick-filters {
        margin: 2rem 0;
        padding: 1.5rem;
        background: linear-gradient(135deg, #fef3c7 0%, #fcd34d 100%);
        border-radius: var(--radius-lg);
        border: 1px solid #fbbf24;
    }

    .quick-filter-label {
        font-weight: 600;
        color: #92400e;
        margin-bottom: 1rem;
        display: block;
    }

    .quick-filter-buttons {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
    }

    .quick-btn {
        padding: 0.5rem 1rem;
        background: var(--white);
        border: 2px solid #fbbf24;
        border-radius: var(--radius-md);
        color: #92400e;
        font-size: 0.875rem;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.3s ease;
        white-space: nowrap;
    }

    .quick-btn:hover,
    .quick-btn.active {
        background: #92400e;
        color: var(--white);
        transform: translateY(-1px);
        box-shadow: var(--shadow-md);
    }

    /* Generate Button */
    .generate-section {
        text-align: center;
        margin-top: 2rem;
        padding-top: 2rem;
        border-top: 1px solid var(--border-color);
    }

    .btn-generate {
        background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
        color: var(--white);
        border: none;
        padding: 1rem 2.5rem;
        border-radius: var(--radius-lg);
        font-size: 1rem;
        font-weight: 600;
        cursor: pointer;
        position: relative;
        overflow: hidden;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 0.75rem;
        min-width: 280px;
        justify-content: center;
    }

    .btn-generate:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-xl);
    }

    .btn-generate:active {
        transform: translateY(0);
    }

    .btn-icon {
        font-size: 1.125rem;
    }

    .btn-ripple {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: radial-gradient(circle, rgba(255, 255, 255, 0.3) 0%, transparent 70%);
        transform: scale(0);
        opacity: 0;
        pointer-events: none;
    }

    .btn-generate:active .btn-ripple {
        animation: ripple 0.6s linear;
    }

    @keyframes ripple {
        to {
            transform: scale(4);
            opacity: 0;
        }
    }

    .generate-note {
        margin-top: 1rem;
        color: var(--text-muted);
        font-size: 0.875rem;
    }

    /* Information Section */
    .info-section {
        background: var(--white);
        padding: 2rem;
        border-radius: var(--radius-xl);
        box-shadow: var(--shadow-lg);
    }

    .info-title {
        font-size: 1.25rem;
        font-weight: 700;
        color: var(--dark-color);
        margin-bottom: 1.5rem;
        display: flex;
        align-items: center;
    }

    /* Info Cards */
    .info-card {
        background: var(--white);
        border-radius: var(--radius-lg);
        padding: 1.5rem;
        height: 100%;
        transition: all 0.3s ease;
        border: 2px solid transparent;
        position: relative;
        overflow: hidden;
    }

    .info-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, var(--primary-color), var(--secondary-color));
    }

    .info-card.attendance::before {
        background: linear-gradient(90deg, #3b82f6, #1d4ed8);
    }

    .info-card.tracking::before {
        background: linear-gradient(90deg, #10b981, #047857);
    }

    .info-card.timesheet::before {
        background: linear-gradient(90deg, #f59e0b, #d97706);
    }

    .info-card.leave::before {
        background: linear-gradient(90deg, #ef4444, #dc2626);
    }

    .info-card:hover {
        transform: translateY(-4px);
        box-shadow: var(--shadow-xl);
        border-color: var(--border-color);
    }

    .info-icon {
        width: 50px;
        height: 50px;
        border-radius: var(--radius-lg);
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 1rem;
        font-size: 1.25rem;
        color: var(--white);
    }

    .attendance .info-icon {
        background: linear-gradient(135deg, #3b82f6, #1d4ed8);
    }

    .tracking .info-icon {
        background: linear-gradient(135deg, #10b981, #047857);
    }

    .timesheet .info-icon {
        background: linear-gradient(135deg, #f59e0b, #d97706);
    }

    .leave .info-icon {
        background: linear-gradient(135deg, #ef4444, #dc2626);
    }

    .info-content h6 {
        font-weight: 700;
        color: var(--dark-color);
        margin-bottom: 0.5rem;
        font-size: 1rem;
    }

    .info-content p {
        color: var(--text-muted);
        font-size: 0.875rem;
        line-height: 1.5;
        margin-bottom: 0;
    }

    /* Loading Overlay */
    .loading-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.8);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 9999;
        opacity: 0;
        visibility: hidden;
        transition: all 0.3s ease;
    }

    .loading-overlay.active {
        opacity: 1;
        visibility: visible;
    }

    .loading-content {
        background: var(--white);
        padding: 3rem;
        border-radius: var(--radius-xl);
        text-align: center;
        max-width: 400px;
        width: 90%;
        box-shadow: var(--shadow-xl);
    }

    .loading-spinner {
        width: 60px;
        height: 60px;
        border: 4px solid var(--border-color);
        border-top: 4px solid var(--primary-color);
        border-radius: 50%;
        animation: spin 1s linear infinite;
        margin: 0 auto 1.5rem;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    .loading-content h4 {
        color: var(--dark-color);
        font-weight: 700;
        margin-bottom: 0.5rem;
    }

    .loading-content p {
        color: var(--text-muted);
        margin-bottom: 1.5rem;
    }

    .loading-progress {
        width: 100%;
        height: 4px;
        background: var(--border-color);
        border-radius: 2px;
        overflow: hidden;
    }

    .progress-bar {
        height: 100%;
        background: linear-gradient(90deg, var(--primary-color), var(--secondary-color));
        width: 0%;
        animation: progress 3s ease-in-out infinite;
    }

    @keyframes progress {
        0%, 100% { width: 0%; }
        50% { width: 100%; }
    }

    /* Responsive Design */
    @media (max-width: 991.98px) {
        .page-header {
            padding: 1.5rem;
            text-align: center;
        }

        
        .card-header-custom {
            padding: 1.25rem 1.5rem;
            flex-direction: column;
            text-align: center;
        }
        
        .header-icon {
            margin-right: 0;
            margin-bottom: 1rem;
        }
        
        .card-body-custom {
            padding: 1.5rem;
        }
        
        .btn-generate {
            width: 100%;
            min-width: auto;
        }
        
        .quick-filter-buttons {
            justify-content: center;
        }
        
        /* Adjust filter layout for medium screens */
        .col-lg-3 {
            margin-bottom: 1rem;
        }
    }

    @media (max-width: 767.98px) {
        .page-header {
            padding: 1rem;
        }
        
        .card-body-custom {
            padding: 1rem;
        }
        
        .info-section {
            padding: 1.5rem;
        }
        
        .quick-btn {
            font-size: 0.75rem;
            padding: 0.4rem 0.8rem;
        }
        
        .loading-content {
            padding: 2rem;
        }
        
        /* Stack filters vertically on mobile */
        .filter-container .row .col-lg-3 {
            width: 100%;
            margin-bottom: 1rem;
        }
    }

    @media (max-width: 575.98px) {
        .quick-filter-buttons {
            flex-direction: column;
            align-items: stretch;
        }
        
        .quick-btn {
            text-align: center;
            margin-bottom: 0.5rem;
        }
    }

    /* Utility Classes */
    .me-1 { margin-right: 0.25rem; }
    .me-2 { margin-right: 0.5rem; }
    .mb-4 { margin-bottom: 1.5rem; }
    .g-4 > * { padding: 1rem; }

    /* Animation Classes */
    .fade-in {
        animation: fadeIn 0.5s ease-in;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .scale-in {
        animation: scaleIn 0.3s ease-out;
    }

    @keyframes scaleIn {
        from { transform: scale(0.9); opacity: 0; }
        to { transform: scale(1); opacity: 1; }
    }

    /* Custom scrollbar for select elements */
    .form-select::-webkit-scrollbar {
        width: 8px;
    }

    /* Loading spinner enhancement */
    .loading-spinner {
        position: relative;
    }

    .loading-spinner::after {
        content: '';
        position: absolute;
        top: 50%;
        left: 50%;
        width: 30px;
        height: 30px;
        margin: -15px 0 0 -15px;
        border: 2px solid transparent;
        border-top: 2px solid var(--secondary-color);
        border-radius: 50%;
        animation: spin 2s linear infinite reverse;
    }

    /* Enhanced button states */
    .btn-generate:disabled {
        opacity: 0.6;
        cursor: not-allowed;
        transform: none !important;
    }

    .btn-generate:disabled:hover {
        transform: none !important;
        box-shadow: var(--shadow-md);
    }

    /* Print styles */
    @media print {
        .loading-overlay,
        .quick-filters,
        .generate-section {
            display: none !important;
        }
        
        .page-header {
            background: #f8f9fa !important;
            color: #333 !important;
            print-color-adjust: exact;
        }
        
        .report-card,
        .info-section {
            box-shadow: none !important;
            border: 1px solid #ddd;
        }
    }
</style>