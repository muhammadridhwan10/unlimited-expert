@extends('layouts.admin')
@section('page-title')
    {{__('Manage Project Timesheet')}}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
    <li class="breadcrumb-item"><a href="{{route('projects.index')}}">{{__('Project')}}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('projects.show',\Crypt::encrypt($project->id)) }}">{{ucwords($project->project_name)}}</a></li>
    <li class="breadcrumb-item">{{__('Project Timesheet')}}</li>
@endsection

@push('script-page')

    <script type="text/javascript" src="{{ asset('js/jszip.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/pdfmake.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/vfs_fonts.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/dataTables.buttons.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/buttons.html5.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/html2pdf.bundle.min.js') }}"></script>
    <script>
        function exportToExcel() {
            $('#export_excel').val(1);
            document.getElementById('report_monthly_tracker').submit();
        }
    </script>
@endpush

@section('action-btn')
    {{-- <div class="float-end">
        <a href="#" class="btn btn-sm btn-success" onclick="exportToExcel()" data-bs-toggle="tooltip" title="{{__('Export to Excel')}}" data-original-title="{{__('Export to Excel')}}">
            <span class="btn-inner--icon"><i class="ti ti-file"></i></span>
        </a>
    </div> --}}
@endsection



@section('content')

    {{-- <div class="row">
        <div class="col-sm-12">
            <div class=" mt-2 " id="multiCollapseExample1">
                <div class="card">
                    <div class="card-body">
                        {{ Form::open(array('route' => array('project.timesheet', $project->id),'method'=>'get','id'=>'report_monthly_tracker')) }}
                        {{ Form::hidden('export_excel', 0, ['id' => 'export_excel']) }}
                        <div class="row align-items-center justify-content-end">
                            <div class="col-auto">
                                <div class="row">
                                    <div class="col-auto">
                                        <div class="btn-box">
                                            {{Form::label('month',__('Month'),['class'=>'form-label'])}}
                                           {{ Form::month('month', request()->input('month', ''), ['class' => 'month-btn form-control']) }}

                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-auto">
                                <div class="btn-box">
                                    {{ Form::label('date', __('Date'), ['class' => 'form-label']) }}
                                    {{ Form::date('date', request()->input('date'), ['class' => 'form-control']) }}
                                </div>
                            </div>
                            <div class="col-auto">
                                <div class="btn-box">
                                    {{ Form::label('status', __('Status'), ['class' => 'form-label']) }}
                                    <select class="form-control select" name="status" id="status" placeholder="Select Status">
                                        <option value="0">{{ __('All Status') }}</option>
                                        @foreach(\App\Models\Project::$project_status as $key => $label)
                                            <option value="{{ $key }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-auto">
                                <div class="btn-box" style = "width:400px;">
                                    {{ Form::label('user_id', __('Employee'), ['class' => 'form-label']) }}
                                    {{ Form::select('user_id', $employee, isset($_GET['user_id']) ? $_GET['user_id'] : 0, ['class' => 'form-control select2']) }}
                                </div>
                            </div>
                            <div class="col-auto">
                                <div class="row">
                                    <div class="col-auto mt-4">
                                        <a href="#" class="btn btn-sm btn-primary" onclick="document.getElementById('report_monthly_tracker').submit(); return false;" data-bs-toggle="tooltip" title="{{__('Apply')}}" data-original-title="{{__('apply')}}">
                                            <span class="btn-inner--icon"><i class="ti ti-search"></i></span>
                                        </a>
                                        <a href="{{route('project.timesheet', $project->id)}}" class="btn btn-sm btn-danger " data-bs-toggle="tooltip"  title="{{ __('Reset') }}" data-original-title="{{__('Reset')}}">
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
    </div> --}}

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body table-border-style mt-2">
                    <div class="table-responsive">
                        <table class="table datatable">
                            <thead>
                            <tr>

                                <!-- <th> {{__('Description')}}</th> -->
                                <th> {{__('Employee')}}</th>
                                <th> {{__('Project')}}</th>
                                <th> {{__('Date')}}</th>
                                <th> {{__('Time')}}</th>
                                <th> {{__('Status')}}</th>
                                <th>{{__('Action')}}</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach ($employeeTimesheet as $timesheet)
                                <tr>
                                    <td>{{!empty($timesheet->user->name)?$timesheet->user->name:'-'}}</td>
                                    <td>{{!empty($timesheet->project->project_name)?$timesheet->project->project_name:'-'}}</td>
                                    <td>{{date("l, d-m-Y",strtotime($timesheet->date))}}</td>
                                    <td>{{date("H:i:s",strtotime($timesheet->time))}}</td>
                                    <td>{{!empty($timesheet->project->status)?$timesheet->project->status:'-'}}</td>
                                    @if (Gate::check('edit timesheet') || Gate::check('delete timesheet'))
                                        <td class="Action">
                                                <span>
                                                    @can('edit timesheet')
                                                        <div class="action-btn bg-primary ms-2">
                                                            <a href="#!" data-size="lg" data-url="{{ route('timesheet.edit',\Crypt::encrypt($timesheet->id)) }}" data-ajax-popup="true" 
                                                            class="mx-3 btn btn-sm align-items-center" data-bs-toggle="tooltip" data-bs-original-title="{{__('Edit Timesheet')}}">
                                                                <i class="ti ti-pencil text-white"></i>
                                                            </a>
                                                        </div>
                                                    @endcan
                                                    @can('delete timesheet')
                                                        <div class="action-btn bg-danger ms-2">
                                                        {!! Form::open(['method' => 'DELETE', 'route' => ['timesheet.destroy', $timesheet->id]]) !!}
                                                                    <a href="#" class="mx-3 btn btn-sm align-items-center bs-pass-para " data-bs-toggle="tooltip" title="{{__('Delete')}}"
                                                                       data-original-title="{{ __('Delete') }}"
                                                                       data-confirm="{{ __('Are You Sure?') . '|' . __('This action can not be undone. Do you want to continue?') }}"
                                                                       data-confirm-yes="document.getElementById('delete-form-{{ $timesheet->id }}').submit();">
                                                                        <i class="ti ti-trash text-white"></i>
                                                                    </a>
                                                                {!! Form::close() !!}
                                                            </div>
                                                    @endcan
                                                </span>
                                        </td>
                                    @endif
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                        <div class="d-flex justify-content-center mt-3">
                            {{ $employeeTimesheet->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('script-page')
@endpush
