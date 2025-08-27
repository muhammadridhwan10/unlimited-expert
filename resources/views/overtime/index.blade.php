@extends('layouts.admin')

@section('page-title')
    {{__('Manage Overtime')}}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
    <li class="breadcrumb-item">{{__('Manage Overtime')}}</li>
@endsection

@section('action-btn')
    <div class="float-end">
        <a href="#" data-size="lg" data-url="{{ route('overtime.create') }}" data-ajax-popup="true" 
           data-bs-toggle="tooltip" title="{{__('Create')}}" data-title="{{__('Create Overtime')}}" 
           class="btn btn-primary">
            <i class="ti ti-plus"></i> {{__('Add Overtime')}}
        </a>
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

    /* Project and time badges */
    .project-badge {
        background: #e3f2fd;
        color: #1976d2;
        padding: 0.25rem 0.5rem;
        border-radius: 4px;
        font-size: 0.8rem;
        font-weight: 500;
    }

    .time-badge {
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

    /* Filter section styling */
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

    /* Bulk actions */
    .bulk-actions {
        background: #e3f2fd;
        border: 1px solid #bbdefb;
        border-radius: 4px;
        padding: 0.75rem;
        margin-bottom: 1rem;
    }

    .bulk-select {
        margin-right: 1rem;
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
                            <!-- Employee Filter -->
                            @if(\Auth::user()->type == 'admin' || \Auth::user()->type == 'senior accounting' || \Auth::user()->type == 'company' || \Auth::user()->type == 'partners')
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

                            <!-- Project Filter -->
                            <div class="col-lg-3 col-md-6">
                                <label class="form-label">{{__('Project')}}</label>
                                <select name="project_filter" id="project_filter" class="form-select">
                                    <option value="">{{__('All Projects')}}</option>
                                    @if(isset($projects))
                                        @foreach($projects as $project)
                                            <option value="{{ $project->id }}" {{ request('project_filter') == $project->id ? 'selected' : '' }}>
                                                {{ $project->project_name }}
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

                            <!-- Show Entries -->
                            <div class="col-lg-3 col-md-6">
                                <label class="form-label">{{__('Show Entries')}}</label>
                                <select name="show_entries" id="show_entries" class="form-select">
                                    <option value="10" {{ request('show_entries') == '10' ? 'selected' : '' }}>10</option>
                                    <option value="25" {{ request('show_entries') == '25' ? 'selected' : '' }}>25</option>
                                    <option value="50" {{ request('show_entries') == '50' ? 'selected' : '' }}>50</option>
                                    <option value="100" {{ request('show_entries') == '100' ? 'selected' : '' }}>100</option>
                                </select>
                            </div>

                            <!-- Month Filter -->
                            <div class="col-lg-3 col-md-6">
                                <label class="form-label">{{__('Month')}}</label>
                                <input type="month" name="month" id="month" class="form-control" value="{{ request('month') }}">
                            </div>

                            <!-- Date Range Filter -->
                            <div class="col-lg-3 col-md-6">
                                <label class="form-label">{{__('Date From')}}</label>
                                <input type="date" name="date_from" id="date_from" class="form-control" value="{{ request('date_from') }}">
                            </div>

                            <div class="col-lg-3 col-md-6">
                                <label class="form-label">{{__('Date To')}}</label>
                                <input type="date" name="date_to" id="date_to" class="form-control" value="{{ request('date_to') }}">
                            </div>

                            <!-- Search Box -->
                            <div class="col-lg-3 col-md-6">
                                <label class="form-label">{{__('Search')}}</label>
                                <input type="text" name="search" id="search" class="form-control" 
                                       placeholder="{{__('Search by employee, project, or note...')}}" 
                                       value="{{ request('search') }}">
                            </div>

                            <!-- Filter Buttons -->
                            <div class="col-12 d-flex justify-content-end">
                                <div class="btn-group" role="group">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="ti ti-search me-1"></i>{{__('Apply Filters')}}
                                    </button>
                                    <a href="{{ route('overtime.index') }}" class="btn btn-secondary">
                                        <i class="ti ti-refresh me-1"></i>{{__('Clear')}}
                                    </a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Overtime Records -->
    <div class="row">
        <div class="col-12">
            <div class="clean-card fade-in">
                <div class="card-header-clean">
                    <h6><i class="ti ti-clock me-2"></i>{{__('Overtime Records')}}</h6>
                </div>
                <div class="card-body p-0">
                    @if($employeeOvertimes->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-clean">
                                <thead>
                                    <tr>
                                        @if(\Auth::user()->type == 'admin' || \Auth::user()->type == 'senior accounting' || \Auth::user()->type == 'company' || \Auth::user()->type == 'partners' || \Auth::user()->type == 'client' || \Auth::user()->type == 'staff_client')
                                            <th>{{__('Employee')}}</th>
                                        @endif
                                        <th>{{__('Project Name')}}</th>
                                        <th>{{__('Approval By')}}</th>
                                        <th>{{__('Start Date')}}</th>
                                        <th>{{__('Time')}}</th>
                                        <th>{{__('Total Time')}}</th>
                                        <th>{{__('Note')}}</th>
                                        <th>{{__('Status')}}</th>
                                        <th>{{__('Action')}}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($employeeOvertimes as $overtime)
                                        <tr>
                                            @if(\Auth::user()->type == 'admin' || \Auth::user()->type == 'senior accounting' || \Auth::user()->type == 'company' || \Auth::user()->type == 'partners' || \Auth::user()->type == 'client' || \Auth::user()->type == 'staff_client')
                                                <td data-label="Employee">
                                                    <div class="employee-info">
                                                        <div class="employee-avatar">
                                                            {{ substr(!empty($overtime->employee->name)?$overtime->employee->name:'U', 0, 1) }}
                                                        </div>
                                                        <span>{{!empty($overtime->employee->name)?$overtime->employee->name:'-'}}</span>
                                                    </div>
                                                </td>
                                            @endif
                                            <td data-label="Project Name">
                                                <span class="project-badge">{{!empty($overtime->project->project_name)?$overtime->project->project_name:'-'}}</span>
                                            </td>
                                            <td data-label="Approval By">{{!empty($overtime->approvals->name)?$overtime->approvals->name:'-'}}</td>
                                            <td data-label="Start Date">{{date("l, d-m-Y",strtotime($overtime->start_date))}}</td>
                                            <td data-label="Time">
                                                <div>
                                                    <small class="d-block"><strong>Start:</strong> {{ ($overtime->start_time !='00:00:00') ?\Auth::user()->timeFormat( $overtime->start_time):'00:00' }}</small>
                                                    <small class="d-block"><strong>End:</strong> {{ ($overtime->end_time !='00:00:00') ?\Auth::user()->timeFormat( $overtime->end_time):'00:00' }}</small>
                                                </div>
                                            </td>
                                            <td data-label="Total Time">
                                                <span class="time-badge">{{!empty($overtime->total_time)?$overtime->total_time:'00:00:00'}}</span>
                                            </td>
                                            <td data-label="Note">
                                                <span style="max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; display: inline-block;" 
                                                      data-bs-toggle="tooltip" title="{{$overtime->note}}">
                                                    {{!empty($overtime->note)?$overtime->note:'-'}}
                                                </span>
                                            </td>
                                            <td data-label="Status">
                                                @if($overtime->status=="Pending")
                                                    <span class="status-badge status-pending">{{ $overtime->status }}</span>
                                                @elseif($overtime->status=="Approved")
                                                    <span class="status-badge status-approved">{{ $overtime->status }}</span>
                                                @else
                                                    <span class="status-badge status-rejected">{{ $overtime->status }}</span>
                                                @endif
                                            </td>
                                            <td data-label="Action">
                                                @if($overtime->status == "Pending")
                                                    @can('edit overtime')
                                                        <div class="action-btn bg-primary ms-2">
                                                            <a href="#" data-url="{{ URL::to('overtime/'.$overtime->id.'/edit') }}" data-size="lg" data-ajax-popup="true" data-title="{{__('Edit Overtime')}}" class="mx-3 btn btn-sm  align-items-center" data-bs-toggle="tooltip" title="{{__('Edit')}}" data-original-title="{{__('Edit')}}"><i class="ti ti-pencil text-white"></i></a>
                                                        </div>
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
                            <i class="ti ti-clock-off"></i>
                            <h5>{{__('No Overtime Records Found')}}</h5>
                            <p>{{__('There are no overtime records to display.')}}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Pagination -->
    @if($employeeOvertimes->hasPages())
        <div class="d-flex justify-content-center mt-3">
            {{ $employeeOvertimes->links() }}
        </div>
    @endif

    <!-- Request Approval Section -->
    @if(\Auth::user()->type == 'admin' || \Auth::user()->type == 'company' || \Auth::user()->type == 'senior audit' || \Auth::user()->type == 'junior audit' || \Auth::user()->type == 'senior accounting' || \Auth::user()->type == 'manager audit' || \Auth::user()->type == 'partners' || \Auth::user()->type == 'staff')
        <div class="row">
            <div class="col-12">
                <div class="clean-card fade-in">
                    <div class="card-header-clean">
                        <div class="d-flex justify-content-between align-items-center">
                            <h6><i class="ti ti-user-check me-2"></i>{{__('Pending Overtime Approvals')}}</h6>
                            <button class="btn btn-success btn-sm" id="approve-selected">
                                <i class="ti ti-check me-1"></i>{{__('Approve Selected')}}
                            </button>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        @if($approval->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-clean">
                                    <thead>
                                        <tr>
                                            <th width="50">
                                                <input type="checkbox" id="select-all" class="form-check-input">
                                            </th>
                                            @if(\Auth::user()->type == 'admin' || \Auth::user()->type == 'company' || \Auth::user()->type == 'senior audit' || \Auth::user()->type == 'junior audit' || \Auth::user()->type == 'senior accounting' || \Auth::user()->type == 'manager audit' || \Auth::user()->type == 'partners' || \Auth::user()->type == 'staff')
                                                <th>{{__('Employee')}}</th>
                                            @endif
                                            <th>{{__('Project Name')}}</th>
                                            <th>{{__('Start Date')}}</th>
                                            <th>{{__('Time')}}</th>
                                            <th>{{__('Note')}}</th>
                                            <th>{{__('Action')}}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($approval as $approvals)
                                            <tr>
                                                <td>
                                                    <input type="checkbox" class="approval-checkbox form-check-input" data-id="{{ $approvals->id }}">
                                                </td>
                                                @if(\Auth::user()->type == 'admin' || \Auth::user()->type == 'company' || \Auth::user()->type == 'senior audit' || \Auth::user()->type == 'junior audit' || \Auth::user()->type == 'senior accounting' || \Auth::user()->type == 'manager audit' || \Auth::user()->type == 'partners' || \Auth::user()->type == 'staff')
                                                    <td data-label="Employee">
                                                        <div class="employee-info">
                                                            <div class="employee-avatar">
                                                                {{ substr(!empty($approvals->employee->name)?$approvals->employee->name:'U', 0, 1) }}
                                                            </div>
                                                            <span>{{!empty($approvals->employee->name)?$approvals->employee->name:'-'}}</span>
                                                        </div>
                                                    </td>
                                                @endif
                                                <td data-label="Project Name">
                                                    <span class="project-badge">{{!empty($approvals->project->project_name)?$approvals->project->project_name:'-'}}</span>
                                                </td>
                                                <td data-label="Start Date">{{date("l, d-m-Y",strtotime($approvals->start_date))}}</td>
                                                <td data-label="Time">
                                                    <div>
                                                        <small class="d-block"><strong>Start:</strong> {{ ($approvals->start_time !='00:00:00') ?\Auth::user()->timeFormat( $approvals->start_time):'00:00' }}</small>
                                                        <small class="d-block"><strong>End:</strong> {{ ($approvals->end_time !='00:00:00') ?\Auth::user()->timeFormat( $approvals->end_time):'00:00' }}</small>
                                                    </div>
                                                </td>
                                                <td data-label="Note">
                                                    <span style="max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; display: inline-block;" 
                                                          data-bs-toggle="tooltip" title="{{$approvals->note}}">
                                                        {{!empty($approvals->note)?$approvals->note:'-'}}
                                                    </span>
                                                </td>
                                                <td data-label="Action">
                                                    <a href="#" data-url="{{ URL::to('overtime/'.$approvals->id.'/action') }}" data-size="lg" data-ajax-popup="true" data-title="{{__('Overtime Action')}}" class="mx-3 btn btn-sm  align-items-center" data-bs-toggle="tooltip" title="{{__('Overtime Action')}}" data-original-title="{{__('Overtime Action')}}">
                                                    <i class="ti ti-caret-right text-white"></i> </a>
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
                                <p>{{__('All overtime requests have been processed.')}}</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection

@push('script-page')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <script>
        $(document).ready(function() {
            // Initialize tooltips
            $('[data-bs-toggle="tooltip"]').tooltip();

            // Date validation
            $('#date_from').change(function() {
                var fromDate = $(this).val();
                if (fromDate) {
                    $('#date_to').attr('min', fromDate);
                }
            });

            $('#date_to').change(function() {
                var toDate = $(this).val();
                var fromDate = $('#date_from').val();
                
                if (fromDate && toDate && toDate < fromDate) {
                    alert('End date cannot be earlier than start date');
                    $(this).val('');
                }
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

            // Select all functionality
            $('#select-all').change(function() {
                $('.approval-checkbox').prop('checked', $(this).is(':checked'));
            });

            $('.approval-checkbox').change(function() {
                if (!$(this).is(':checked')) {
                    $('#select-all').prop('checked', false);
                } else {
                    var allChecked = $('.approval-checkbox:checked').length === $('.approval-checkbox').length;
                    $('#select-all').prop('checked', allChecked);
                }
            });

            // Bulk approve functionality
            $('#approve-selected').click(function () {
                var selectedIds = [];
                $('.approval-checkbox:checked').each(function () {
                    selectedIds.push($(this).data('id'));
                });

                if (selectedIds.length === 0) {
                    alert('{{__("Please select at least one item to approve.")}}');
                    return;
                }

                if (confirm('{{__("Are you sure you want to approve selected overtime requests?")}}')) {
                    var url = "{{ route('approve-overtime-multiple') }}";
                    var data = {
                        selectedIds: selectedIds,
                        _token: "{{ csrf_token() }}"
                    };

                    // Show loading state
                    $(this).prop('disabled', true).html('<i class="ti ti-loader-2 spin me-1"></i>{{__("Processing...")}}');

                    $.ajax({
                        type: 'POST',
                        url: url,
                        data: data,
                        success: function (response) {
                            alert('{{__("Overtime requests have been approved successfully.")}}');
                            window.location.reload();
                        },
                        error: function () {
                            alert('{{__("Something went wrong. Please try again later.")}}');
                            $('#approve-selected').prop('disabled', false).html('<i class="ti ti-check me-1"></i>{{__("Approve Selected")}}');
                        },
                    });
                }
            });

            // Auto-submit on show_entries change
            $('#show_entries').change(function() {
                $('#filterForm').submit();
            });
        });
    </script>

    <style>
        .spin {
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        .form-check-input {
            width: 1.2em;
            height: 1.2em;
        }

        .form-check-input:checked {
            background-color: #007bff;
            border-color: #007bff;
        }
    </style>
@endpush