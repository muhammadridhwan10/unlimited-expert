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
        <a href="#" data-size="lg" data-url="{{ route('overtime.create') }}" data-ajax-popup="true" data-bs-toggle="tooltip" title="{{__('Create')}}" data-title="{{__('Create Overtime')}}" class="btn btn-sm btn-primary">
            <i class="ti ti-plus"></i>
        </a>
    </div>
@endsection

@section('content')

    <div class="row">
        <div class="col-sm-12">
            <div class=" mt-2 " id="multiCollapseExample1">
                <div class="card">
                    <div class="card-body">
                        {{ Form::open(array('route' => array('overtime.index'),'method'=>'get','id'=>'report_monthly_overtime')) }}
                        <div class="row align-items-center justify-content-end">
                            <div class="col-auto">
                                <div class="row">
                                    <div class="col-auto">
                                        <div class="btn-box">
                                            {{Form::label('month',__('Month'),['class'=>'form-label'])}}
                                            {{Form::month('month',isset($_GET['month'])?$_GET['month']:date('Y-m'),array('class'=>'month-btn form-control'))}}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-auto">
                                <div class="row">
                                    <div class="col-auto mt-4">
                                        <a href="#" class="btn btn-sm btn-primary" onclick="document.getElementById('report_monthly_overtime').submit(); return false;" data-bs-toggle="tooltip" title="{{__('Apply')}}" data-original-title="{{__('apply')}}">
                                            <span class="btn-inner--icon"><i class="ti ti-search"></i></span>
                                        </a>
                                        <a href="{{route('overtime.index')}}" class="btn btn-sm btn-danger " data-bs-toggle="tooltip"  title="{{ __('Reset') }}" data-original-title="{{__('Reset')}}">
                                            <span class="btn-inner--icon"><i class="ti ti-trash-off text-white-off "></i></span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    {{ Form::close() }}
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-xl-12">
            <div class="card">
            <div class="card-body table-border-style">
                    <div class="table-responsive">
                    <table class="table datatable">
                            <thead>
                            <tr>
                                @if(\Auth::user()->type == 'admin' || \Auth::user()->type == 'company' || \Auth::user()->type == 'partners' || \Auth::user()->type == 'client' || \Auth::user()->type == 'staff_client')
                                    <th>{{__('Employee')}}</th>
                                @endif
                                <th>{{__('Project Name')}}</th>
                                <th>{{__('Approval By')}}</th>
                                <th>{{__('Start Date')}}</th>
                                <th>{{__('Start Time')}}</th>
                                <th>{{__('End Time')}}</th>
                                <th>{{__('Total Time')}}</th>
                                <th width="200px">{{__('Note')}}</th>
                                <th>{{__('Status')}}</th>
                                <th width="200px">{{__('Action')}}</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach ($employeeOvertimes as $overtime)
                                <tr>
                                    @if(\Auth::user()->type == 'admin' || \Auth::user()->type == 'company' || \Auth::user()->type == 'partners' || \Auth::user()->type == 'client' || \Auth::user()->type == 'staff_client')
                                        <td>{{!empty($overtime->employee->name)?$overtime->employee->name:'-'}}</td>
                                    @endif
                                    <td>{{!empty($overtime->project->project_name)?$overtime->project->project_name:'-'}}</td>
                                    <td>{{!empty($overtime->approvals->name)?$overtime->approvals->name:'-'}}</td>
                                    <td>{{date("l, d-m-Y",strtotime($overtime->start_date))}}</td>
                                    <td>{{ ($overtime->start_time !='00:00:00') ?\Auth::user()->timeFormat( $overtime->start_time):'00:00' }} </td>
                                    <td>{{ ($overtime->end_time !='00:00:00') ?\Auth::user()->timeFormat( $overtime->end_time):'00:00' }}</td>
                                    <td>{{!empty($overtime->total_time)?$overtime->total_time:'00:00:00'}}</td>
                                    <td>{{!empty($overtime->note)?$overtime->note:'-'}}</td>
                                    <td>

                                        @if($overtime->status=="Pending")
                                            <div class="status_badge badge bg-warning p-2 px-3 rounded">{{ $overtime->status }}</div>
                                        @elseif($overtime->status=="Approved")
                                            <div class="status_badge badge bg-success p-2 px-3 rounded">{{ $overtime->status }}</div>
                                        @else($overtime->status =="Reject")
                                            <div class="status_badge badge bg-danger p-2 px-3 rounded">{{ $overtime->status }}</div>
                                        @endif
                                    </td>
                                    <td>
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
                </div>
            </div>
        </div>
    </div>

    @if(\Auth::user()->type == 'admin' || \Auth::user()->type == 'company' || \Auth::user()->type == 'senior audit' || \Auth::user()->type == 'senior accounting' || \Auth::user()->type == 'manager audit' || \Auth::user()->type == 'partners')
        <div class="row">
            <div class="col-xl-12">
                <div class="card">
                <div class="card-header"><h6 class="mb-0">{{__('Request Approval')}}</h6></div>
                <div class="card-body table-border-style">
                        <div class="table-responsive">
                        <table class="table datatables">
                                <thead>
                                <tr>
                                    @if(\Auth::user()->type == 'admin' || \Auth::user()->type == 'company' || \Auth::user()->type == 'senior audit' || \Auth::user()->type == 'senior accounting' || \Auth::user()->type == 'manager audit' || \Auth::user()->type == 'partners')
                                        <th>{{__('Employee')}}</th>
                                    @endif
                                    <th>{{__('Project Name')}}</th>
                                    <th>{{__('Start Date')}}</th>
                                    <th>{{__('Start Time')}}</th>
                                    <th>{{__('End Time')}}</th>
                                    <th width="200px">{{__('Note')}}</th>
                                    <th width="100px">{{__('Action')}}</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach ($approval as $approvals)
                                    <tr>
                                        @if(\Auth::user()->type == 'admin' || \Auth::user()->type == 'company' || \Auth::user()->type == 'senior audit' || \Auth::user()->type == 'senior accounting' || \Auth::user()->type == 'manager audit' || \Auth::user()->type == 'partners')
                                            <td>{{!empty($approvals->employee->name)?$approvals->employee->name:'-'}}</td>
                                        @endif
                                        <td>{{!empty($approvals->project->project_name)?$approvals->project->project_name:'-'}}</td>
                                        <td>{{date("l, d-m-Y",strtotime($approvals->start_date))}}</td>
                                        <td>{{ ($approvals->start_time !='00:00:00') ?\Auth::user()->timeFormat( $approvals->start_time):'00:00' }} </td>
                                        <td>{{ ($approvals->end_time !='00:00:00') ?\Auth::user()->timeFormat( $approvals->end_time):'00:00' }}</td>
                                        <td>{{!empty($approvals->note)?$approvals->note:'-'}}</td>
                                        <td>
                                            <div class="action-btn bg-warning ms-2">
                                                <a href="#" data-url="{{ URL::to('overtime/'.$approvals->id.'/action') }}" data-size="lg" data-ajax-popup="true" data-title="{{__('Overtime Action')}}" class="mx-3 btn btn-sm  align-items-center" data-bs-toggle="tooltip" title="{{__('Overtime Action')}}" data-original-title="{{__('Overtime Action')}}">
                                                    <i class="ti ti-caret-right text-white"></i> </a>
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
    @endif
@endsection

@push('script-page')
	<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
	<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <script>
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

                        if (value.total_leave >= value.days) {
                            $('#leave_type_id').append('<option value="' + value.id + '" disabled>' + value.title + '&nbsp(' + value.total_leave + '/' + value.days + ')</option>');
                        } else {
                            $('#leave_type_id').append('<option value="' + value.id + '">' + value.title + '&nbsp(' + value.total_leave + '/' + value.days + ')</option>');
                        }
                    });

                }
            });
        });

    </script>
@endpush
