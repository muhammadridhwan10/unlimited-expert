@extends('layouts.admin')
@section('page-title')
    {{__('Projects Report')}}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
    <li class="breadcrumb-item">{{__('Projects Report')}}</li>
@endsection
@push('script-page')

    <script type="text/javascript" src="{{ asset('js/jszip.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/pdfmake.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/vfs_fonts.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/dataTables.buttons.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/buttons.html5.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/html2pdf.bundle.min.js') }}"></script>
    <script>
        var filename = $('#filename').val();

        function saveAsPDF() {
            var element = document.getElementById('printableArea');
            var opt = {
                margin: 0.3,
                filename: filename,
                image: {type: 'jpeg', quality: 1},
                html2canvas: {scale: 4, dpi: 72, letterRendering: true},
                jsPDF: {unit: 'in', format: 'A4'}
            };
            html2pdf().set(opt).from(element).save();

        }

        $(document).ready(function () {
            var filename = $('#filename').val();
            $('#report-dataTable').DataTable({
                dom: 'lBfrtip',
                buttons: [
                    {
                        extend: 'pdf',
                        title: filename
                    },
                    {
                        extend: 'excel',
                        title: filename
                    }, {
                        extend: 'csv',
                        title: filename
                    }
                ]
            });
        });
    </script>
    <script>
        $('input[name="type"]:radio').on('change', function (e) {
            var type = $(this).val();
            if (type == 'monthly') {
                $('.month').addClass('d-block');
                $('.month').removeClass('d-none');
                $('.year').addClass('d-none');
                $('.year').removeClass('d-block');
            } else {
                $('.year').addClass('d-block');
                $('.year').removeClass('d-none');
                $('.month').addClass('d-none');
                $('.month').removeClass('d-block');
            }
        });

        $('input[name="type"]:radio:checked').trigger('change');

    </script>
    <script>
        function exportToExcel() {
            $('#export_excel').val(1);
            document.getElementById('report_projects').submit();
        }
    </script>
@endpush
@section('action-btn')
    <div class="float-end">
{{--        <a href="{{ route('leave.export') }}" data-bs-toggle="tooltip" title="{{ __('Export') }}"--}}
{{--           class="btn btn-sm btn-primary">--}}
{{--            <i class="ti ti-file-export"></i>--}}
{{--        </a>--}}

        <a href="#" class="btn btn-sm btn-primary" onclick="saveAsPDF()"data-bs-toggle="tooltip" title="{{__('Download')}}" data-original-title="{{__('Download')}}">
            <span class="btn-inner--icon"><i class="ti ti-download"></i></span>
        </a>
        <a href="#" class="btn btn-sm btn-success" onclick="exportToExcel()" data-bs-toggle="tooltip" title="{{__('Export to Excel')}}" data-original-title="{{__('Export to Excel')}}">
            <span class="btn-inner--icon"><i class="ti ti-file"></i></span>
        </a>
    </div>
@endsection

@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class=" mt-2 " id="multiCollapseExample1">
                <div class="card">
                    <div class="card-body">
                        {{ Form::open(array('route' => array('report.projects'),'method'=>'get','id'=>'report_projects')) }}
                        {{ Form::hidden('export_excel', 0, ['id' => 'export_excel']) }}
                        <div class="row align-items-center justify-content-end">
                            <div class="col-auto">
                                <div class="row">
                                    <div class="col-auto" style = "width:400px;">
                                        <div class="btn-box">
                                            {{ Form::label('client_id', __('Client'), ['class' => 'form-label']) }}
                                            {{ Form::select('client_id[]', $client, isset($_GET['client_id']) ? $_GET['client_id'] : null, ['class' => 'form-control select2','id'=>'choices-multiple1','multiple']) }}
                                        </div>
                                    </div>
                                    <div class="col-auto" style = "width:400px;">
                                        <div class="btn-box">
                                            {{ Form::label('user_ids', __('Employee'), ['class' => 'form-label']) }}
                                            {{ Form::select('user_ids[]', $employess, isset($_GET['user_ids']) ? $_GET['user_ids'] : null, ['class' => 'form-control select2','id'=>'choices-multiple2','multiple']) }}
                                        </div>
                                    </div>
                                    <div class="col-auto" style = "width:400px;">
                                        <div class="btn-box">
                                            {{ Form::label('label', __('Label'), ['class' => 'form-label']) }}
                                            {{ Form::select('label[]', \App\Models\Project::$label, isset($request->label) ? $request->label : null, ['class' => 'form-control select2', 'id'=>'choices-multiple3','multiple']) }}
                                        </div>
                                    </div>
                                    <div class="col-auto" style = "width:400px;">
                                        <div class="btn-box">
                                            {{ Form::label('start_month', __('Start Month'), ['class' => 'form-label']) }}
                                            {{ Form::month('start_month', isset($_GET['start_month']) ? $_GET['start_month'] : null, ['class' => 'month-btn form-control']) }}
                                        </div>
                                    </div>
                                    <div class="col-auto" style = "width:400px;">
                                        <div class="btn-box">
                                            {{ Form::label('end_month', __('End Month'), ['class' => 'form-label']) }}
                                            {{ Form::month('end_month', isset($_GET['end_month']) ? $_GET['end_month'] : null, ['class' => 'month-btn form-control']) }}
                                        </div>
                                    </div>

                                </div>
                            </div>
                            <div class="col-auto mt-4">
                                <div class="row">
                                    <div class="col-auto">
                                        <a href="#" class="btn btn-sm btn-primary" onclick="document.getElementById('report_projects').submit(); return false;" data-bs-toggle="tooltip" title="{{__('Apply')}}" data-original-title="{{__('apply')}}">
                                            <span class="btn-inner--icon"><i class="ti ti-search"></i></span>
                                        </a>
                                        <a href="{{route('report.leave')}}" class="btn btn-sm btn-danger " data-bs-toggle="tooltip"  title="{{ __('Reset') }}" data-original-title="{{__('Reset')}}">
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
    <div id="printableArea" class="">
        <div class="row">
            <div class="col">
                <div class="card">
                    <div class="card-body table-border-style">
                        <div class="table-responsive py-4">
                            <table class="table datatable" id="report-dataTable">
                                <thead>
                                <tr>
                                    <th>{{__('Employee')}}</th>
                                    <th>{{__('Start Date')}}</th>
                                    <th>{{__('Project Name')}}</th>
                                    <th>{{__('Label Project')}}</th>
                                    <th>{{__('Client Name')}}</th>
                                    <th>{{__('Logged Hours')}}</th>
                                    <th>{{__('Status')}}</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($employeeProject as $project_user)
                                    @php
                                        $hours_format_number = 0;
                                        $total_hours = 0;
                                        $hourdiff_late = 0;
                                        $esti_late_hour =0;
                                        $esti_late_hour_chart=0;

                                        $logged_hours = 0;
                                        $timesheets = App\Models\Timesheet::where('project_id',$project_user->project_id)->where('created_by' ,$project_user->user_id)->get();
                                    @endphp
                                    @foreach($timesheets as $timesheet)
                                        @php

                                            $hours =  date('H', strtotime($timesheet->time));
                                            $minutes =  date('i', strtotime($timesheet->time));
                                            $total_hours = $hours + ($minutes/60) ;
                                            $logged_hours += $total_hours ;
                                            $hours_format_number = number_format($logged_hours, 2, '.', '');
                                        @endphp
                                    @endforeach
                                    @if(!empty($project_user->project->project_name))
                                        <tr>
                                            <td>{{!empty($project_user->user->name)?$project_user->user->name:'-'}}</td>
                                            <td>{{!empty($project_user->project->start_date)?$project_user->project->start_date:'-'}}</td>
                                            <td>{{!empty($project_user->project->project_name)?$project_user->project->project_name:'-'}}</td>
                                            <td>{{!empty($project_user->project->label)?$project_user->project->label:'-'}}</td>
                                             <td>{{!empty($project_user->project->user->name)?$project_user->project->user->name:'-'}}</td>
                                            <td>{{$hours_format_number}}</td>
                                            <td>
                                                @if($project_user->project->status == "on_hold")
                                                    <div class="status_badge badge bg-warning p-2 px-3 rounded">{{ $project_user->project->status }}</div>
                                                @elseif($project_user->project->status=="complete")
                                                    <div class="status_badge badge bg-success p-2 px-3 rounded">{{ $project_user->project->status }}</div>
                                                @elseif($project_user->project->status=="in_progress")
                                                    <div class="status_badge badge bg-info p-2 px-3 rounded">{{ $project_user->project->status }}</div>
                                                @else($project_user->project->status =="canceled")
                                                    <div class="status_badge badge bg-danger p-2 px-3 rounded">{{ $project_user->project->status }}</div>
                                                @endif
                                            </td>
                                        </tr>
                                    @endif
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

