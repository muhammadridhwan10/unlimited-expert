@extends('layouts.admin')
@section('page-title')
    {{__('Manage Attendance List')}}
@endsection
@push('script-page')
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
                jsPDF: {unit: 'in', format: 'A2'}
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
        function exportToExcel() {
            $('#export_excel').val(1);
            document.getElementById('attendanceemployee_filter').submit();
        }
    </script>
    <script>
        $('input[name="type"]:radio').on('change', function (e) {
            var type = $(this).val();

            if (type == 'monthly') {
                $('.month').addClass('d-block');
                $('.month').removeClass('d-none');
                $('.date').addClass('d-none');
                $('.date').removeClass('d-block');
            } else {
                $('.date').addClass('d-block');
                $('.date').removeClass('d-none');
                $('.month').addClass('d-none');
                $('.month').removeClass('d-block');
            }
        });

        $('input[name="type"]:radio:checked').trigger('change');

    </script>
@endpush
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
    <li class="breadcrumb-item">{{__('Attendance')}}</li>
@endsection

@section('action-btn')
    <div class="float-end">
        <a href="#" class="btn btn-sm btn-primary" onclick="saveAsPDF()" data-bs-toggle="tooltip" title="{{ __('Download') }}"
           data-original-title="{{ __('Download') }}">
            <span class="btn-inner--icon"><i class="ti ti-download"></i></span>
        </a>
        <a href="#" class="btn btn-sm btn-success" onclick="exportToExcel()" data-bs-toggle="tooltip" title="{{__('Export to Excel')}}" data-original-title="{{__('Export to Excel')}}">
            <span class="btn-inner--icon"><i class="ti ti-file"></i></span>
        </a>

       {{-- <a href="{{route('report.attendance',[isset($_GET['month'])?$_GET['month']:date('Y-m'),isset($_GET['branch'])?$_GET['branch']:0,isset($_GET['department'])?$_GET['department']:0])}}" class="btn btn-sm btn-primary" onclick="saveAsPDF()"data-bs-toggle="tooltip" title="{{__('Download Filter')}}" data-original-title="{{__('Download Filter')}}">
           <span class="btn-inner--icon"><i class="ti ti-download"></i></span>
       </a> --}}

    </div>
@endsection
@section('content')

    <div class="row">
        <div class="col-sm-12">
            <div class=" mt-2 " id="multiCollapseExample1">
                <div class="card">
                    <div class="card-body">
                        {{ Form::open(array('route' => array('attendanceemployee.index'),'method'=>'get','id'=>'attendanceemployee_filter')) }}
                        {{ Form::hidden('export_excel', 0, ['id' => 'export_excel']) }}
                        <div class="row align-items-center justify-content-end">
                            <div class="col-xl-10">
                                <div class="row">

                                    <div class="col-3">
                                        <label class="form-label">{{__('Type')}}</label> <br>

                                        <div class="form-check form-check-inline form-group">
                                            <input type="radio" id="monthly" value="monthly" name="type" class="form-check-input" {{isset($_GET['type']) && $_GET['type']=='monthly' ?'checked':'checked'}}>
                                            <label class="form-check-label" for="monthly">{{__('Monthly')}}</label>
                                        </div>
                                        <div class="form-check form-check-inline form-group">
                                            <input type="radio" id="daily" value="daily" name="type" class="form-check-input" {{isset($_GET['type']) && $_GET['type']=='daily' ?'checked':''}}>
                                            <label class="form-check-label" for="daily">{{__('Daily')}}</label>
                                        </div>

                                    </div>

                                    <div class="col-auto month">
                                        <div class="btn-box">
                                            {{Form::label('month',__('Month'),['class'=>'form-label'])}}
                                            {{Form::month('month',isset($_GET['month'])?$_GET['month']:date('Y-m'),array('class'=>'month-btn form-control month-btn'))}}
                                        </div>
                                    </div>
                                    {{-- <div class="col-auto date">
                                        <div class="btn-box">
                                            {{ Form::label('date', __('Date'),['class'=>'form-label'])}}
                                            {{ Form::date('date',isset($_GET['date'])?$_GET['date']:'', array('class' => 'form-control month-btn')) }}
                                        </div>
                                    </div> --}}
                                    <div class="col-auto date">
                                        <div class="btn-box">
                                            {{Form::label('start_date',__('Start Date'),['class'=>'form-label'])}}
                                            {{Form::date('start_date', isset($_GET['start_date']) ? $_GET['start_date'] : null, ['class'=>'form-control'])}}
                                        </div>
                                    </div>
                                    <div class="col-auto date">
                                        <div class="btn-box">
                                            {{Form::label('end_date',__('End Date'),['class'=>'form-label'])}}
                                            {{Form::date('end_date', isset($_GET['end_date']) ? $_GET['end_date'] : null, ['class'=>'form-control'])}}
                                        </div>
                                    </div>
                                    {{-- <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12">
                                        <div class="btn-box">
                                            {{ Form::label('branch_id', __('Branch'),['class'=>'form-label']) }}
                                            {{ Form::select('branch_id',$branch,null, array('class' => 'form-control select')) }}
                                        </div>
                                    </div>
                                    <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12">
                                        <div class="btn-box">
                                            {{ Form::label('department', __('Department'),['class'=>'form-label'])}}
                                            {{ Form::select('department', $department,isset($_GET['department'])?$_GET['department']:'', array('class' => 'form-control select')) }}
                                        </div>
                                    </div> --}}
                                    <div class="col-12">
                                        <div class="btn-box">
                                            {{ Form::label('employee_id', __('Employee'),['class'=>'form-label'])}}
                                            {{ Form::select('employee_id', $employees, isset($_GET['employee_id']) ? $_GET['employee_id'] : '', ['class' => 'form-control select2']) }}
                                        </div>
                                    </div>

                                </div>
                            </div>
                            <div class="col-auto mt-4">
                                <div class="row">
                                    <div class="col-auto">

                                        <a href="#" class="btn btn-sm btn-primary" onclick="document.getElementById('attendanceemployee_filter').submit(); return false;" data-bs-toggle="tooltip" title="{{__('Apply')}}" data-original-title="{{__('apply')}}">
                                            <span class="btn-inner--icon"><i class="ti ti-search"></i></span>
                                        </a>

                                        <a href="{{route('attendanceemployee.index')}}" class="btn btn-sm btn-danger " data-bs-toggle="tooltip"  title="{{ __('Reset') }}" data-original-title="{{__('Reset')}}">
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

    <div id="printableArea">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body table-border-style">

                        <div class="table-responsive">
                            <table class="table datatable" id="report-dataTable">
                                <thead>
                                <tr>
                                    @if(\Auth::user()->type!='employee')
                                        <th>{{__('Employee')}}</th>
                                        <th>{{__('Employee Branch')}}</th>
                                    @endif
                                    <th>{{__('Date')}}</th>
                                    <th>{{__('Status')}}</th>
                                    <th>{{__('Clock In')}}</th>
                                    <th>{{__('Clock Out')}}</th>
                                    <!-- <th>{{__('Location')}}</th> -->
                                    <th>{{__('Total Work')}}</th>
                                    <th>{{__('Late')}}</th>
                                    <th>{{__('Early Leaving')}}</th>
                                    <th>{{__('Overtime')}}</th>
                                    @if(Gate::check('edit attendance') || Gate::check('delete attendance'))
                                        <th>{{__('Action')}}</th>
                                    @endif
                                </tr>
                                </thead>
                                <tbody>

                                @foreach ($attendanceEmployee as $attendance)
                                    <tr>
                                        @if(\Auth::user()->type!='employee')
                                            <td>{{!empty($attendance->employee)?$attendance->employee->name:'' }}</td>
                                            <td>{{!empty($attendance->employee->branch)?$attendance->employee->branch->name:'' }}</td>
                                        @endif
                                        <td>{{ \Auth::user()->dateFormat($attendance->date) }}</td>
                                        <td>{{ $attendance->status }}</td>
                                        <td>{{ ($attendance->clock_in !='00:00:00') ?\Auth::user()->timeFormat( $attendance->clock_in):'00:00' }} </td>
                                        <td>{{ ($attendance->clock_out !='00:00:00') ?\Auth::user()->timeFormat( $attendance->clock_out):'00:00' }}</td>
                                        <?php
                                            
                                            // Waktu awal
                                            $startTime = Carbon\Carbon::parse($attendance->clock_in);

                                            // Waktu akhir
                                            $endTime = Carbon\Carbon::parse($attendance->clock_out);

                                            // Menghitung selisih waktu
                                            $diff = $endTime->diff($startTime);

                                            // Mengambil selisih jam dan menit
                                            $hours = $startTime->diffInHours($endTime);
                                            $minutes = $startTime->diffInMinutes($endTime) % 60;

                                            $total_work = $hours . ' Jam ' . $minutes . ' Menit';
                                        ?>
                                        <td>{{ $total_work }}</td>
                                        <td>{{ $attendance->late }}</td>
                                        <td>{{ $attendance->early_leaving }}</td>
                                        <td>{{ $attendance->overtime }}</td>
                                        @if(Gate::check('edit attendance') || Gate::check('delete attendance'))
                                            <td>
                                                @can('edit attendance')
                                                    <div class="action-btn bg-primary ms-2">
                                                        <a href="#" data-url="{{ URL::to('attendanceemployee/'.$attendance->id.'/edit') }}" data-size="lg" data-ajax-popup="true" data-title="{{__('Edit Attendance')}}" class="mx-3 btn btn-sm align-items-center" data-bs-toggle="tooltip" title="{{__('Edit')}}" data-original-title="{{__('Edit')}}">
                                                            <i class="ti ti-pencil text-white"></i></a>
                                                    </div>
                                                @endcan
                                                    @can('delete attendance')
                                                        <div class="action-btn bg-danger ms-2">
                                                            {!! Form::open(['method' => 'DELETE', 'route' => ['attendanceemployee.destroy', $attendance->id],'id'=>'delete-form-'.$attendance->id]) !!}

                                                            <a href="#" class="mx-3 btn btn-sm  align-items-center bs-pass-para" data-bs-toggle="tooltip" title="{{__('Delete')}}"
                                                            data-original-title="{{__('Delete')}}" data-confirm="{{__('Are You Sure?').'|'.__('This action can not be undone. Do you want to continue?')}}" data-confirm-yes="document.getElementById('delete-form-{{$attendance->id}}').submit();">
                                                                <i class="ti ti-trash text-white"></i></a>
                                                            {!! Form::close() !!}
                                                        </div>
                                                    @endif
                                            </td>
                                        @endif
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                            <div class="d-flex justify-content-center mt-3">
                                {{ $attendanceEmployee->links() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


@endsection

@push('script-page')
    <script>
        $(document).ready(function () {
            $('.daterangepicker').daterangepicker({
                format: 'yyyy-mm-dd',
                locale: {format: 'YYYY-MM-DD'},
            });
        });
    </script>
@endpush
