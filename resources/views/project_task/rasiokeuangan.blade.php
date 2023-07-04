@extends('layouts.admin')
@section('page-title')
    {{ucwords('Rasio Keuangan')}}
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
    <li class="breadcrumb-item">{{__('Rasio Keuangan')}}</li>
@endsection
@push('script-page')
@endpush
@section('action-btn')
@endsection

@section('content')
{{-- {{ Form::open(['route' => ['summary.materialitas'], 'method' => 'post']) }} --}}
    <input type="hidden" name="_token" id="token" value="{{ csrf_token() }}">
    <input type="hidden" name="materialitas_id" class = "form-control materialitas_id">
    <div class="row">
        <div class="col-sm-12">
            <div class=" mt-2 " id="multiCollapseExample1">
                <div class="card overflow-auto" style="overflow-x: scroll;">
                    <div class="card-body">
                    <div class="card-header"><h6 class="mb-0">{{__('Rasio Likuiditas')}}</h6></div>
                        <table class="table datatables">
                            <thead>
                                <tr>
                                    <th style="text-align: center;" scope="col">Rasio Keuangan</th>
                                    <th style="text-align: center;" scope="col">{{date(' Y', strtotime('-3 year', strtotime($project->book_year)))}}</th>
                                    <th style="text-align: center;" scope="col">{{date(' Y', strtotime('-2 year', strtotime($project->book_year)))}}</th>
                                    <th style="text-align: center;" scope="col">{{'Inhouse ' . $project->book_year}}</th>
                                    <th style="text-align: center;" scope="col">{{'Audited ' . $project->book_year}}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($rasio_likuiditas as $data)
                                    <tr>
                                        <td style="border: 1px solid black;">{{$data['akun']}}</td>
                                        <td style="border: 1px solid black; width:150px; text-align: right;">{{!empty($data['data_2020'])? ($data['data_2020'] != '0.00' ? $data['data_2020'] : '-') : '-'}}</td>
                                        <td style="border: 1px solid black; width:150px; text-align: right;">{{!empty($data['data_2021'])? ($data['data_2021'] != '0.00' ? $data['data_2021'] : '-') : '-'}}</td>
                                        <td style="border: 1px solid black; width:150px; text-align: right;">{{!empty($data['data_in_2022'])? ($data['data_in_2022'] != '0.00' ? $data['data_in_2022'] : '-') : '-'}}</td>
                                        <td style="border: 1px solid black; width:150px; text-align: right;">{{!empty($data['data_au_2022'])? ($data['data_au_2022'] != '0.00' ? $data['data_au_2022'] : '-') : '-'}}</td>
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
                    <div class="card-body">
                    <div class="card-header"><h6 class="mb-0">{{__('Rasio Profitabilitas')}}</h6></div>
                        <table class="table datatabless">
                            <thead>
                                <tr>
                                    <th style="text-align: center;" scope="col">Rasio Keuangan</th>
                                    <th style="text-align: center;" scope="col">{{date(' Y', strtotime('-3 year', strtotime($project->book_year)))}}</th>
                                    <th style="text-align: center;" scope="col">{{date(' Y', strtotime('-2 year', strtotime($project->book_year)))}}</th>
                                    <th style="text-align: center;" scope="col">{{'Inhouse ' . $project->book_year}}</th>
                                    <th style="text-align: center;" scope="col">{{'Audited ' . $project->book_year}}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($rasio_profitabilitas as $data)
                                    <tr>
                                        <td style="border: 1px solid black;">{{$data['akun']}}</td>
                                        <td style="border: 1px solid black; width:150px; text-align: right;">{{!empty($data['data_2020'])? ($data['data_2020'] != '0.00' ? $data['data_2020'] : '-') : '-'}}</td>
                                        <td style="border: 1px solid black; width:150px; text-align: right;">{{!empty($data['data_2021'])? ($data['data_2021'] != '0.00' ? $data['data_2021'] : '-') : '-'}}</td>
                                        <td style="border: 1px solid black; width:150px; text-align: right;">{{!empty($data['data_in_2022'])? ($data['data_in_2022'] != '0.00' ? $data['data_in_2022'] : '-') : '-'}}</td>
                                        <td style="border: 1px solid black; width:150px; text-align: right;">{{!empty($data['data_au_2022'])? ($data['data_au_2022'] != '0.00' ? $data['data_au_2022'] : '-') : '-'}}</td>
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
                    <div class="card-body">
                    <div class="card-header"><h6 class="mb-0">{{__('Rasio Utang')}}</h6></div>
                        <table class="table datatablesss">
                            <thead>
                                <tr>
                                    <th style="text-align: center;" scope="col">Rasio Keuangan</th>
                                    <th style="text-align: center;" scope="col">{{date(' Y', strtotime('-3 year', strtotime($project->book_year)))}}</th>
                                    <th style="text-align: center;" scope="col">{{date(' Y', strtotime('-2 year', strtotime($project->book_year)))}}</th>
                                    <th style="text-align: center;" scope="col">{{'Inhouse ' . $project->book_year}}</th>
                                    <th style="text-align: center;" scope="col">{{'Audited ' . $project->book_year}}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($rasio_utang as $data)
                                    <tr>
                                        <td style="border: 1px solid black;">{{$data['akun']}}</td>
                                        <td style="border: 1px solid black; width:150px; text-align: right;">{{!empty($data['data_2020'])? ($data['data_2020'] != '0.00' ? $data['data_2020'] : '-') : '-'}}</td>
                                        <td style="border: 1px solid black; width:150px; text-align: right;">{{!empty($data['data_2021'])? ($data['data_2021'] != '0.00' ? $data['data_2021'] : '-') : '-'}}</td>
                                        <td style="border: 1px solid black; width:150px; text-align: right;">{{!empty($data['data_in_2022'])? ($data['data_in_2022'] != '0.00' ? $data['data_in_2022'] : '-') : '-'}}</td>
                                        <td style="border: 1px solid black; width:150px; text-align: right;">{{!empty($data['data_au_2022'])? ($data['data_au_2022'] != '0.00' ? $data['data_au_2022'] : '-') : '-'}}</td>
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

