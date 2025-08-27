@extends('layouts.admin')

@section('page-title')
    {{__('Manage Sick Letter')}}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
    <li class="breadcrumb-item">{{__('Manage Sick Letter')}}</li>
@endsection

@section('action-btn')
    <div class="float-end">
        @can('create leave')
        <a href="#" data-size="lg" data-url="{{ route('sick-letter.create') }}" data-ajax-popup="true" 
           data-bs-toggle="tooltip" title="{{__('Create')}}" data-title="{{__('Create Sick Letter')}}" 
           class="btn btn-primary">
            <i class="ti ti-plus"></i> {{__('Add Sick Letter')}}
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

    /* Days badge */
    .days-badge {
        background: #17a2b8;
        color: white;
        padding: 0.25rem 0.5rem;
        border-radius: 4px;
        font-size: 0.8rem;
        font-weight: 600;
    }

    /* Sick letter image styling */
    .sick-letter-img {
        width: 40px;
        height: 40px;
        border-radius: 8px;
        border: 2px solid #dee2e6;
        cursor: pointer;
        transition: all 0.2s ease;
        object-fit: cover;
    }

    .sick-letter-img:hover {
        border-color: #007bff;
        transform: scale(1.1);
    }

    .sick-letter-placeholder {
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

    .sick-letter-placeholder:hover {
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

        .sick-letter-img,
        .sick-letter-placeholder {
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
                            <div class="col-lg-4 col-md-6">
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

                            <!-- Applied Date Range Filter -->
                            <div class="col-lg-4 col-md-6">
                                <label class="form-label">{{__('Applied Date From')}}</label>
                                <input type="date" name="date_from" id="date_from" class="form-control" value="{{ request('date_from') }}">
                            </div>

                            <div class="col-lg-4 col-md-6">
                                <label class="form-label">{{__('Applied Date To')}}</label>
                                <input type="date" name="date_to" id="date_to" class="form-control" value="{{ request('date_to') }}">
                            </div>

                            <!-- Sick Letter Date Range Filter -->
                            <div class="col-lg-4 col-md-6">
                                <label class="form-label">{{__('Sick Letter Date From')}}</label>
                                <input type="date" name="sick_date_from" id="sick_date_from" class="form-control" value="{{ request('sick_date_from') }}">
                            </div>

                            <div class="col-lg-4 col-md-6">
                                <label class="form-label">{{__('Sick Letter Date To')}}</label>
                                <input type="date" name="sick_date_to" id="sick_date_to" class="form-control" value="{{ request('sick_date_to') }}">
                            </div>

                            <!-- Search Box -->
                            <div class="col-lg-4 col-md-6">
                                <label class="form-label">{{__('Search')}}</label>
                                <input type="text" name="search" id="search" class="form-control" 
                                       placeholder="{{__('Search by employee name...')}}" 
                                       value="{{ request('search') }}">
                            </div>

                            <!-- Filter Buttons -->
                            <div class="col-12 d-flex justify-content-end">
                                <div class="btn-group" role="group">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="ti ti-search me-1"></i>{{__('Apply Filters')}}
                                    </button>
                                    <a href="{{ request()->url() }}" class="btn btn-secondary">
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

    <!-- Sick Letters -->
    <div class="row">
        <div class="col-12">
            <div class="clean-card fade-in">
                <div class="card-header-clean">
                    <h6><i class="ti ti-file-medical me-2"></i>{{__('Sick Letters')}}</h6>
                </div>
                <div class="card-body p-0">
                    @if($absence_sick->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-clean">
                                <thead>
                                    <tr>
                                        @if(\Auth::user()->type!='employee')
                                            <th>{{__('Employee')}}</th>
                                        @endif
                                        <th>{{__('Total Days')}}</th>
                                        <th>{{__('Sick Letter')}}</th>
                                        <th>{{__('Date Sick Letter')}}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($absence_sick as $sick)
                                        <tr>
                                            @if(\Auth::user()->type!='employee')
                                                <td data-label="Employee">
                                                    <div class="employee-info">
                                                        <div class="employee-avatar">
                                                            {{ substr(!empty(\Auth::user()->getEmployee($sick->employee_id))?\Auth::user()->getEmployee($sick->employee_id)->name:'U', 0, 1) }}
                                                        </div>
                                                        <span>{{ !empty(\Auth::user()->getEmployee($sick->employee_id))?\Auth::user()->getEmployee($sick->employee_id)->name:'-' }}</span>
                                                    </div>
                                                </td>
                                            @endif
                                            <td data-label="Total Days">
                                                @if(!empty($sick->total_sick_days))
                                                    <span class="days-badge">{{ $sick->total_sick_days }} days</span>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td data-label="Sick Letter">
                                                @if(!empty($sick->sick_letter))
                                                    <img alt="Sick Letter" 
                                                         src="{{ asset('storage/sick_letters/'.$sick->sick_letter) }}" 
                                                         class="sick-letter-img view-images" 
                                                         data-bs-toggle="tooltip" 
                                                         title="{{__('View Sick Letter')}}" 
                                                         data-id="{{$sick->id}}" 
                                                         id="track-images-{{$sick->id}}"
                                                         onerror="this.src='{{ asset('assets/images/gallery.png') }}'">
                                                @else
                                                    <div class="sick-letter-placeholder" 
                                                         data-bs-toggle="tooltip" 
                                                         title="{{__('No sick letter uploaded')}}">
                                                        <i class="ti ti-photo"></i>
                                                    </div>
                                                @endif
                                            </td>
                                            <td data-label="Date Sick Letter">
                                                {{ !empty($sick->date_sick_letter) ? \Auth::user()->dateFormat($sick->date_sick_letter) : '-' }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="empty-state">
                            <i class="ti ti-file-medical"></i>
                            <h5>{{__('No Sick Letters Found')}}</h5>
                            <p>{{__('There are no sick letters to display.')}}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Pagination -->
    @if($absence_sick->hasPages())
        <div class="d-flex justify-content-center mt-3">
            {{ $absence_sick->links() }}
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
    <script>
        $(document).ready(function() {
            // Initialize tooltips
            $('[data-bs-toggle="tooltip"]').tooltip();

            // Date validation
            $('#date_from, #sick_date_from').change(function() {
                var fromDate = $(this).val();
                var toField = $(this).attr('id') === 'date_from' ? '#date_to' : '#sick_date_to';
                
                if (fromDate) {
                    $(toField).attr('min', fromDate);
                }
            });

            $('#date_to, #sick_date_to').change(function() {
                var toDate = $(this).val();
                var fromField = $(this).attr('id') === 'date_to' ? '#date_from' : '#sick_date_from';
                var fromDate = $(fromField).val();
                
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
        });

        $(document).on('click', '.view-images', function () {
            var p_url = "{{route('sick-letter.image.view')}}";
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