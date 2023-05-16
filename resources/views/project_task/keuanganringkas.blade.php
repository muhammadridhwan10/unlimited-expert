@extends('layouts.admin')
@section('page-title')
    {{ucwords('Data Keuangan Ringkas')}}
@endsection

@push('css-page')
    <link rel="stylesheet" href="{{asset('css/summernote/summernote-bs4.css')}}">
    <link rel="stylesheet" href="{{ asset('assets/css/plugins/dragula.min.css') }}" id="main-style-link">
@endpush
@push('script-page')
    <script src="{{asset('css/summernote/summernote-bs4.js')}}"></script>
    <script src="{{asset('js/jquery-ui.min.js')}}"></script>
    <script src="{{asset('js/jquery.repeater.min.js')}}"></script>


@endpush
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
    <li class="breadcrumb-item"><a href="{{route('projects.index')}}">{{__('Project')}}</a></li>
    <li class="breadcrumb-item"><a href="{{route('projects.show',$project->id)}}">    {{ucwords($project->project_name)}}</a></li>
    <li class="breadcrumb-item"><a href="{{route('projects.tasks.index',$project->id)}}">{{__('Task')}}</a></li>
    <li class="breadcrumb-item">{{__('Data Keuangan Ringkas')}}</li>
@endsection
@push('script-page')
@endpush
@section('action-btn')
@endsection

@section('content')
{{-- {{ Form::open(['route' => ['summary.materialitas'], 'method' => 'post']) }} --}}
    <input type="hidden" name="_token" id="token" value="{{ csrf_token() }}">
    <input type="hidden" name="materialitas_id" class = "form-control materialitas_id">
    {{-- <div class="row">
        <div class="col-sm-12">
            <div class=" mt-2 " id="multiCollapseExample1">
                <div class="card">
                <div class="card-header"><h6 class="mb-0">{{__('Data Keuangan Tahunan')}}</h6></div>
                    <div class="card-body">
                        <div class="row">
                                <div class="col-sm-3 col-md-4">
                                    <div class="form-group">
                                        <p style= 'text-align:center'><b>AKUN</b></p>
                                        @foreach(\App\Models\ProjectTask::$financial_statement as $k => $v)
                                            <div class="col-sm-3 col-md-12">
                                                <div class="form-group">
                                                    {{ Form::text('component', $v, ['class' => 'form-control','readonly'=>'true']) }}
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                                <div class="col-sm-3 col-md-2">
                                    <div class="form-group">
                                        <p style= 'text-align:center'><b>2020</b></p>
                                        @foreach($data_array_2020 as $key => $data_2020)
                                            <div class="col-sm-3 col-md-12">
                                                <div class="form-group">
                                                    {{ Form::text('2020', !empty(number_format($data_2020))? number_format($data_2020):'-', ['class' => 'form-control','readonly'=>'true']) }}
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                                <div class="col-sm-3 col-md-2">
                                    <div class="form-group">
                                        <p style= 'text-align:center'><b>2021</b></p>
                                        @foreach($data_array_2021 as $key => $data_2021)
                                            <div class="col-sm-3 col-md-12">
                                                <div class="form-group">
                                                    {{ Form::text('2021', !empty(number_format($data_2021))? number_format($data_2021):'-', ['class' => 'form-control','readonly'=>'true']) }}
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                                <div class="col-sm-3 col-md-2">
                                    <div class="form-group">
                                        <p style= 'text-align:center'><b>2022 Inhouse</b></p>
                                        @foreach($data_array_in_2022 as $key => $data_in_2022)
                                            <div class="col-sm-3 col-md-12">
                                                <div class="form-group">
                                                    {{ Form::text('inhouse2022', !empty(number_format($data_in_2022))? number_format($data_in_2022):'-', ['class' => 'form-control','readonly'=>'true']) }}
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                                <div class="col-sm-3 col-md-2">
                                    <div class="form-group">
                                        <p style= 'text-align:center'><b>2022 Audited</b></p>
                                        @foreach($data_array_au_2022 as $key => $data_au_2022)
                                            <div class="col-sm-3 col-md-12">
                                                <div class="form-group">
                                                    {{ Form::text('2022audited', !empty(number_format($data_au_2022))? number_format($data_au_2022):'-', ['class' => 'form-control','readonly'=>'true']) }}
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div> --}}
    <div class="row">
        <div class="col-sm-12">
            <div class=" mt-2 " id="multiCollapseExample1">
                <div class="card overflow-auto" style="overflow-x: scroll;">
                <div class="card-header"><h6 class="mb-0">{{__('Data Keuangan Tahunan')}}</h6></div>
                    <div class="card-body">
                        <table class="table datatable">
                            <thead>
                                <tr>
                                    <th style="text-align: center;" scope="col">Kode</th>
                                    <th style="text-align: center;" scope="col">Akun</th>
                                    <th style="text-align: center;" scope="col">{{'Unaudited' . date(' Y', strtotime('-3 year', strtotime($project->book_year)))}}</th>
                                    <th style="text-align: center;" scope="col">{{'Audited' . date(' Y', strtotime('-2 year', strtotime($project->book_year)))}}</th>
                                    <th style="text-align: center;" scope="col">{{'Inhouse ' . $project->book_year}}</th>
                                    <th style="text-align: center;" scope="col">{{'Audited ' . $project->book_year}}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($index as $indexs)
                                    <tr>
                                        <td style="border: 1px solid black;">{{$indexs['kode']}}</td>
                                        <td style="border: 1px solid black;">{{$indexs['akun']}}</td>
                                        <td style="border: 1px solid black; width:150px;">{{!empty(number_format($indexs['data_2020']))? number_format($indexs['data_2020']):'-'}}</td>
                                        <td style="border: 1px solid black; width:150px;">{{!empty(number_format($indexs['data_2021']))? number_format($indexs['data_2021']):'-'}}</td>
                                        <td style="border: 1px solid black; width:150px;">{{!empty(number_format($indexs['data_in_2022']))? number_format($indexs['data_in_2022']):'-'}}</td>
                                        <td style="border: 1px solid black; width:150px;">{{!empty(number_format($indexs['data_au_2022']))? number_format($indexs['data_au_2022']):'-'}}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>

                        <table class="table datatablessssss">
                            <thead>
                                <tr>
                                    <th style="text-align: center;" scope="col">Kode</th>
                                    <th style="text-align: center;" scope="col">Akun</th>
                                    <th style="text-align: center;" scope="col">{{'Unaudited' . date(' Y', strtotime('-3 year', strtotime($project->book_year)))}}</th>
                                    <th style="text-align: center;" scope="col">{{'Audited' . date(' Y', strtotime('-2 year', strtotime($project->book_year)))}}</th>
                                    <th style="text-align: center;" scope="col">{{'Inhouse ' . $project->book_year}}</th>
                                    <th style="text-align: center;" scope="col">{{'Audited ' . $project->book_year}}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($cn as $cns)
                                    <tr>
                                        <td style="border: 1px solid black;">{{$cns['kode']}}</td>
                                        <td style="border: 1px solid black;">{{$cns['akun']}}</td>
                                        <td style="border: 1px solid black; width:150px;">{{!empty(number_format($cns['data_2020']))? number_format($cns['data_2020']):'-'}}</td>
                                        <td style="border: 1px solid black; width:150px;">{{!empty(number_format($cns['data_2021']))? number_format($cns['data_2021']):'-'}}</td>
                                        <td style="border: 1px solid black; width:150px;">{{!empty(number_format($cns['data_in_2022']))? number_format($cns['data_in_2022']):'-'}}</td>
                                        <td style="border: 1px solid black; width:150px;">{{!empty(number_format($cns['data_au_2022']))? number_format($cns['data_au_2022']):'-'}}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>


                    </div>

                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-12">
            <div class=" mt-2 " id="multiCollapseExample1">
                <div class="card overflow-auto" style="overflow-x: scroll;">
                <div class="card-header"><h6 class="mb-0">{{__('Summary Data Keuangan Tahunan')}}</h6></div>
                    <div class="card-body">
                        <table class="table datatablesss">
                            <thead>
                                <tr>
                                    <th style="text-align: center;" scope="col">Akun</th>
                                    <th style="text-align: center;" scope="col">{{'Unaudited' . date(' Y', strtotime('-3 year', strtotime($project->book_year)))}}</th>
                                    <th style="text-align: center;" scope="col">{{'Audited' . date(' Y', strtotime('-2 year', strtotime($project->book_year)))}}</th>
                                    <th style="text-align: center;" scope="col">{{'Inhouse ' . $project->book_year}}</th>
                                    <th style="text-align: center;" scope="col">{{'Audited ' . $project->book_year}}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($result as $results)
                                    <tr>
                                        <td style="border: 1px solid black; background-color:#008b8b; color:white; font-weight: bold; width:150px;">{{$results['akun']}}</td>
                                        <td style="border: 1px solid black; background-color:#008b8b; color:white; font-weight: bold; width:150px;">{{!empty(number_format($results['2020']))? number_format($results['2020']):'-'}}</td>
                                        <td style="border: 1px solid black; background-color:#008b8b; color:white; font-weight: bold; width:150px;">{{!empty(number_format($results['2021']))? number_format($results['2021']):'-'}}</td>
                                        <td style="border: 1px solid black; background-color:#008b8b; color:white; font-weight: bold; width:150px;">{{!empty(number_format($results['inhouse2022']))? number_format($results['inhouse2022']):'-'}}</td>
                                        <td style="border: 1px solid black; background-color:#008b8b; color:white; font-weight: bold; width:150px;">{{!empty(number_format($results['audited2022']))? number_format($results['audited2022']):'-'}}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
        </div>
    </div>
    {{-- <div class="row">
        <div class="col-sm-12">
            <div class=" mt-2 " id="multiCollapseExample1">
                <div class="card overflow-auto" style="overflow-x: scroll;">
                <div class="card-header"><h6 class="mb-0">{{__('Summary CN Tahunan')}}</h6></div>
                    <div class="card-body">
                        <table class="table datatable">
                            <thead>
                                <tr>
                                    <th scope="col">Akun</th>
                                    <th scope="col">2020</th>
                                    <th scope="col">2021</th>
                                    <th scope="col">2022 Inhouse</th>
                                    <th scope="col">2022 audited</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($cn as $cns)
                                    <tr>
                                        <td style="border: 1px solid black;">{{$cns['akun']}}</td>
                                        <td style="border: 1px solid black;">{{!empty(number_format($cns['data_2020']))? number_format($cns['data_2020']):'-'}}</td>
                                        <td style="border: 1px solid black;">{{!empty(number_format($cns['data_2021']))? number_format($cns['data_2021']):'-'}}</td>
                                        <td style="border: 1px solid black;">{{!empty(number_format($cns['data_in_2022']))? number_format($cns['data_in_2022']):'-'}}</td>
                                        <td style="border: 1px solid black;">{{!empty(number_format($cns['data_au_2022']))? number_format($cns['data_au_2022']):'-'}}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
        </div>
    </div> --}}
    <div class="row">
        <div class="col-sm-12">
            <div class=" mt-2 " id="multiCollapseExample1">
                <div class="card overflow-auto" style="overflow-x: scroll;">
                <div class="card-header"><h6 class="mb-0">{{__('Data Keuangan Bulanan')}}</h6></div>
                    <div class="card-body">
                        <table class="table datatables">
                            <thead>
                                <tr>
                                    <th style="text-align: center;" scope="col">Kode</th>
                                    <th style="text-align: center;" scope="col">Akun</th>
                                    <th style="text-align: center;" scope="col">Januari</th>
                                    <th style="text-align: center;" scope="col">Februari</th>
                                    <th style="text-align: center;" scope="col">Maret</th>
                                    <th style="text-align: center;" scope="col">April</th>
                                    <th style="text-align: center;" scope="col">Mei</th>
                                    <th style="text-align: center;" scope="col">Juni</th>
                                    <th style="text-align: center;" scope="col">Juli</th>
                                    <th style="text-align: center;" scope="col">Agustus</th>
                                    <th style="text-align: center;" scope="col">September</th>
                                    <th style="text-align: center;" scope="col">Oktober</th>
                                    <th style="text-align: center;" scope="col">November</th>
                                    <th style="text-align: center;" scope="col">Desember</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($index as $indexs)
                                    <tr>
                                        <td style="border: 1px solid black; width:150px;">{{$indexs['kode']}}</td>
                                        <td style="border: 1px solid black; width:150px;">{{$indexs['akun']}}</td>
                                        <td style="border: 1px solid black; width:150px;">{{!empty(number_format($indexs['januari']))? number_format($indexs['januari']):'-'}}</td>
                                        <td style="border: 1px solid black; width:150px;">{{!empty(number_format($indexs['februari']))? number_format($indexs['februari']):'-'}}</td>
                                        <td style="border: 1px solid black; width:150px;">{{!empty(number_format($indexs['maret']))? number_format($indexs['maret']):'-'}}</td>
                                        <td style="border: 1px solid black; width:150px;">{{!empty(number_format($indexs['april']))? number_format($indexs['april']):'-'}}</td>
                                        <td style="border: 1px solid black; width:150px;">{{!empty(number_format($indexs['mei']))? number_format($indexs['mei']):'-'}}</td>
                                        <td style="border: 1px solid black; width:150px;">{{!empty(number_format($indexs['juni']))? number_format($indexs['juni']):'-'}}</td>
                                        <td style="border: 1px solid black; width:150px;">{{!empty(number_format($indexs['juli']))? number_format($indexs['juli']):'-'}}</td>
                                        <td style="border: 1px solid black; width:150px;">{{!empty(number_format($indexs['agustus']))? number_format($indexs['agustus']):'-'}}</td>
                                        <td style="border: 1px solid black; width:150px;">{{!empty(number_format($indexs['september']))? number_format($indexs['september']):'-'}}</td>
                                        <td style="border: 1px solid black; width:150px;">{{!empty(number_format($indexs['oktober']))? number_format($indexs['oktober']):'-'}}</td>
                                        <td style="border: 1px solid black; width:150px;">{{!empty(number_format($indexs['november']))? number_format($indexs['november']):'-'}}</td>
                                        <td style="border: 1px solid black; width:150px;">{{!empty(number_format($indexs['desember']))? number_format($indexs['desember']):'-'}}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>

                        <table class="table datatablesssssss">
                            <thead>
                                <tr>
                                    <th style="text-align: center;" scope="col">Akun</th>
                                    <th style="text-align: center;" scope="col">Januari</th>
                                    <th style="text-align: center;" scope="col">Februari</th>
                                    <th style="text-align: center;" scope="col">Maret</th>
                                    <th style="text-align: center;" scope="col">April</th>
                                    <th style="text-align: center;" scope="col">Mei</th>
                                    <th style="text-align: center;" scope="col">Juni</th>
                                    <th style="text-align: center;" scope="col">Juli</th>
                                    <th style="text-align: center;" scope="col">Agustus</th>
                                    <th style="text-align: center;" scope="col">September</th>
                                    <th style="text-align: center;" scope="col">Oktober</th>
                                    <th style="text-align: center;" scope="col">November</th>
                                    <th style="text-align: center;" scope="col">Desember</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($cn as $cns)
                                    <tr>
                                        <td style="border: 1px solid black; width:150px;">{{$cns['akun']}}</td>
                                        <td style="border: 1px solid black; width:150px;">{{!empty(number_format($cns['januari']))? number_format($cns['januari']):'-'}}</td>
                                        <td style="border: 1px solid black; width:150px;">{{!empty(number_format($cns['februari']))? number_format($cns['februari']):'-'}}</td>
                                        <td style="border: 1px solid black; width:150px;">{{!empty(number_format($cns['maret']))? number_format($cns['maret']):'-'}}</td>
                                        <td style="border: 1px solid black; width:150px;">{{!empty(number_format($cns['april']))? number_format($cns['april']):'-'}}</td>
                                        <td style="border: 1px solid black; width:150px;">{{!empty(number_format($cns['mei']))? number_format($cns['mei']):'-'}}</td>
                                        <td style="border: 1px solid black; width:150px;">{{!empty(number_format($cns['juni']))? number_format($cns['juni']):'-'}}</td>
                                        <td style="border: 1px solid black; width:150px;">{{!empty(number_format($cns['juli']))? number_format($cns['juli']):'-'}}</td>
                                        <td style="border: 1px solid black; width:150px;">{{!empty(number_format($cns['agustus']))? number_format($cns['agustus']):'-'}}</td>
                                        <td style="border: 1px solid black; width:150px;">{{!empty(number_format($cns['september']))? number_format($cns['september']):'-'}}</td>
                                        <td style="border: 1px solid black; width:150px;">{{!empty(number_format($cns['oktober']))? number_format($cns['oktober']):'-'}}</td>
                                        <td style="border: 1px solid black; width:150px;">{{!empty(number_format($cns['november']))? number_format($cns['november']):'-'}}</td>
                                        <td style="border: 1px solid black; width:150px;">{{!empty(number_format($cns['desember']))? number_format($cns['desember']):'-'}}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        
                    </div>

                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-12">
            <div class=" mt-2 " id="multiCollapseExample1">
                <div class="card overflow-auto" style="overflow-x: scroll;">
                <div class="card-header"><h6 class="mb-0">{{__('Summary Data Keuangan Bulanan')}}</h6></div>
                    <div class="card-body">
                        <table class="table datatabless">
                            <thead>
                                <tr>
                                    <th  style="text-align: center;" scope="col">Akun</th>
                                    <th  style="text-align: center;" scope="col">Januari</th>
                                    <th  style="text-align: center;" scope="col">Februari</th>
                                    <th  style="text-align: center;" scope="col">Maret</th>
                                    <th  style="text-align: center;" scope="col">April</th>
                                    <th  style="text-align: center;" scope="col">Mei</th>
                                    <th  style="text-align: center;" scope="col">Juni</th>
                                    <th  style="text-align: center;" scope="col">Juli</th>
                                    <th  style="text-align: center;" scope="col">Agustus</th>
                                    <th  style="text-align: center;" scope="col">September</th>
                                    <th  style="text-align: center;" scope="col">Oktober</th>
                                    <th  style="text-align: center;" scope="col">November</th>
                                    <th  style="text-align: center;" scope="col">Desember</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($result as $results)
                                    <tr>
                                        <td style="border: 1px solid black; background-color:#FFFFE0; color:#701F28; font-weight: bold; width:150px;">{{$results['akun']}}</td>
                                        <td style="border: 1px solid black; background-color:#FFFFE0; color:#701F28; font-weight: bold; width:150px;">{{!empty(number_format($results['januari']))? number_format($results['januari']):'-'}}</td>
                                        <td style="border: 1px solid black; background-color:#FFFFE0; color:#701F28; font-weight: bold; width:150px;">{{!empty(number_format($results['februari']))? number_format($results['februari']):'-'}}</td>
                                        <td style="border: 1px solid black; background-color:#FFFFE0; color:#701F28; font-weight: bold; width:150px;">{{!empty(number_format($results['maret']))? number_format($results['maret']):'-'}}</td>
                                        <td style="border: 1px solid black; background-color:#FFFFE0; color:#701F28; font-weight: bold; width:150px;">{{!empty(number_format($results['april']))? number_format($results['april']):'-'}}</td>
                                        <td style="border: 1px solid black; background-color:#FFFFE0; color:#701F28; font-weight: bold; width:150px;">{{!empty(number_format($results['mei']))? number_format($results['mei']):'-'}}</td>
                                        <td style="border: 1px solid black; background-color:#FFFFE0; color:#701F28; font-weight: bold; width:150px;">{{!empty(number_format($results['juni']))? number_format($results['juni']):'-'}}</td>
                                        <td style="border: 1px solid black; background-color:#FFFFE0; color:#701F28; font-weight: bold; width:150px;">{{!empty(number_format($results['juli']))? number_format($results['juli']):'-'}}</td>
                                        <td style="border: 1px solid black; background-color:#FFFFE0; color:#701F28; font-weight: bold; width:150px;">{{!empty(number_format($results['agustus']))? number_format($results['agustus']):'-'}}</td>
                                        <td style="border: 1px solid black; background-color:#FFFFE0; color:#701F28; font-weight: bold; width:150px;">{{!empty(number_format($results['september']))? number_format($results['september']):'-'}}</td>
                                        <td style="border: 1px solid black; background-color:#FFFFE0; color:#701F28; font-weight: bold; width:150px;">{{!empty(number_format($results['oktober']))? number_format($results['oktober']):'-'}}</td>
                                        <td style="border: 1px solid black; background-color:#FFFFE0; color:#701F28; font-weight: bold; width:150px;">{{!empty(number_format($results['november']))? number_format($results['november']):'-'}}</td>
                                        <td style="border: 1px solid black; background-color:#FFFFE0; color:#701F28; font-weight: bold; width:150px;">{{!empty(number_format($results['desember']))? number_format($results['desember']):'-'}}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
        </div>
    </div>
    {{-- <div class="row">
        <div class="col-sm-12">
            <div class=" mt-2 " id="multiCollapseExample1">
                <div class="card overflow-auto" style="overflow-x: scroll;">
                <div class="card-header"><h6 class="mb-0">{{__('Summary CN Bulanan')}}</h6></div>
                    <div class="card-body">
                        <table class="table datatable">
                            <thead>
                                <tr>
                                    <th scope="col">Akun</th>
                                    <th scope="col">Januari</th>
                                    <th scope="col">Februari</th>
                                    <th scope="col">Maret</th>
                                    <th scope="col">April</th>
                                    <th scope="col">Mei</th>
                                    <th scope="col">Juni</th>
                                    <th scope="col">Juli</th>
                                    <th scope="col">Agustus</th>
                                    <th scope="col">September</th>
                                    <th scope="col">Oktober</th>
                                    <th scope="col">November</th>
                                    <th scope="col">Desember</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($cn as $cns)
                                    <tr>
                                        <td style="border: 1px solid black;">{{$cns['akun']}}</td>
                                        <td style="border: 1px solid black;">{{!empty(number_format($cns['januari']))? number_format($cns['januari']):'-'}}</td>
                                        <td style="border: 1px solid black;">{{!empty(number_format($cns['februari']))? number_format($cns['februari']):'-'}}</td>
                                        <td style="border: 1px solid black;">{{!empty(number_format($cns['maret']))? number_format($cns['maret']):'-'}}</td>
                                        <td style="border: 1px solid black;">{{!empty(number_format($cns['april']))? number_format($cns['april']):'-'}}</td>
                                        <td style="border: 1px solid black;">{{!empty(number_format($cns['mei']))? number_format($cns['mei']):'-'}}</td>
                                        <td style="border: 1px solid black;">{{!empty(number_format($cns['juni']))? number_format($cns['juni']):'-'}}</td>
                                        <td style="border: 1px solid black;">{{!empty(number_format($cns['juli']))? number_format($cns['juli']):'-'}}</td>
                                        <td style="border: 1px solid black;">{{!empty(number_format($cns['agustus']))? number_format($cns['agustus']):'-'}}</td>
                                        <td style="border: 1px solid black;">{{!empty(number_format($cns['september']))? number_format($cns['september']):'-'}}</td>
                                        <td style="border: 1px solid black;">{{!empty(number_format($cns['oktober']))? number_format($cns['oktober']):'-'}}</td>
                                        <td style="border: 1px solid black;">{{!empty(number_format($cns['november']))? number_format($cns['november']):'-'}}</td>
                                        <td style="border: 1px solid black;">{{!empty(number_format($cns['desember']))? number_format($cns['desember']):'-'}}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
        </div>
    </div> --}}
    <div class="row">
        <div class="col-sm-12">
            <div class=" mt-2 " id="multiCollapseExample1">
                <div class="card overflow-auto" style="overflow-x: scroll;">
                <div class="card-header"><h6 class="mb-0">{{__('Perbandingan Data Antar Periode')}}</h6></div>
                    <div class="card-body">
                        <table class="table datatablessss">
                            <thead>
                                <tr>
                                    <th style="text-align: center;" scope="col">Akun</th>
                                    <th style="text-align: center;" scope="col">Kenaikan / Penurunan 2021</th>
                                    <th style="text-align: center;" scope="col">M / TM</th>
                                    <th style="text-align: center;" scope="col">%</th>
                                    <th style="text-align: center;" scope="col">Kenaikan / Penurunan 2022</th>
                                    <th style="text-align: center;" scope="col">M / TM</th>
                                    <th style="text-align: center;" scope="col">%</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($index as $indexs)
                                    <tr>
                                        <td style="border: 1px solid black; width:150px;">{{$indexs['akun']}}</td>
                                        <td style="border: 1px solid black; width:150px;">{{!empty(number_format($indexs['kenaikan_2021']))? number_format($indexs['kenaikan_2021']):'-'}}</td>
                                        <td style="border: 1px solid black; width:150px; background-color:#ffcccb; font-weight: bold; text-align: center;">{{!empty($indexs['M/TM'])? $indexs['M/TM']:'-'}}</td>
                                        <td style="border: 1px solid black; width:150px;">
                                        <?php
                                            $persen = number_format($indexs['persen_kenaikan'], 2);
                                            $persen = ($persen == '0.00') ? '-' : $persen;
                                        ?>
                                        {{$persen}}
                                        </td>
                                        <td style="border: 1px solid black; width:150px;">{{!empty($indexs['kenaikan_2022']) ? (($indexs['kenaikan_2022'] < 0) ? '('.number_format(abs($indexs['kenaikan_2022'])).')' : number_format($indexs['kenaikan_2022'])) : '-'}}</td>
                                        <td style="border: 1px solid black; width:150px; background-color:#ffcccb; font-weight: bold; text-align: center;">{{!empty($indexs['M/TM_2022'])? $indexs['M/TM_2022']:'-'}}</td>
                                        <td style="border: 1px solid black; width:150px;">
                                        <?php
                                            $persen = number_format($indexs['persen_kenaikan_2022'], 2);
                                            $persen = ($persen == '0.00') ? '-' : $persen;
                                        ?>
                                        {{$persen}}
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
    <div class="row">
        <div class="col-sm-12">
            <div class=" mt-2 " id="multiCollapseExample1">
                <div class="card overflow-auto" style="overflow-x: scroll;">
                <div class="card-header"><h6 class="mb-0">{{__('Summary Perbandingan Data Antar Periode')}}</h6></div>
                    <div class="card-body">
                        <table class="table datatablesssss">
                            <thead>
                                <tr>
                                    <th style="text-align: center;" scope="col">Akun</th>
                                    <th style="text-align: center;" scope="col">2021 (%)</th>
                                    <th style="text-align: center;" scope="col">2022 (%)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($result as $results)
                                    <tr>
                                        <td style="border: 1px solid black; background-color:#008b8b; color:white; font-weight: bold;">{{$results['akun']}}</td>
                                        <td style="border: 1px solid black;">
                                        <?php
                                            $persen = number_format($results['increase_2021'], 2);
                                            $persen = ($persen == '0.00') ? '-' : $persen;
                                        ?>
                                        {{$persen}}
                                        </td>
                                        <td style="border: 1px solid black;">
                                        <?php
                                            $persen_2022 = number_format($results['increase_2022'], 2);
                                            $persen_2022 = ($persen_2022 == '0.00') ? '-' : $persen_2022;
                                        ?>
                                        {{$persen_2022}}
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
{{-- {{ Form::close() }} --}}
@endsection

