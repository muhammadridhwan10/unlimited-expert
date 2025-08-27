@extends('layouts.admin')

@section('page-title')
    {{__('Manage Reimbursement Client')}}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
    <li class="breadcrumb-item">{{__('Manage Reimbursement Client')}}</li>
@endsection

@section('action-btn')
    <div class="float-end">
        <a href="#" data-size="lg" data-url="{{ route('reimbursment-client.create') }}" data-ajax-popup="true" 
           data-bs-toggle="tooltip" title="{{__('Create')}}" data-title="{{__('Create Reimbursement Client')}}" 
           class="btn btn-primary">
            <i class="ti ti-plus"></i> {{__('Add Client Reimbursement')}}
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

    /* Important notice styling */
    .notice-card {
        background: linear-gradient(135deg, #ffebee 0%, #ffcdd2 100%);
        border: 1px solid #ef5350;
        border-radius: 8px;
        padding: 1rem 1.5rem;
        margin-bottom: 1.5rem;
    }

    .notice-text {
        color: #c62828;
        font-weight: 600;
        margin: 0;
        font-size: 0.95rem;
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

    .status-paid {
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

    /* Type and amount badges */
    .type-badge {
        background: #e3f2fd;
        color: #1976d2;
        padding: 0.25rem 0.5rem;
        border-radius: 4px;
        font-size: 0.8rem;
        font-weight: 500;
    }

    .amount-badge {
        background: #e8f5e8;
        color: #2e7d32;
        padding: 0.25rem 0.5rem;
        border-radius: 4px;
        font-size: 0.8rem;
        font-weight: 600;
    }

    .client-badge {
        background: #f3e5f5;
        color: #7b1fa2;
        padding: 0.25rem 0.5rem;
        border-radius: 4px;
        font-size: 0.8rem;
        font-weight: 500;
    }

    /* Image styling */
    .reimbursement-img {
        width: 40px;
        height: 40px;
        border-radius: 8px;
        border: 2px solid #dee2e6;
        cursor: pointer;
        transition: all 0.2s ease;
        object-fit: cover;
    }

    .reimbursement-img:hover {
        border-color: #007bff;
        transform: scale(1.1);
    }

    .reimbursement-placeholder {
        width: 40px;
        height: 40px;
        background: #f8f9fa;
        border: 2px dashed #dee2e6;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #6c757d;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .reimbursement-placeholder:hover {
        border-color: #007bff;
        color: #007bff;
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

        .reimbursement-img,
        .reimbursement-placeholder {
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

    /* Simple animations */
    .fade-in {
        animation: fadeIn 0.5s ease-in;
    }

    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }

    .form-check-input {
        width: 1.2em;
        height: 1.2em;
    }

    .form-check-input:checked {
        background-color: #007bff;
        border-color: #007bff;
    }

    .spin {
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }
</style>
@endpush

@section('content')
    <!-- Important Notice -->
    <div class="row">
        <div class="col-12">
            <div class="notice-card">
                <p class="notice-text">
                    <i class="ti ti-alert-triangle me-2"></i>
                    {{__('Maximum client reimbursement input is done')}} <strong>{{__('1 WEEK AFTER TRANSACTION OUT')}}</strong>.
                </p>
            </div>
        </div>
    </div>

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
                            @if(\Auth::user()->type == 'admin' || \Auth::user()->type == 'company' || \Auth::user()->type == 'senior audit' || \Auth::user()->type == 'senior accounting' || \Auth::user()->type == 'manager audit' || \Auth::user()->type == 'partners')
                            <div class="col-lg-3 col-md-6">
                                <label class="form-label">{{__('Employee')}}</label>
                                <select name="employee_filter" id="employee_filter" class="form-select">
                                    <option value="">{{__('All Employees')}}</option>
                                    @if(isset($employees_list))
                                        @foreach($employees_list as $emp)
                                            <option value="{{ $emp->id }}" {{ request('employee_filter') == $emp->id ? 'selected' : '' }}>
                                                {{ $emp->name }}
                                            </option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                            @endif

                            <!-- Client Filter -->
                            <div class="col-lg-3 col-md-6">
                                <label class="form-label">{{__('Client')}}</label>
                                <select name="client_filter" id="client_filter" class="form-select">
                                    <option value="">{{__('All Clients')}}</option>
                                    @if(isset($clients))
                                        @foreach($clients as $client_item)
                                            <option value="{{ $client_item->id }}" {{ request('client_filter') == $client_item->id ? 'selected' : '' }}>
                                                {{ $client_item->name }}
                                            </option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>

                            <!-- Reimbursement Type Filter -->
                            <div class="col-lg-3 col-md-6">
                                <label class="form-label">{{__('Reimbursement Type')}}</label>
                                <select name="reimbursement_type_filter" id="reimbursement_type_filter" class="form-select">
                                    <option value="">{{__('All Types')}}</option>
                                    @if(isset($reimbursement_types))
                                        @foreach($reimbursement_types as $key => $type)
                                            <option value="{{ $key }}" {{ request('reimbursement_type_filter') == $key ? 'selected' : '' }}>
                                                {{ $type }}
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
                                    <option value="Paid" {{ request('status_filter') == 'Paid' ? 'selected' : '' }}>{{__('Paid')}}</option>
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
                            <div class="col-lg-6 col-md-12">
                                <label class="form-label">{{__('Search')}}</label>
                                <input type="text" name="search" id="search" class="form-control" 
                                       placeholder="{{__('Search by employee, client, or description...')}}" 
                                       value="{{ request('search') }}">
                            </div>

                            <!-- Filter Buttons -->
                            <div class="col-lg-6 col-md-12 d-flex align-items-end">
                                <div class="btn-group w-100" role="group">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="ti ti-search me-1"></i>{{__('Apply Filters')}}
                                    </button>
                                    <a href="{{ route('reimbursment-client.index') }}" class="btn btn-secondary">
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

    <!-- Reimbursement Client Records -->
    <div class="row">
        <div class="col-12">
            <div class="clean-card fade-in">
                <div class="card-header-clean">
                    <h6><i class="ti ti-building me-2"></i>{{__('Client Reimbursement Records')}}</h6>
                </div>
                <div class="card-body p-0">
                    @if($employeeReimbursment->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-clean">
                                <thead>
                                    <tr>
                                        @if(\Auth::user()->type == 'admin' || \Auth::user()->type == 'company' || \Auth::user()->type == 'senior audit' || \Auth::user()->type == 'senior accounting' || \Auth::user()->type == 'manager audit' || \Auth::user()->type == 'partners')
                                            <th>{{__('Employee')}}</th>
                                        @endif
                                        <th>{{__('Client')}}</th>
                                        <th>{{__('Approval By')}}</th>
                                        <th>{{__('Reimbursement Type')}}</th>
                                        <th>{{__('Date')}}</th>
                                        <th>{{__('Amount')}}</th>
                                        <th>{{__('Description')}}</th>
                                        <th>{{__('Image')}}</th>
                                        <th>{{__('Status')}}</th>
                                        <th>{{__('Action')}}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($employeeReimbursment as $reimbursment)
                                        <tr>
                                            @if(\Auth::user()->type == 'admin' || \Auth::user()->type == 'company' || \Auth::user()->type == 'senior audit' || \Auth::user()->type == 'senior accounting' || \Auth::user()->type == 'manager audit' || \Auth::user()->type == 'partners')
                                                <td data-label="Employee">
                                                    <div class="employee-info">
                                                        <div class="employee-avatar">
                                                            {{ substr(!empty($reimbursment->employee->name)?$reimbursment->employee->name:'U', 0, 1) }}
                                                        </div>
                                                        <span>{{!empty($reimbursment->employee->name)?$reimbursment->employee->name:'-'}}</span>
                                                    </div>
                                                </td>
                                            @endif
                                            <td data-label="Client">
                                                <span class="client-badge">{{!empty($reimbursment->client->name) ? $reimbursment->client->name:'-'}}</span>
                                            </td>
                                            <td data-label="Approval By">{{!empty($reimbursment->approvals->name)?$reimbursment->approvals->name:'-'}}</td>
                                            <td data-label="Reimbursement Type">
                                                <span class="type-badge">{{!empty($reimbursment->reimbursment_type)?$reimbursment->reimbursment_type:'-'}}</span>
                                            </td>
                                            <td data-label="Date">{{date("l, d-m-Y",strtotime($reimbursment->date))}}</td>
                                            <td data-label="Amount">
                                                <span class="amount-badge">
                                                    {{!empty(number_format($reimbursment->amount))?'Rp ' . number_format($reimbursment->amount):'-'}}
                                                </span>
                                            </td>
                                            <td data-label="Description">
                                                <span style="max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; display: inline-block;" 
                                                      data-bs-toggle="tooltip" title="{{$reimbursment->description}}">
                                                    {{!empty($reimbursment->description)?$reimbursment->description:'-'}}
                                                </span>
                                            </td>
                                            <td data-label="Image">
                                                @if(!empty($reimbursment->reimbursment_image))
                                                    <img alt="Reimbursement Image" 
                                                         src="{{ asset('storage/reimbursment_images/'.$reimbursment->reimbursment_image) }}" 
                                                         class="reimbursement-img view-images" 
                                                         data-bs-toggle="tooltip" 
                                                         title="{{__('View Reimbursement Images')}}" 
                                                         data-id="{{$reimbursment->id}}" 
                                                         id="track-images-{{$reimbursment->id}}"
                                                         onerror="this.src='{{ asset('assets/images/gallery.png') }}'">
                                                @else
                                                    <div class="reimbursement-placeholder view-images" 
                                                         data-bs-toggle="tooltip" 
                                                         title="{{__('View Reimbursement Images')}}"
                                                         data-id="{{$reimbursment->id}}" 
                                                         id="track-images-{{$reimbursment->id}}">
                                                        <i class="ti ti-photo"></i>
                                                    </div>
                                                @endif
                                            </td>
                                            <td data-label="Status">
                                                @if($reimbursment->status=="Pending")
                                                    <span class="status-badge status-pending">{{ $reimbursment->status }}</span>
                                                @elseif($reimbursment->status=="Paid")
                                                    <span class="status-badge status-paid">{{ $reimbursment->status }}</span>
                                                @else
                                                    <span class="status-badge status-rejected">{{ $reimbursment->status }}</span>
                                                @endif
                                            </td>
                                            <td data-label="Action">
                                                @if($reimbursment->status == "Pending")
                                                        <a href="#" data-url="{{ URL::to('reimbursment-client/'.\Crypt::encrypt($reimbursment->id).'/edit') }}" data-size="lg" data-ajax-popup="true" data-title="{{__('Edit Medical Allowance')}}" class="mx-3 btn btn-sm btn-primary  align-items-center" data-bs-toggle="tooltip" title="{{__('Edit')}}" data-original-title="{{__('Edit')}}"><i class="ti ti-pencil text-white"></i></a>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="empty-state">
                            <i class="ti ti-building"></i>
                            <h5>{{__('No Client Reimbursement Records Found')}}</h5>
                            <p>{{__('There are no client reimbursement records to display.')}}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Pagination -->
    @if($employeeReimbursment->hasPages())
        <div class="d-flex justify-content-center mt-3">
            {{ $employeeReimbursment->links() }}
        </div>
    @endif

    <!-- Request Approval Section -->
    @if(\Auth::user()->type == 'admin' || \Auth::user()->type == 'company' || \Auth::user()->type == 'senior accounting')
        <div class="row">
            <div class="col-12">
                <div class="clean-card fade-in">
                    <div class="card-header-clean">
                        <div class="d-flex justify-content-between align-items-center">
                            <h6><i class="ti ti-user-check me-2"></i>{{__('Request Approval Reimbursement')}}</h6>
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
                                            @if(\Auth::user()->type == 'admin' || \Auth::user()->type == 'company' || \Auth::user()->type == 'senior audit' || \Auth::user()->type == 'senior accounting' || \Auth::user()->type == 'manager audit' || \Auth::user()->type == 'partners')
                                                <th>{{__('Employee')}}</th>
                                            @endif
                                            <th>{{__('Client')}}</th>
                                            <th>{{__('Approval By')}}</th>
                                            <th>{{__('Reimbursement Type')}}</th>
                                            <th>{{__('Date')}}</th>
                                            <th>{{__('Amount')}}</th>
                                            <th>{{__('Description')}}</th>
                                            <th>{{__('Image')}}</th>
                                            <th>{{__('Action')}}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($approval as $approvals)
                                            <tr>
                                                <td>
                                                    <input type="checkbox" class="approval-checkbox form-check-input" data-id="{{ $approvals->id }}">
                                                </td>
                                                @if(\Auth::user()->type == 'admin' || \Auth::user()->type == 'company' || \Auth::user()->type == 'senior audit' || \Auth::user()->type == 'senior accounting' || \Auth::user()->type == 'manager audit' || \Auth::user()->type == 'partners')
                                                    <td data-label="Employee">
                                                        <div class="employee-info">
                                                            <div class="employee-avatar">
                                                                {{ substr(!empty($approvals->employee->name)?$approvals->employee->name:'U', 0, 1) }}
                                                            </div>
                                                            <span>{{!empty($approvals->employee->name)?$approvals->employee->name:'-'}}</span>
                                                        </div>
                                                    </td>
                                                @endif
                                                <td data-label="Client">
                                                    <span class="client-badge">{{!empty($approvals->client->name) ? $approvals->client->name:'-'}}</span>
                                                </td>
                                                <td data-label="Approval By">{{!empty($approvals->approvals->name)?$approvals->approvals->name:'-'}}</td>
                                                <td data-label="Reimbursement Type">
                                                    <span class="type-badge">{{!empty($approvals->reimbursment_type)?$approvals->reimbursment_type:'-'}}</span>
                                                </td>
                                                <td data-label="Date">{{date("l, d-m-Y",strtotime($approvals->date))}}</td>
                                                <td data-label="Amount">
                                                    <span class="amount-badge">
                                                        {{!empty(number_format($approvals->amount))?'Rp ' . number_format($approvals->amount):'-'}}
                                                    </span>
                                                </td>
                                                <td data-label="Description">
                                                    <span style="max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; display: inline-block;" 
                                                          data-bs-toggle="tooltip" title="{{$approvals->description}}">
                                                        {{!empty($approvals->description)?$approvals->description:'-'}}
                                                    </span>
                                                </td>
                                                <td data-label="Image">
                                                    @if(!empty($approvals->reimbursment_image))
                                                        <img alt="Reimbursement Image" 
                                                             src="{{ asset('storage/reimbursment_images/'.$approvals->reimbursment_image) }}" 
                                                             class="reimbursement-img view-images" 
                                                             data-bs-toggle="tooltip" 
                                                             title="{{__('View Screenshot images')}}" 
                                                             data-id="{{$approvals->id}}" 
                                                             id="track-images-{{$approvals->id}}"
                                                             onerror="this.src='{{ asset('assets/images/gallery.png') }}'">
                                                    @else
                                                        <div class="reimbursement-placeholder view-images" 
                                                             data-bs-toggle="tooltip" 
                                                             title="{{__('View Screenshot images')}}"
                                                             data-id="{{$approvals->id}}" 
                                                             id="track-images-{{$approvals->id}}">
                                                            <i class="ti ti-photo"></i>
                                                        </div>
                                                    @endif
                                                </td>
                                                <td data-label="Action">
                                                    <a href="#" data-url="{{ URL::to('reimbursment-client/'.$approvals->id.'/action') }}" 
                                                       data-size="lg" data-ajax-popup="true" 
                                                       data-title="{{__('Reimbursment Action')}}" 
                                                       class="action-btn btn-action" 
                                                       data-bs-toggle="tooltip" title="{{__('Reimbursment Action')}}">
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
                                <p>{{__('All client reimbursement requests have been processed.')}}</p>
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
            <div class="modal-content image_sider_div" style="border-radius: 8px; border: none; box-shadow: 0 10px 40px rgba(0,0,0,0.1);">
            </div>
        </div>
    </div>
@endsection

@push('script-page')
    <script src="{{url('js/swiper.min.js')}}"></script>
    <script type="text/javascript">
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

            // Auto-submit on show_entries change
            $('#show_entries').change(function() {
                $('#filterForm').submit();
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

                if (confirm('{{__("Are you sure you want to approve selected client reimbursement requests?")}}')) {
                    var url = "{{ route('approve-multiple-client') }}";
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
                            alert('{{__("Client reimbursement requests have been approved successfully.")}}');
                            window.location.reload();
                        },
                        error: function () {
                            alert('{{__("Something went wrong. Please try again later.")}}');
                            $('#approve-selected').prop('disabled', false).html('<i class="ti ti-check me-1"></i>{{__("Approve Selected")}}');
                        },
                    });
                }
            });
        });

        $(document).on('click', '.view-images', function () {
            var p_url = "{{route('reimbursment-client.image.view')}}";
            var data = {
                'id': $(this).attr('data-id')
            };
            postAjax(p_url, data, function (res) {
                $('.image_sider_div').html(res);
                $('#exampleModalCenter').modal('show');
            });
        });
    </script>
@endpush