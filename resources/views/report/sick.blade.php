@extends('layouts.admin')
@section('page-title')
    {{__('Manage Report Sick')}}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
    <li class="breadcrumb-item">{{__('Report Sick')}}</li>
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
    </script>
@endpush



@section('action-btn')
    <div class="float-end">
        <a href="#" class="btn btn-sm btn-primary" onclick="saveAsPDF()" data-bs-toggle="tooltip" title="{{ __('Download') }}"
           data-original-title="{{ __('Download') }}">
            <span class="btn-inner--icon"><i class="ti ti-download"></i></span>
        </a>

    </div>
@endsection

@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class=" mt-2 " id="multiCollapseExample1">
                <div class="card">
                    <div class="card-body">
                        {{ Form::open(array('route' => array('report.sick'),'method'=>'get','id'=>'report_sick')) }}
                        <div class="row align-items-center justify-content-end">
                            <div class="col-auto">
                                <div class="row">
                                    <div class="col-auto">
                                        <div class="btn-box">
                                            {{Form::label('month',__('Month'),['class'=>'form-label'])}}
                                            {{Form::month('month',isset($_GET['month'])?$_GET['month']:date('Y-m'),array('class'=>'month-btn form-control'))}}
                                        </div>
                                    </div>
                                    <div class="col-auto" style = "width:200px;">
                                        <div class="btn-box">
                                            {{ Form::label('branch', __('Branch'),['class'=>'form-label']) }}
                                            {{ Form::select('branch', $branch,isset($_GET['branch'])?$_GET['branch']:'', array('class' => 'form-control select')) }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-auto">
                                <div class="row">
                                    <div class="col-auto mt-4">
                                        <a href="#" class="btn btn-sm btn-primary" onclick="document.getElementById('report_sick').submit(); return false;" data-bs-toggle="tooltip" title="{{__('Apply')}}" data-original-title="{{__('apply')}}">
                                            <span class="btn-inner--icon"><i class="ti ti-search"></i></span>
                                        </a>
                                        <a href="{{route('report.sick')}}" class="btn btn-sm btn-danger " data-bs-toggle="tooltip"  title="{{ __('Reset') }}" data-original-title="{{__('Reset')}}">
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
            <div class="col">
                <div class="card">
                    <div class="card-body table-border-style">
                        <div class="table-responsive py-4 attendance-table-responsive">
                            <table class="table">
                                <thead>
                                <tr>
                                    <th class="active">{{__('Name Employee')}}</th>
                                    <th class="active">{{__('Total Sick Days')}}</th>
                                    <th class="active">{{__('Sick Letter Details')}}</th>
                                </tr>
                                </thead>
                                <tbody>

                                    @foreach($employeesSick as $sick)
                                        <tr>
                                            <td>{{$sick['name']}}</td>
                                            <td>{{$sick['sick']}}</td>
                                            <td>
                                            <div class="m-view-btn badge bg-info p-2 px-3 rounded">{{$sick['total_sick_letter']}}
                                                <a href="#" class="text-white" data-url="{{ route('report.employee.sick',[$sick['id'],isset($_GET['month']) ? $_GET['month']:date('Y-m')]) }}" data-ajax-popup="true" data-title="{{__('Sick List Detail')}}" data-size="lg" data-bs-toggle="tooltip" title="{{__('View')}}" data-original-title="{{__('View')}}">{{__('View')}}</a>
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
    </div>

@endsection
