@extends('layouts.admin')

@section('page-title')
    {{__('Manage Leave Request')}}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
    <li class="breadcrumb-item">{{__('Manage Leave Request')}}</li>
@endsection

@section('action-btn')
    <div class="float-end">
        @can('create leave')
        <a href="#" data-size="lg" data-url="{{ route('leave.create') }}" data-ajax-popup="true" 
           data-bs-toggle="tooltip" title="{{__('Create')}}" data-title="{{__('Create Leave Request')}}" 
           class="btn btn-primary">
            <i class="ti ti-plus"></i> {{__('Add Leave')}}
        </a>
        @endcan
    </div>
@endsection

@push('css-page')
<style>
    /* Simple, clean card styling */
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

    /* Simple table styling */
    .table-clean {
        margin: 0;
    }

    .table-clean thead th {
        background: #f8f9fa;
        border-top: none;
        border-bottom: 2px solid #dee2e6;
        font-weight: 600;
        color: #495057;
        font-size: 0.875rem;
        padding: 1rem 0.75rem;
    }

    .table-clean tbody td {
        padding: 1rem 0.75rem;
        vertical-align: middle;
        border-top: 1px solid #dee2e6;
    }

    .table-clean tbody tr:hover {
        background-color: #f8f9fa;
    }

    /* Status badges */
    .status-badge {
        padding: 0.25rem 0.75rem;
        border-radius: 4px;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
    }

    .status-pending {
        background-color: #fff3cd;
        color: #856404;
        border: 1px solid #ffeaa7;
    }

    .status-approved {
        background-color: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }

    .status-rejected {
        background-color: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }

    /* Employee info */
    .employee-info {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .employee-avatar {
        width: 35px;
        height: 35px;
        background: #6c757d;
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        font-size: 0.875rem;
        flex-shrink: 0;
    }

    /* Action buttons */
    .action-btn {
        padding: 0.375rem 0.75rem;
        border-radius: 4px;
        border: none;
        font-size: 0.875rem;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
        transition: all 0.2s ease;
    }

    .action-btn:hover {
        transform: translateY(-1px);
    }

    .btn-edit {
        background: #007bff;
        color: white;
    }

    .btn-edit:hover {
        background: #0056b3;
        color: white;
    }

    .btn-action {
        background: #28a745;
        color: white;
    }

    .btn-action:hover {
        background: #1e7e34;
        color: white;
    }

    /* Type badges */
    .type-badge, .leave-type-badge {
        background: #e9ecef;
        color: #495057;
        padding: 0.25rem 0.5rem;
        border-radius: 4px;
        font-size: 0.8rem;
        font-weight: 500;
    }

    .leave-type-badge {
        background: #e3f2fd;
        color: #1976d2;
    }

    /* Days badge */
    .days-badge {
        background: #17a2b8;
        color: white;
        padding: 0.25rem 0.5rem;
        border-radius: 4px;
        font-size: 0.8rem;
        font-weight: 600;
    }

    /* Empty state */
    .empty-state {
        text-align: center;
        padding: 3rem 1rem;
        color: #6c757d;
    }

    .empty-state i {
        font-size: 3rem;
        margin-bottom: 1rem;
        opacity: 0.5;
    }

    /* Responsive design */
    @media (max-width: 768px) {
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
            width: 30px;
            height: 30px;
            font-size: 0.75rem;
        }

        .action-btn {
            padding: 0.25rem 0.5rem;
            font-size: 0.75rem;
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

    /* Filter section styling */
    .filter-collapsed {
        display: none;
    }

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

    .btn-group .btn {
        border-radius: 0;
    }

    .btn-group .btn:first-child {
        border-radius: 4px 0 0 4px;
    }

    .btn-group .btn:last-child {
        border-radius: 0 4px 4px 0;
    }

    /* Filter toggle animation */
    .filter-content {
        transition: all 0.3s ease;
        overflow: hidden;
    }

    /* Simple animations */
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
    <div class="row mb-4">
        <div class="col-12">
            <div class="clean-card">
                <div class="card-header-clean">
                    <h6><i class="ti ti-filter me-2"></i>{{__('Filters')}}</h6>
                </div>
                <div class="card-body">
                    <form id="filterForm" method="GET">
                        <div class="row g-3">
                            <!-- Employee Filter (for admin/company only) -->
                            @if(\Auth::user()->type != 'employee' && \Auth::user()->type != 'staff IT' && \Auth::user()->type != 'junior audit' && \Auth::user()->type != 'senior audit' && \Auth::user()->type != 'junior accounting' && \Auth::user()->type != 'senior accounting' && \Auth::user()->type != 'manager audit' && \Auth::user()->type != 'intern' && \Auth::user()->type != 'support' && \Auth::user()->type != 'staff')
                            <div class="col-lg-3 col-md-6">
                                <label class="form-label">{{__('Employee')}}</label>
                                <select name="employee_filter" id="employee_filter" class="form-select">
                                    <option value="">{{__('All Employees')}}</option>
                                    @if(isset($employees))
                                        @foreach($employees as $emp)
                                            <option value="{{ $emp->id }}" {{ request('employee_filter') == $emp->id ? 'selected' : '' }}>
                                                {{ $emp->name }}
                                            </option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                            @endif

                            <!-- Leave Type Filter -->
                            <div class="col-lg-3 col-md-6">
                                <label class="form-label">{{__('Leave Type')}}</label>
                                <select name="leave_type_filter" id="leave_type_filter" class="form-select">
                                    <option value="">{{__('All Leave Types')}}</option>
                                    @if(isset($leave_types))
                                        @foreach($leave_types as $type)
                                            <option value="{{ $type->id }}" {{ request('leave_type_filter') == $type->id ? 'selected' : '' }}>
                                                {{ $type->title }}
                                            </option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>

                            <!-- Status Filter -->
                            <div class="col-lg-3 col-md-6">
                                <label class="form-label">{{__('Status')}}</label>
                                <select name="status_filter" id="status_filter" class="form-select">
                                    <option value="">{{__('All Status')}}</option>
                                    <option value="Pending" {{ request('status_filter') == 'Pending' ? 'selected' : '' }}>{{__('Pending')}}</option>
                                    <option value="Approved" {{ request('status_filter') == 'Approved' ? 'selected' : '' }}>{{__('Approved')}}</option>
                                    <option value="Reject" {{ request('status_filter') == 'Reject' ? 'selected' : '' }}>{{__('Rejected')}}</option>
                                </select>
                            </div>

                            <!-- Attendance Type Filter -->
                            <div class="col-lg-3 col-md-6">
                                <label class="form-label">{{__('Attendance Type')}}</label>
                                <select name="attendance_type_filter" id="attendance_type_filter" class="form-select">
                                    <option value="">{{__('All Types')}}</option>
                                    <option value="leave" {{ request('attendance_type_filter') == 'leave' ? 'selected' : '' }}>{{__('Leave')}}</option>
                                    <option value="sick" {{ request('attendance_type_filter') == 'sick' ? 'selected' : '' }}>{{__('Sick')}}</option>
                                    <option value="emergency" {{ request('attendance_type_filter') == 'emergency' ? 'selected' : '' }}>{{__('Emergency')}}</option>
                                </select>
                            </div>

                            <!-- Date Range Filter -->
                            <div class="col-lg-3 col-md-6">
                                <label class="form-label">{{__('Applied Date From')}}</label>
                                <input type="date" name="date_from" id="date_from" class="form-control" value="{{ request('date_from') }}">
                            </div>

                            <div class="col-lg-3 col-md-6">
                                <label class="form-label">{{__('Applied Date To')}}</label>
                                <input type="date" name="date_to" id="date_to" class="form-control" value="{{ request('date_to') }}">
                            </div>

                            <!-- Leave Date Range Filter -->
                            <div class="col-lg-3 col-md-6">
                                <label class="form-label">{{__('Leave Start From')}}</label>
                                <input type="date" name="leave_start_from" id="leave_start_from" class="form-control" value="{{ request('leave_start_from') }}">
                            </div>

                            <div class="col-lg-3 col-md-6">
                                <label class="form-label">{{__('Leave Start To')}}</label>
                                <input type="date" name="leave_start_to" id="leave_start_to" class="form-control" value="{{ request('leave_start_to') }}">
                            </div>

                            <!-- Search Box -->
                            <div class="col-lg-6 col-md-12">
                                <label class="form-label">{{__('Search')}}</label>
                                <input type="text" name="search" id="search" class="form-control" 
                                       placeholder="{{__('Search by employee name or reason...')}}" 
                                       value="{{ request('search') }}">
                            </div>

                            <!-- Filter Buttons -->
                            <div class="col-lg-6 col-md-12 d-flex align-items-end">
                                <div class="btn-group w-100" role="group">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="ti ti-search me-1"></i>{{__('Apply Filters')}}
                                    </button>
                                    <a href="{{ request()->url() }}" class="btn btn-secondary">
                                        <i class="ti ti-refresh me-1"></i>{{__('Clear')}}
                                    </a>
                                    <button type="button" id="toggleFilters" class="btn btn-outline-secondary">
                                        <i class="ti ti-chevron-up" id="toggleIcon"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Leave Requests -->
    <div class="row">
        <div class="col-12">
            <div class="clean-card fade-in">
                @if(\Auth::user()->type != 'intern')
                    <div class="card-header-clean">
                        <h6>{{__('Leave Requests')}}</h6>
                    </div>
                    <div class="card-body p-0">
                        @if($absence_leave->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-clean">
                                    <thead>
                                        <tr>
                                            @if(\Auth::user()->type!='employee')
                                                <th>{{__('Employee')}}</th>
                                            @endif
                                            <th>{{__('Attendance Type')}}</th>
                                            <th>{{__('Leave Type')}}</th>
                                            <th>{{__('Applied On')}}</th>
                                            <th>{{__('Start Date')}}</th>
                                            <th>{{__('End Date')}}</th>
                                            <th>{{__('Total Days')}}</th>
                                            <th>{{__('Leave Reason')}}</th>
                                            <th>{{__('Status')}}</th>
                                            <th>{{__('Action')}}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($absence_leave as $leave)
                                            <tr>
                                                @if(\Auth::user()->type!='employee')
                                                    <td data-label="Employee">
                                                        <div class="employee-info">
                                                            <div class="employee-avatar">
                                                                {{ substr(!empty(\Auth::user()->getEmployee($leave->employee_id))?\Auth::user()->getEmployee($leave->employee_id)->name:'U', 0, 1) }}
                                                            </div>
                                                            <span>{{ !empty(\Auth::user()->getEmployee($leave->employee_id))?\Auth::user()->getEmployee($leave->employee_id)->name:'-' }}</span>
                                                        </div>
                                                    </td>
                                                @endif
                                                <td data-label="Attendance Type">
                                                    <span class="type-badge">{{ !empty($leave->absence_type)?$leave->absence_type:'-' }}</span>
                                                </td>
                                                <td data-label="Leave Type">
                                                    <span class="leave-type-badge">{{ !empty(\Auth::user()->getLeaveType($leave->leave_type_id))?\Auth::user()->getLeaveType($leave->leave_type_id)->title:'-' }}</span>
                                                </td>
                                                <td data-label="Applied On">{{ \Auth::user()->dateFormat($leave->applied_on) }}</td>
                                                <td data-label="Start Date">{{ \Auth::user()->dateFormat($leave->start_date) }}</td>
                                                <td data-label="End Date">{{ \Auth::user()->dateFormat($leave->end_date) }}</td>
                                                @php
                                                    $startDate = new \DateTime($leave->start_date);
                                                    $endDate = new \DateTime($leave->end_date);
                                                    $total_leave_days = 0;

                                                    while ($startDate <= $endDate) {
                                                        if ($startDate->format('N') <= 5) {
                                                            $total_leave_days++;
                                                        }
                                                        $startDate->add(new \DateInterval('P1D'));
                                                    }
                                                @endphp
                                                <td data-label="Total Days">
                                                    <span class="days-badge">{{ $total_leave_days }} days</span>
                                                </td>
                                                <td data-label="Reason">{{ $leave->leave_reason }}</td>
                                                <td data-label="Status">
                                                    @if($leave->status=="Pending")
                                                        <span class="status-badge status-pending">{{ $leave->status }}</span>
                                                    @elseif($leave->status=="Approved")
                                                        <span class="status-badge status-approved">{{ $leave->status }}</span>
                                                    @else
                                                        <span class="status-badge status-rejected">{{ $leave->status }}</span>
                                                    @endif
                                                </td>
                                                <td data-label="Action">
                                                    @if($leave->status == "Pending")
                                                        @can('edit leave')
                                                        <a href="#" data-url="{{ URL::to('leave/'.$leave->id.'/edit') }}" 
                                                           data-size="lg" data-ajax-popup="true" 
                                                           data-title="{{__('Edit Leave Request')}}" 
                                                           class="action-btn btn-edit" 
                                                           data-bs-toggle="tooltip" title="{{__('Edit')}}">
                                                            <i class="ti ti-edit"></i> Edit
                                                        </a>
                                                        @endcan
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="empty-state">
                                <i class="ti ti-calendar-off"></i>
                                <h5>{{__('No Leave Requests Found')}}</h5>
                                <p>{{__('There are no leave requests to display.')}}</p>
                            </div>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Pagination -->
    @if($absence_leave->hasPages())
        <div class="d-flex justify-content-center mt-3">
            {{ $absence_leave->links() }}
        </div>
    @endif

    <!-- Request Approval Leave -->
    @if(\Auth::user()->type == 'admin' || \Auth::user()->type == 'company')
        <div class="row">
            <div class="col-12">
                <div class="clean-card fade-in">
                    <div class="card-header-clean">
                        <h6>{{__('Request Approval Leave')}}</h6>
                    </div>
                    <div class="card-body p-0">
                        @if($approval->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-clean datatabless">
                                    <thead>
                                        <tr>
                                            @if(\Auth::user()->type == 'admin' || \Auth::user()->type == 'company')
                                                <th>{{__('Employee')}}</th>
                                            @endif
                                            <th>{{__('Leave Type')}}</th>
                                            <th>{{__('Applied On')}}</th>
                                            <th>{{__('Start Date')}}</th>
                                            <th>{{__('End Date')}}</th>
                                            <th>{{__('Total Days')}}</th>
                                            <th>{{__('Leave Reason')}}</th>
                                            <th>{{__('Status')}}</th>
                                            <th>{{__('Action')}}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($approval as $approvals)
                                            <tr>
                                                @if(\Auth::user()->type == 'admin' || \Auth::user()->type == 'company' || \Auth::user()->type == 'senior audit' || \Auth::user()->type == 'senior accounting' || \Auth::user()->type == 'manager audit' || \Auth::user()->type == 'partners')
                                                    <td data-label="Employee">
                                                        <div class="employee-info">
                                                            <div class="employee-avatar">
                                                                {{ substr(!empty($approvals->employees->name)?$approvals->employees->name:'U', 0, 1) }}
                                                            </div>
                                                            <span>{{!empty($approvals->employees->name)?$approvals->employees->name:'-'}}</span>
                                                        </div>
                                                    </td>
                                                @endif
                                                <td data-label="Leave Type">
                                                    <span class="leave-type-badge">{{ !empty(\Auth::user()->getLeaveType($approvals->leave_type_id))?\Auth::user()->getLeaveType($approvals->leave_type_id)->title:'' }}</span>
                                                </td>
                                                <td data-label="Applied On">{{ \Auth::user()->dateFormat($approvals->applied_on) }}</td>
                                                <td data-label="Start Date">{{ \Auth::user()->dateFormat($approvals->start_date) }}</td>
                                                <td data-label="End Date">{{ \Auth::user()->dateFormat($approvals->end_date) }}</td>
                                                @php
                                                    $startDate = new \DateTime($approvals->start_date);
                                                    $endDate = new \DateTime($approvals->end_date);
                                                    $total_leave_days = 0;

                                                    while ($startDate <= $endDate) {
                                                        if ($startDate->format('N') <= 5) {
                                                            $total_leave_days++;
                                                        }
                                                        $startDate->add(new \DateInterval('P1D'));
                                                    }
                                                @endphp
                                                <td data-label="Total Days">
                                                    <span class="days-badge">{{ $total_leave_days }} days</span>
                                                </td>
                                                <td data-label="Reason">{{ $approvals->leave_reason }}</td>
                                                <td data-label="Status">
                                                    @if($approvals->status=="Pending")
                                                        <span class="status-badge status-pending">{{ $approvals->status }}</span>
                                                    @elseif($approvals->status=="Approved")
                                                        <span class="status-badge status-approved">{{ $approvals->status }}</span>
                                                    @else
                                                        <span class="status-badge status-rejected">{{ $approvals->status }}</span>
                                                    @endif
                                                </td>
                                                <td data-label="Action">
                                                    <a href="#" data-url="{{ URL::to('leave/'.$approvals->id.'/action') }}" 
                                                       data-size="lg" data-ajax-popup="true" 
                                                       data-title="{{__('Leave Action')}}" 
                                                       class="action-btn btn-action" 
                                                       data-bs-toggle="tooltip" title="{{__('Leave Action')}}">
                                                        <i class="ti ti-chevron-right"></i> Action
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="empty-state">
                                <i class="ti ti-user-check"></i>
                                <h5>{{__('No Pending Approvals')}}</h5>
                                <p>{{__('All leave requests have been processed.')}}</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Modal -->
    <div class="modal fade" id="exampleModalCenter" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content image_sider_div">
            </div>
        </div>
    </div>
@endsection

@push('script-page')
    <script src="{{url('js/swiper.min.js')}}"></script>
    <script>
        $(document).ready(function() {
            // Initialize tooltips
            $('[data-bs-toggle="tooltip"]').tooltip();

            // Filter toggle functionality
            $('#toggleFilters').click(function() {
                var filterRows = $('.card-body .row.g-3').children().slice(2); // Hide all except first 2 rows
                var icon = $('#toggleIcon');
                
                if (filterRows.first().is(':visible')) {
                    filterRows.hide();
                    icon.removeClass('ti-chevron-up').addClass('ti-chevron-down');
                } else {
                    filterRows.show();
                    icon.removeClass('ti-chevron-down').addClass('ti-chevron-up');
                }
            });

            // Auto-submit form on filter change (optional)
            $('.form-select, .form-control').change(function() {
                // Uncomment next line if you want auto-submit
                // $('#filterForm').submit();
            });

            // Search with delay
            let searchTimeout;
            $('#search').on('input', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(function() {
                    // Auto search after 1 second of no typing
                    // $('#filterForm').submit();
                }, 1000);
            });

            // Date validation
            $('#date_from, #leave_start_from').change(function() {
                var fromDate = $(this).val();
                var toField = $(this).attr('id') === 'date_from' ? '#date_to' : '#leave_start_to';
                
                if (fromDate) {
                    $(toField).attr('min', fromDate);
                }
            });

            $('#date_to, #leave_start_to').change(function() {
                var toDate = $(this).val();
                var fromField = $(this).attr('id') === 'date_to' ? '#date_from' : '#leave_start_from';
                var fromDate = $(fromField).val();
                
                if (fromDate && toDate && toDate < fromDate) {
                    alert('End date cannot be earlier than start date');
                    $(this).val('');
                }
            });

            // Initialize collapsed state
            var filterRows = $('.card-body .row.g-3').children().slice(4); // Hide rows after basic filters
            filterRows.hide();
            $('#toggleIcon').removeClass('ti-chevron-up').addClass('ti-chevron-down');
        });

        $(document).on('change', '#employee_id', function () {
            var employee_id = $(this).val();

            $.ajax({
                url: '{{route('leave.jsoncount')}}',
                type: 'POST',
                data: {
                    "employee_id": employee_id, "_token": "{{ csrf_token() }}",
                },
                success: function (data) {
                    $('#leave_type_id').empty();
                    $('#leave_type_id').append('<option value="">{{__('Select Leave Type')}}</option>');

                    $.each(data, function (key, value) {
                        var optionText = value.title + ' (' + value.total_leave + '/' + value.days + ')';
                        var optionValue = value.id;

                        $('#leave_type_id').append('<option value="' + optionValue + '">' + optionText + '</option>');
                    });

                    // Reset start_date and end_date inputs
                    $('#start_date').val('');
                    $('#end_date').val('');
                }
            });
        });

        function isWeekend(date) {
            return date.getDay() === 0 || date.getDay() === 6;
        }

        function getNextWorkingDay(date) {
            while (isWeekend(date) || date.getDay() === 5) {
                date.setDate(date.getDate() + 1);
            }
            return date;
        }
    </script>
@endpush