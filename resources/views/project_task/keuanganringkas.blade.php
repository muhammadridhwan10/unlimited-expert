@extends('layouts.admin')
@section('page-title')
    {{ucwords('Perbandingan Data Antar Periode')}}
@endsection

@push('css-page')
    <link rel="stylesheet" href="{{asset('css/summernote/summernote-bs4.css')}}">
    <link rel="stylesheet" href="{{ asset('assets/css/plugins/dragula.min.css') }}" id="main-style-link">
@endpush
@push('script-page')
    <script src="{{asset('css/summernote/summernote-bs4.js')}}"></script>
    <script src="{{asset('js/jquery-ui.min.js')}}"></script>
    <script src="{{asset('js/jquery.repeater.min.js')}}"></script>
    <script>
        window.addEventListener('DOMContentLoaded', (event) => {
            let answerText = document.querySelector('.formatted-text');
            let answerTextContent = answerText.textContent.trim();
            answerText.textContent = '';

            let span = document.createElement('span');
            span.classList.add('text');
            answerText.appendChild(span);

            let currentCharIndex = 0;
            let typingTimer = setInterval(function() {
                if (currentCharIndex < answerTextContent.length) {
                    span.textContent += answerTextContent.charAt(currentCharIndex);
                    currentCharIndex++;
                } else {
                    clearInterval(typingTimer);
                }
            }, 20);
        });
    </script>


@endpush
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
    <li class="breadcrumb-item"><a href="{{route('projects.index')}}">{{__('Project')}}</a></li>
    <li class="breadcrumb-item"><a href="{{route('projects.show',$project->id)}}">    {{ucwords($project->project_name)}}</a></li>
    <li class="breadcrumb-item"><a href="{{route('projects.tasks.index',$project->id)}}">{{__('Task')}}</a></li>
    <li class="breadcrumb-item">{{__($task->name)}}</li>
@endsection
@push('script-page')
@endpush
@push('css-page')
    <style>
        .formatted-text {
            white-space: pre-wrap;
            font-family: 'Roboto', sans-serif;
        }

        .typing-animation .text:after {
            content: '|';
            animation: blink-caret 0.75s infinite;
        }

        @keyframes typing {
            from { width: 0; }
            to { width: 100%; }
        }

        @keyframes blink-caret {
            from, to { opacity: 0; }
            50% { opacity: 1; }
        }

        @keyframes typing-text {
            0% { width: 0; }
            100% { width: 100%; }
        }
    </style>
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
    {{-- <div class="row">
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
                                    <th style="text-align: center;" scope="col">{{date(' Y', strtotime('-3 year', strtotime($project->book_year)))}}</th>
                                    <th style="text-align: center;" scope="col">{{date(' Y', strtotime('-2 year', strtotime($project->book_year)))}}</th>
                                    <th style="text-align: center;" scope="col">{{'Inhouse ' . $project->book_year}}</th>
                                    <th style="text-align: center;" scope="col">{{'Audited ' . $project->book_year}}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($index as $indexs)
                                    <tr>
                                        <td style="border: 1px solid black;">{{$indexs['kode']}}</td>
                                        <td style="border: 1px solid black;">{{$indexs['akun']}}</td>
                                        <td style="border: 1px solid black; width:150px; text-align: right;">{{ !empty(number_format($indexs['data_2020']))? (number_format($indexs['data_2020']) < 0 ? '('.number_format(abs($indexs['data_2020'])).')' : number_format($indexs['data_2020'])): '-'}}</td>
                                        <td style="border: 1px solid black; width:150px; text-align: right;">{{ !empty(number_format($indexs['data_2021']))? (number_format($indexs['data_2021']) < 0 ? '('.number_format(abs($indexs['data_2021'])).')' : number_format($indexs['data_2021'])): '-'}}</td>
                                        <td style="border: 1px solid black; width:150px; text-align: right;">{{ !empty(number_format($indexs['data_in_2022']))? (number_format($indexs['data_in_2022']) < 0 ? '('.number_format(abs($indexs['data_in_2022'])).')' : number_format($indexs['data_in_2022'])): '-'}}</td>
                                        <td style="border: 1px solid black; width:150px; text-align: right;">{{ !empty(number_format($indexs['data_au_2022']))? (number_format($indexs['data_au_2022']) < 0 ? '('.number_format(abs($indexs['data_au_2022'])).')' : number_format($indexs['data_au_2022'])): '-'}}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>

                        <table class="table datatablessssss">
                            <thead>
                                <tr>
                                    <th style="text-align: center;" scope="col">Kode</th>
                                    <th style="text-align: center;" scope="col">Akun</th>
                                    <th style="text-align: center;" scope="col">{{date(' Y', strtotime('-3 year', strtotime($project->book_year)))}}</th>
                                    <th style="text-align: center;" scope="col">{{date(' Y', strtotime('-2 year', strtotime($project->book_year)))}}</th>
                                    <th style="text-align: center;" scope="col">{{'Inhouse ' . $project->book_year}}</th>
                                    <th style="text-align: center;" scope="col">{{'Audited ' . $project->book_year}}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($cn as $cns)
                                    <tr>
                                        <td style="border: 1px solid black;">{{$cns['kode']}}</td>
                                        <td style="border: 1px solid black;">{{$cns['akun']}}</td>
                                        <td style="border: 1px solid black; width:150px; text-align: right;">{{ !empty(number_format($cns['data_2020']))? (number_format($cns['data_2020']) < 0 ? '('.number_format(abs($cns['data_2020'])).')' : number_format($cns['data_2020'])): '-'}}</td>
                                        <td style="border: 1px solid black; width:150px; text-align: right;">{{ !empty(number_format($cns['data_2021']))? (number_format($cns['data_2021']) < 0 ? '('.number_format(abs($cns['data_2021'])).')' : number_format($cns['data_2021'])): '-'}}</td>
                                        <td style="border: 1px solid black; width:150px; text-align: right;">{{ !empty(number_format($cns['data_in_2022']))? (number_format($cns['data_in_2022']) < 0 ? '('.number_format(abs($cns['data_in_2022'])).')' : number_format($cns['data_in_2022'])): '-'}}</td>
                                        <td style="border: 1px solid black; width:150px; text-align: right;">{{ !empty(number_format($cns['data_au_2022']))? (number_format($cns['data_au_2022']) < 0 ? '('.number_format(abs($cns['data_au_2022'])).')' : number_format($cns['data_au_2022'])): '-'}}</td>
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
                                    <th style="text-align: center;" scope="col">{{date(' Y', strtotime('-3 year', strtotime($project->book_year)))}}</th>
                                    <th style="text-align: center;" scope="col">{{date(' Y', strtotime('-2 year', strtotime($project->book_year)))}}</th>
                                    <th style="text-align: center;" scope="col">{{'Inhouse ' . $project->book_year}}</th>
                                    <th style="text-align: center;" scope="col">{{'Audited ' . $project->book_year}}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($result as $results)
                                    <tr>
                                        <td style="border: 1px solid black; background-color:#008b8b; color:white; font-weight: bold; width:150px;">{{$results['akun']}}</td>
                                        <td style="border: 1px solid black; background-color:#008b8b; color:white; font-weight: bold; width:150px; text-align: right;">{{ !empty(number_format($results['2020']))? (number_format($results['2020']) < 0 ? '('.number_format(abs($results['2020'])).')' : number_format($results['2020'])): '-'}}</td>
                                        <td style="border: 1px solid black; background-color:#008b8b; color:white; font-weight: bold; width:150px; text-align: right;">{{ !empty(number_format($results['2021']))? (number_format($results['2021']) < 0 ? '('.number_format(abs($results['2021'])).')' : number_format($results['2021'])): '-'}}</td>
                                        <td style="border: 1px solid black; background-color:#008b8b; color:white; font-weight: bold; width:150px; text-align: right;">{{ !empty(number_format($results['inhouse2022']))? (number_format($results['inhouse2022']) < 0 ? '('.number_format(abs($results['inhouse2022'])).')' : number_format($results['inhouse2022'])): '-'}}</td>
                                        <td style="border: 1px solid black; background-color:#008b8b; color:white; font-weight: bold; width:150px; text-align: right;">{{ !empty(number_format($results['audited2022']))? (number_format($results['audited2022']) < 0 ? '('.number_format(abs($results['audited2022'])).')' : number_format($results['audited2022'])): '-'}}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
        </div>
    </div> --}}
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
    {{-- <div class="row">
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
                                        <td style="border: 1px solid black; width:150px; text-align: right;">{{ !empty(number_format($indexs['januari']))? (number_format($indexs['januari']) < 0 ? '('.number_format(abs($indexs['januari'])).')' : number_format($indexs['januari'])): '-'}}</td>
                                        <td style="border: 1px solid black; width:150px; text-align: right;">{{ !empty(number_format($indexs['februari']))? (number_format($indexs['februari']) < 0 ? '('.number_format(abs($indexs['februari'])).')' : number_format($indexs['februari'])): '-'}}</td>
                                        <td style="border: 1px solid black; width:150px; text-align: right;">{{ !empty(number_format($indexs['maret']))? (number_format($indexs['maret']) < 0 ? '('.number_format(abs($indexs['maret'])).')' : number_format($indexs['maret'])): '-'}}</td>
                                        <td style="border: 1px solid black; width:150px; text-align: right;">{{ !empty(number_format($indexs['april']))? (number_format($indexs['april']) < 0 ? '('.number_format(abs($indexs['april'])).')' : number_format($indexs['april'])): '-'}}</td>
                                        <td style="border: 1px solid black; width:150px; text-align: right;">{{ !empty(number_format($indexs['mei']))? (number_format($indexs['mei']) < 0 ? '('.number_format(abs($indexs['mei'])).')' : number_format($indexs['mei'])): '-'}}</td>
                                        <td style="border: 1px solid black; width:150px; text-align: right;">{{ !empty(number_format($indexs['juni']))? (number_format($indexs['juni']) < 0 ? '('.number_format(abs($indexs['juni'])).')' : number_format($indexs['juni'])): '-'}}</td>
                                        <td style="border: 1px solid black; width:150px; text-align: right;">{{ !empty(number_format($indexs['juli']))? (number_format($indexs['juli']) < 0 ? '('.number_format(abs($indexs['juli'])).')' : number_format($indexs['juli'])): '-'}}</td>
                                        <td style="border: 1px solid black; width:150px; text-align: right;">{{ !empty(number_format($indexs['agustus']))? (number_format($indexs['agustus']) < 0 ? '('.number_format(abs($indexs['agustus'])).')' : number_format($indexs['agustus'])): '-'}}</td>
                                        <td style="border: 1px solid black; width:150px; text-align: right;">{{ !empty(number_format($indexs['september']))? (number_format($indexs['september']) < 0 ? '('.number_format(abs($indexs['september'])).')' : number_format($indexs['september'])): '-'}}</td>
                                        <td style="border: 1px solid black; width:150px; text-align: right;">{{ !empty(number_format($indexs['oktober']))? (number_format($indexs['oktober']) < 0 ? '('.number_format(abs($indexs['oktober'])).')' : number_format($indexs['oktober'])): '-'}}</td>
                                        <td style="border: 1px solid black; width:150px; text-align: right;">{{ !empty(number_format($indexs['november']))? (number_format($indexs['november']) < 0 ? '('.number_format(abs($indexs['november'])).')' : number_format($indexs['november'])): '-'}}</td>
                                        <td style="border: 1px solid black; width:150px; text-align: right;">{{ !empty(number_format($indexs['desember']))? (number_format($indexs['desember']) < 0 ? '('.number_format(abs($indexs['desember'])).')' : number_format($indexs['desember'])): '-'}}</td>
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
                                        <td style="border: 1px solid black; width:150px; text-align: right;">{{ !empty(number_format($cns['januari']))? (number_format($cns['januari']) < 0 ? '('.number_format(abs($cns['januari'])).')' : number_format($cns['januari'])): '-'}}</td>
                                        <td style="border: 1px solid black; width:150px; text-align: right;">{{ !empty(number_format($cns['februari']))? (number_format($cns['februari']) < 0 ? '('.number_format(abs($cns['februari'])).')' : number_format($cns['februari'])): '-'}}</td>
                                        <td style="border: 1px solid black; width:150px; text-align: right;">{{ !empty(number_format($cns['maret']))? (number_format($cns['maret']) < 0 ? '('.number_format(abs($cns['maret'])).')' : number_format($cns['maret'])): '-'}}</td>
                                        <td style="border: 1px solid black; width:150px; text-align: right;">{{ !empty(number_format($cns['april']))? (number_format($cns['april']) < 0 ? '('.number_format(abs($cns['april'])).')' : number_format($cns['april'])): '-'}}</td>
                                        <td style="border: 1px solid black; width:150px; text-align: right;">{{ !empty(number_format($cns['mei']))? (number_format($cns['mei']) < 0 ? '('.number_format(abs($cns['mei'])).')' : number_format($cns['mei'])): '-'}}</td>
                                        <td style="border: 1px solid black; width:150px; text-align: right;">{{ !empty(number_format($cns['juni']))? (number_format($cns['juni']) < 0 ? '('.number_format(abs($cns['juni'])).')' : number_format($cns['juni'])): '-'}}</td>
                                        <td style="border: 1px solid black; width:150px; text-align: right;">{{ !empty(number_format($cns['juli']))? (number_format($cns['juli']) < 0 ? '('.number_format(abs($cns['juli'])).')' : number_format($cns['juli'])): '-'}}</td>
                                        <td style="border: 1px solid black; width:150px; text-align: right;">{{ !empty(number_format($cns['agustus']))? (number_format($cns['agustus']) < 0 ? '('.number_format(abs($cns['agustus'])).')' : number_format($cns['agustus'])): '-'}}</td>
                                        <td style="border: 1px solid black; width:150px; text-align: right;">{{ !empty(number_format($cns['september']))? (number_format($cns['september']) < 0 ? '('.number_format(abs($cns['september'])).')' : number_format($cns['september'])): '-'}}</td>
                                        <td style="border: 1px solid black; width:150px; text-align: right;">{{ !empty(number_format($cns['oktober']))? (number_format($cns['oktober']) < 0 ? '('.number_format(abs($cns['oktober'])).')' : number_format($cns['oktober'])): '-'}}</td>
                                        <td style="border: 1px solid black; width:150px; text-align: right;">{{ !empty(number_format($cns['november']))? (number_format($cns['november']) < 0 ? '('.number_format(abs($cns['november'])).')' : number_format($cns['november'])): '-'}}</td>
                                        <td style="border: 1px solid black; width:150px; text-align: right;">{{ !empty(number_format($cns['desember']))? (number_format($cns['desember']) < 0 ? '('.number_format(abs($cns['desember'])).')' : number_format($cns['desember'])): '-'}}</td>
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
                                        <td style="border: 1px solid black; background-color:#FFFFE0; color:#701F28; font-weight: bold; width:150px; text-align: right;">{{ !empty(number_format($results['januari']))? (number_format($results['januari']) < 0 ? '('.number_format(abs($results['januari'])).')' : number_format($results['januari'])): '-'}}</td>
                                        <td style="border: 1px solid black; background-color:#FFFFE0; color:#701F28; font-weight: bold; width:150px; text-align: right;">{{ !empty(number_format($results['februari']))? (number_format($results['februari']) < 0 ? '('.number_format(abs($results['februari'])).')' : number_format($results['februari'])): '-'}}</td>
                                        <td style="border: 1px solid black; background-color:#FFFFE0; color:#701F28; font-weight: bold; width:150px; text-align: right;">{{ !empty(number_format($results['maret']))? (number_format($results['maret']) < 0 ? '('.number_format(abs($results['maret'])).')' : number_format($results['maret'])): '-'}}</td>
                                        <td style="border: 1px solid black; background-color:#FFFFE0; color:#701F28; font-weight: bold; width:150px; text-align: right;">{{ !empty(number_format($results['april']))? (number_format($results['april']) < 0 ? '('.number_format(abs($results['april'])).')' : number_format($results['april'])): '-'}}</td>
                                        <td style="border: 1px solid black; background-color:#FFFFE0; color:#701F28; font-weight: bold; width:150px; text-align: right;">{{ !empty(number_format($results['mei']))? (number_format($results['mei']) < 0 ? '('.number_format(abs($results['mei'])).')' : number_format($results['mei'])): '-'}}</td>
                                        <td style="border: 1px solid black; background-color:#FFFFE0; color:#701F28; font-weight: bold; width:150px; text-align: right;">{{ !empty(number_format($results['juni']))? (number_format($results['juni']) < 0 ? '('.number_format(abs($results['juni'])).')' : number_format($results['juni'])): '-'}}</td>
                                        <td style="border: 1px solid black; background-color:#FFFFE0; color:#701F28; font-weight: bold; width:150px; text-align: right;">{{ !empty(number_format($results['juli']))? (number_format($results['juli']) < 0 ? '('.number_format(abs($results['juli'])).')' : number_format($results['juli'])): '-'}}</td>
                                        <td style="border: 1px solid black; background-color:#FFFFE0; color:#701F28; font-weight: bold; width:150px; text-align: right;">{{ !empty(number_format($results['agustus']))? (number_format($results['agustus']) < 0 ? '('.number_format(abs($results['agustus'])).')' : number_format($results['agustus'])): '-'}}</td>
                                        <td style="border: 1px solid black; background-color:#FFFFE0; color:#701F28; font-weight: bold; width:150px; text-align: right;">{{ !empty(number_format($results['september']))? (number_format($results['september']) < 0 ? '('.number_format(abs($results['september'])).')' : number_format($results['september'])): '-'}}</td>
                                        <td style="border: 1px solid black; background-color:#FFFFE0; color:#701F28; font-weight: bold; width:150px; text-align: right;">{{ !empty(number_format($results['oktober']))? (number_format($results['oktober']) < 0 ? '('.number_format(abs($results['oktober'])).')' : number_format($results['oktober'])): '-'}}</td>
                                        <td style="border: 1px solid black; background-color:#FFFFE0; color:#701F28; font-weight: bold; width:150px; text-align: right;">{{ !empty(number_format($results['november']))? (number_format($results['november']) < 0 ? '('.number_format(abs($results['november'])).')' : number_format($results['november'])): '-'}}</td>
                                        <td style="border: 1px solid black; background-color:#FFFFE0; color:#701F28; font-weight: bold; width:150px; text-align: right;">{{ !empty(number_format($results['desember']))? (number_format($results['desember']) < 0 ? '('.number_format(abs($results['desember'])).')' : number_format($results['desember'])): '-'}}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
        </div>
    </div> --}}
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

    {{-- sebelum difilter --}}
    {{-- <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="col-12">
                    <div class="card-header">
                        <div class="float-end">
                            @can('create project task')
                                <a href="{{ route('projects.tasks.create.mappingaccount',[$project->id, $task->id]) }}" class="btn btn-sm btn-primary" data-bs-toggle="tooltip" title="{{__('Add Mapping Account')}}">
                                    <i class="ti ti-plus"></i>
                                </a>
                            @endcan
                        </div>
                        <h6 class="mb-0">{{__('Financial Statements (Short Form)')}}</h6>
                    </div>
                    <div class="card-body table-border-style">
                        <div class="table-responsive">
                            <table class="table datatabless">
                                <thead>
                                    <tr>
                                        <th style="text-align: center;" scope="col">{{'Code'}}</th>
                                        <th style="text-align: center;" scope="col">{{'Account Name'}}</th>
                                        <th style="text-align: center;" scope="col">Kenaikan / (Penurunan) {{$project->book_year}} Unaudited</th>
                                        <th style="text-align: center;" scope="col">%</th>
                                        <th style="text-align: center;" scope="col">Kenaikan / (Penurunan) {{$project->book_year}} Audited</th>
                                        <th style="text-align: center;" scope="col">%</th>
                                    </tr>
                                </thead>
                                <tbody class="list">
                                    @php
                                        $accountGroups = [];
                                        $totals = [];
                                    @endphp

                                    @if (count(array($data_keuangan)) > 0)
                                        @foreach ($data_keuangan as $data_keuangans)
                                            @php
                                                $accountGroup = $data_keuangans['account_group'];
                                                if (!isset($accountGroups[$accountGroup])) {
                                                    $accountGroups[$accountGroup] = [];
                                                    $totals[$accountGroup] = [
                                                        'prior_period2_total' => 0,
                                                        'prior_period_total' => 0,
                                                        'inhouse_total' => 0,
                                                        'audited_total' => 0,
                                                        'kenaikan_penurunan_prior_period_1_total' => 0,
                                                        'kenaikan_penurunan_prior_period_persen_1_total' => 0,
                                                        'kenaikan_penurunan_prior_period_2_total' => 0,
                                                        'kenaikan_penurunan_prior_period_persen_2_total' => 0,
                                                    ];
                                                }
                                                $accountGroups[$accountGroup][] = $data_keuangans;
                                                $totals[$accountGroup]['prior_period2_total'] += $data_keuangans['prior_period2'];
                                                $totals[$accountGroup]['prior_period_total'] += $data_keuangans['prior_period'];
                                                $totals[$accountGroup]['inhouse_total'] += $data_keuangans['inhouse'];
                                                $totals[$accountGroup]['kenaikan_penurunan_prior_period_1_total'] += $data_keuangans['kenaikan_penurunan_prior_period_1'];
                                                $totals[$accountGroup]['kenaikan_penurunan_prior_period_persen_1_total'] += $data_keuangans['kenaikan_penurunan_prior_period_persen_1'];
                                                $totals[$accountGroup]['kenaikan_penurunan_prior_period_2_total'] += $data_keuangans['kenaikan_penurunan_prior_period_2'];
                                                $totals[$accountGroup]['kenaikan_penurunan_prior_period_persen_2_total'] += $data_keuangans['kenaikan_penurunan_prior_period_persen_2'];
                                            @endphp
                                        @endforeach

                                        @foreach ($accountGroups as $accountGroup => $accounts)
                                            @if (count($accounts) > 0)
                                                <tr>
                                                    <th colspan="6">{{ $accountGroup }}</th>
                                                </tr>
                                                @foreach ($accounts as $account)
                                                    <tr>
                                                        <td style="border: 1px solid black; width:100px;">{{ $account['account_code'] }}</td>
                                                        <td style="border: 1px solid black; width:100px;">{{ $account['name'] }}</td>
                                                        <td style="border: 1px solid black; width:150px; text-align: right;">
                                                            {!!
                                                                !empty(number_format($account['kenaikan_penurunan_prior_period_1'])) ? (
                                                                    number_format($account['kenaikan_penurunan_prior_period_1']) < 0 ?
                                                                    '<span style="color: red;">('.number_format(abs($account['kenaikan_penurunan_prior_period_1'])).') <i class="fas fa-arrow-down"></i></span>' :
                                                                    '<span style="color: green;">'.number_format($account['kenaikan_penurunan_prior_period_1']).' <i class="fas fa-arrow-up"></i></span>'
                                                                ) : '-'
                                                            !!}
                                                        </td>
                                                        <td style="border: 1px solid black; width:150px; text-align: right;">
                                                            {!!
                                                                !empty(number_format($account['kenaikan_penurunan_prior_period_persen_1'], 2)) ? (
                                                                    $account['kenaikan_penurunan_prior_period_persen_1'] < 0 ?
                                                                    '<span style="color: red;">('.number_format(abs($account['kenaikan_penurunan_prior_period_persen_1']), 2).') <i class="fas fa-arrow-down"></i></span>' :
                                                                    '<span style="color: green;">'.number_format($account['kenaikan_penurunan_prior_period_persen_1'], 2).' <i class="fas fa-arrow-up"></i></span>'
                                                                ) : '-'
                                                            !!} %
                                                        </td>

                                                        <td style="border: 1px solid black; width:150px; text-align: right;">
                                                            {!!
                                                                !empty(number_format($account['kenaikan_penurunan_prior_period_2'])) ? (
                                                                    number_format($account['kenaikan_penurunan_prior_period_2']) < 0 ?
                                                                    '<span style="color: red;">('.number_format(abs($account['kenaikan_penurunan_prior_period_2'])).') <i class="fas fa-arrow-down"></i></span>' :
                                                                    '<span style="color: green;">'.number_format($account['kenaikan_penurunan_prior_period_2']).' <i class="fas fa-arrow-up"></i></span>'
                                                                ) : '-'
                                                            !!}
                                                        </td>

                                                        <td style="border: 1px solid black; width:150px; text-align: right;">
                                                            {!!
                                                                !empty(number_format($account['kenaikan_penurunan_prior_period_persen_2'], 2)) ? (
                                                                    $account['kenaikan_penurunan_prior_period_persen_2'] < 0 ?
                                                                    '<span style="color: red;">('.number_format(abs($account['kenaikan_penurunan_prior_period_persen_2']), 2).') <i class="fas fa-arrow-down"></i></span>' :
                                                                    '<span style="color: green;">'.number_format($account['kenaikan_penurunan_prior_period_persen_2'], 2).' <i class="fas fa-arrow-up"></i></span>'
                                                                ) : '-'
                                                            !!} %
                                                        </td>
                                                    </tr>
                                                @endforeach
                                                <tr>
                                                    <td colspan="2" style="border: 1px solid black; text-align: center; background-color:#008b8b; color:white; font-weight: bold;"><strong>TOTAL {{ $accountGroup }} :</strong></td>
                                                    <td style="border: 1px solid black; width:150px; text-align: right; background-color:#008b8b; color:white; font-weight: bold;">
                                                        {!!
                                                            !empty(number_format($totals[$accountGroup]['kenaikan_penurunan_prior_period_1_total'])) ? (
                                                                number_format($totals[$accountGroup]['kenaikan_penurunan_prior_period_1_total']) < 0 ?
                                                                '<span style="color: white;">('.number_format(abs($totals[$accountGroup]['kenaikan_penurunan_prior_period_1_total'])).') <i class="fas fa-arrow-down"></i></span>' :
                                                                '<span style="color: white;">'.number_format($totals[$accountGroup]['kenaikan_penurunan_prior_period_1_total']).' <i class="fas fa-arrow-up"></i></span>'
                                                            ) : '-'
                                                        !!}
                                                    </td>

                                                    <td style="border: 1px solid black; width:150px; text-align: right; background-color:#008b8b; color:white; font-weight: bold;">
                                                        {!!
                                                            !empty(number_format($totals[$accountGroup]['kenaikan_penurunan_prior_period_persen_1_total'], 2)) ? (
                                                                $totals[$accountGroup]['kenaikan_penurunan_prior_period_persen_1_total'] < 0 ?
                                                                '<span style="color: white;">('.number_format(abs($totals[$accountGroup]['kenaikan_penurunan_prior_period_persen_1_total']), 2).') <i class="fas fa-arrow-down"></i></span>' :
                                                                '<span style="color: white;">'.number_format($totals[$accountGroup]['kenaikan_penurunan_prior_period_persen_1_total'], 2).' <i class="fas fa-arrow-up"></i></span>'
                                                            ) : '-'
                                                        !!} %
                                                    </td>

                                                    <td style="border: 1px solid black; width:150px; text-align: right; background-color:#008b8b; color:white; font-weight: bold;">
                                                        {!!
                                                            !empty(number_format($totals[$accountGroup]['kenaikan_penurunan_prior_period_2_total'])) ? (
                                                                number_format($totals[$accountGroup]['kenaikan_penurunan_prior_period_2_total']) < 0 ?
                                                                '<span style="color: white;">('.number_format(abs($totals[$accountGroup]['kenaikan_penurunan_prior_period_2_total'])).') <i class="fas fa-arrow-down"></i></span>' :
                                                                '<span style="color: white;">'.number_format($totals[$accountGroup]['kenaikan_penurunan_prior_period_2_total']).' <i class="fas fa-arrow-up"></i></span>'
                                                            ) : '-'
                                                        !!}
                                                    </td>
                                                    <td style="border: 1px solid black; width:150px; text-align: right; background-color:#008b8b; color:white; font-weight: bold;">
                                                        {!!
                                                            !empty(number_format($totals[$accountGroup]['kenaikan_penurunan_prior_period_persen_2_total'], 2)) ? (
                                                                $totals[$accountGroup]['kenaikan_penurunan_prior_period_persen_2_total'] < 0 ?
                                                                '<span style="color: white;">('.number_format(abs($totals[$accountGroup]['kenaikan_penurunan_prior_period_persen_2_total']), 2).') <i class="fas fa-arrow-down"></i></span>' :
                                                                '<span style="color: white;">'.number_format($totals[$accountGroup]['kenaikan_penurunan_prior_period_persen_2_total'], 2).' <i class="fas fa-arrow-up"></i></span>'
                                                            ) : '-'
                                                        !!} %
                                                    </td>
                                                </tr>
                                            @endif
                                        @endforeach
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div> --}}

    {{-- sesudah difilter --}}
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="col-12">
                    <div class="card-header">
                        {{-- <div class="float-end">
                            @can('create project task')
                                <a href="{{ route('projects.tasks.create.mappingaccount',[$project->id, $task->id]) }}" class="btn btn-sm btn-primary" data-bs-toggle="tooltip" title="{{__('Add Mapping Account')}}">
                                    <i class="ti ti-plus"></i>
                                </a>
                            @endcan
                        </div> --}}
                        <h6 class="mb-0">{{__('Perbandingan Data Antar Periode')}}</h6>
                    </div>
                    <div class="card-body table-border-style">
                        <div class="table-responsive">
                            <table class="table datatabless">
                                <thead>
                                    <tr>
                                        <th style="text-align: center;" scope="col">{{'Code'}}</th>
                                        <th style="text-align: center;" scope="col">{{'Account Name'}}</th>
                                        <th style="text-align: center; width:150px; white-space: normal;" scope="col">Kenaikan / (Penurunan) {{$project->book_year}} Unaudited</th>
                                        <th style="text-align: center; width:150px; white-space: normal;" scope="col">%</th>
                                        <th style="text-align: center; width:150px; white-space: normal;" scope="col">Kenaikan / (Penurunan) {{$project->book_year}} Audited</th>
                                        <th style="text-align: center; width:150px; white-space: normal;" scope="col">%</th>
                                    </tr>
                                </thead>
                                <tbody class="list">
                                    @php
                                        $accountGroups = [];
                                        $totals = [];
                                    @endphp

                                    @if (count(array($data_keuangan)) > 0)
                                        @foreach ($data_keuangan as $data_keuangans)
                                            @php
                                                $accountGroup = $data_keuangans['account_group'];
                                                if (!isset($accountGroups[$accountGroup])) {
                                                    $accountGroups[$accountGroup] = [];
                                                    $totals[$accountGroup] = [
                                                        'prior_period2_total' => 0,
                                                        'prior_period_total' => 0,
                                                        'inhouse_total' => 0,
                                                        'audited_total' => 0,
                                                        'kenaikan_penurunan_prior_period_1_total' => 0,
                                                        'kenaikan_penurunan_prior_period_persen_1_total' => 0,
                                                        'kenaikan_penurunan_prior_period_2_total' => 0,
                                                        'kenaikan_penurunan_prior_period_persen_2_total' => 0,
                                                    ];
                                                }
                                                $accountGroups[$accountGroup][] = $data_keuangans;
                                                $totals[$accountGroup]['prior_period2_total'] += $data_keuangans['prior_period2'];
                                                $totals[$accountGroup]['prior_period_total'] += $data_keuangans['prior_period'];
                                                $totals[$accountGroup]['inhouse_total'] += $data_keuangans['inhouse'];
                                                $totals[$accountGroup]['kenaikan_penurunan_prior_period_1_total'] += $data_keuangans['kenaikan_penurunan_prior_period_1'];
                                                $totals[$accountGroup]['kenaikan_penurunan_prior_period_persen_1_total'] += $data_keuangans['kenaikan_penurunan_prior_period_persen_1'];
                                                $totals[$accountGroup]['kenaikan_penurunan_prior_period_2_total'] += $data_keuangans['kenaikan_penurunan_prior_period_2'];
                                                $totals[$accountGroup]['kenaikan_penurunan_prior_period_persen_2_total'] += $data_keuangans['kenaikan_penurunan_prior_period_persen_2'];
                                            @endphp
                                        @endforeach

                                        @php
                                           $sortedAccountGroups = [
                                                'ASET' => [],
                                                'LIABILITAS' => [],
                                                'EKUITAS' => [],
                                                'PENDAPATAN' => [],
                                                'BEBAN POKOK PENDAPATAN' => [],
                                                'BEBAN OPERASIONAL' => [],
                                                'PENDAPATAN / BEBAN KEUANGAN' => [],
                                                'PENDAPATAN / BEBAN LAIN-LAIN' => [],
                                                'BEBAN PAJAK PENGHASILAN' => [],
                                                'PENGHASILAN KOMPREHENSIF LAIN' => [],
                                            ];
                                            foreach ($accountGroups as $accountGroup => $accounts) {
                                                if (count($accounts) > 0) {
                                                    $sortedAccountGroups[$accountGroup] = $accounts;
                                                }
                                            }
                                        @endphp

                                        @foreach ($sortedAccountGroups as $accountGroup => $accounts)
                                            @if (count($accounts) > 0)
                                                <tr>
                                                    <th colspan="6">{{ $accountGroup }}</th>
                                                </tr>
                                                @foreach ($accounts as $account)
                                                    <tr>
                                                        <td style="border: 1px solid black; width:100px;">{{ $account['account_code'] }}</td>
                                                        <td style="border: 1px solid black; width:100px;">{{ $account['name'] }}</td>
                                                        <td style="border: 1px solid black; width:150px; text-align: right;">
                                                            {!!
                                                                !empty(number_format($account['kenaikan_penurunan_prior_period_1'])) ? (
                                                                    number_format($account['kenaikan_penurunan_prior_period_1']) < 0 ?
                                                                    '<span style="color: red;">('.number_format(abs($account['kenaikan_penurunan_prior_period_1'])).') <i class="fas fa-arrow-down"></i></span>' :
                                                                    '<span style="color: green;">'.number_format($account['kenaikan_penurunan_prior_period_1']).' <i class="fas fa-arrow-up"></i></span>'
                                                                ) : '-'
                                                            !!}
                                                        </td>
                                                        <td style="border: 1px solid black; width:150px; text-align: right;">
                                                            {!!
                                                                !empty(number_format($account['kenaikan_penurunan_prior_period_persen_1'], 2)) ? (
                                                                    $account['kenaikan_penurunan_prior_period_persen_1'] < 0 ?
                                                                    '<span style="color: red;">('.number_format(abs($account['kenaikan_penurunan_prior_period_persen_1']), 2).') <i class="fas fa-arrow-down"></i></span>' :
                                                                    '<span style="color: green;">'.number_format($account['kenaikan_penurunan_prior_period_persen_1'], 2).' <i class="fas fa-arrow-up"></i></span>'
                                                                ) : '-'
                                                            !!} %
                                                        </td>

                                                        <td style="border: 1px solid black; width:150px; text-align: right;">
                                                            {!!
                                                                !empty(number_format($account['kenaikan_penurunan_prior_period_2'])) ? (
                                                                    number_format($account['kenaikan_penurunan_prior_period_2']) < 0 ?
                                                                    '<span style="color: red;">('.number_format(abs($account['kenaikan_penurunan_prior_period_2'])).') <i class="fas fa-arrow-down"></i></span>' :
                                                                    '<span style="color: green;">'.number_format($account['kenaikan_penurunan_prior_period_2']).' <i class="fas fa-arrow-up"></i></span>'
                                                                ) : '-'
                                                            !!}
                                                        </td>

                                                        <td style="border: 1px solid black; width:150px; text-align: right;">
                                                            {!!
                                                                !empty(number_format($account['kenaikan_penurunan_prior_period_persen_2'], 2)) ? (
                                                                    $account['kenaikan_penurunan_prior_period_persen_2'] < 0 ?
                                                                    '<span style="color: red;">('.number_format(abs($account['kenaikan_penurunan_prior_period_persen_2']), 2).') <i class="fas fa-arrow-down"></i></span>' :
                                                                    '<span style="color: green;">'.number_format($account['kenaikan_penurunan_prior_period_persen_2'], 2).' <i class="fas fa-arrow-up"></i></span>'
                                                                ) : '-'
                                                            !!} %
                                                        </td>
                                                    </tr>
                                                @endforeach
                                                <tr>
                                                    <td colspan="2" style="border: 1px solid black; text-align: center; background-color:#008b8b; color:white; font-weight: bold;"><strong>TOTAL {{ $accountGroup }} :</strong></td>
                                                    <td style="border: 1px solid black; width:150px; text-align: right; background-color:#008b8b; color:white; font-weight: bold;">
                                                        {!!
                                                            !empty(number_format($totals[$accountGroup]['kenaikan_penurunan_prior_period_1_total'])) ? (
                                                                number_format($totals[$accountGroup]['kenaikan_penurunan_prior_period_1_total']) < 0 ?
                                                                '<span style="color: white;">('.number_format(abs($totals[$accountGroup]['kenaikan_penurunan_prior_period_1_total'])).') <i class="fas fa-arrow-down"></i></span>' :
                                                                '<span style="color: white;">'.number_format($totals[$accountGroup]['kenaikan_penurunan_prior_period_1_total']).' <i class="fas fa-arrow-up"></i></span>'
                                                            ) : '-'
                                                        !!}
                                                    </td>

                                                    <td style="border: 1px solid black; width:150px; text-align: right; background-color:#008b8b; color:white; font-weight: bold;">
                                                        {!!
                                                            !empty(number_format($totals[$accountGroup]['kenaikan_penurunan_prior_period_persen_1_total'], 2)) ? (
                                                                $totals[$accountGroup]['kenaikan_penurunan_prior_period_persen_1_total'] < 0 ?
                                                                '<span style="color: white;">('.number_format(abs($totals[$accountGroup]['kenaikan_penurunan_prior_period_persen_1_total']), 2).') <i class="fas fa-arrow-down"></i></span>' :
                                                                '<span style="color: white;">'.number_format($totals[$accountGroup]['kenaikan_penurunan_prior_period_persen_1_total'], 2).' <i class="fas fa-arrow-up"></i></span>'
                                                            ) : '-'
                                                        !!} %
                                                    </td>

                                                    <td style="border: 1px solid black; width:150px; text-align: right; background-color:#008b8b; color:white; font-weight: bold;">
                                                        {!!
                                                            !empty(number_format($totals[$accountGroup]['kenaikan_penurunan_prior_period_2_total'])) ? (
                                                                number_format($totals[$accountGroup]['kenaikan_penurunan_prior_period_2_total']) < 0 ?
                                                                '<span style="color: white;">('.number_format(abs($totals[$accountGroup]['kenaikan_penurunan_prior_period_2_total'])).') <i class="fas fa-arrow-down"></i></span>' :
                                                                '<span style="color: white;">'.number_format($totals[$accountGroup]['kenaikan_penurunan_prior_period_2_total']).' <i class="fas fa-arrow-up"></i></span>'
                                                            ) : '-'
                                                        !!}
                                                    </td>
                                                    <td style="border: 1px solid black; width:150px; text-align: right; background-color:#008b8b; color:white; font-weight: bold;">
                                                        {!!
                                                            !empty(number_format($totals[$accountGroup]['kenaikan_penurunan_prior_period_persen_2_total'], 2)) ? (
                                                                $totals[$accountGroup]['kenaikan_penurunan_prior_period_persen_2_total'] < 0 ?
                                                                '<span style="color: white;">('.number_format(abs($totals[$accountGroup]['kenaikan_penurunan_prior_period_persen_2_total']), 2).') <i class="fas fa-arrow-down"></i></span>' :
                                                                '<span style="color: white;">'.number_format($totals[$accountGroup]['kenaikan_penurunan_prior_period_persen_2_total'], 2).' <i class="fas fa-arrow-up"></i></span>'
                                                            ) : '-'
                                                        !!} %
                                                    </td>
                                                </tr>
                                            @endif
                                        @endforeach
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div>
        <p style="color:red">
            *Indikator kenaikan dan penurunan untuk akun <strong>PENDAPATAN / BEBAN KEUANGAN</strong>, <strong>PENDAPATAN / BEBAN LAIN-LAIN</strong>, <strong>BEBAN PAJAK PENGHASILAN</strong>, dan <strong>PENGHASILAN KOMPREHENSIF LAIN</strong> belum dapat digunakan, sistem masih dalam pengembangan.
        </p>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="col-12">
                    <div class="card-header">
                        <h6 class="mb-0">{{__('Analysis Summary')}}</h6>
                    </div>
                    <div class="card-body">
                        <div>
                            <p>
                                <strong style="color:red">PERHATIAN!</strong> Akun - Akun berikut mengalami kenaikan atau penurunan secara signifikan.
                            </p>
                        </div>
                        <div class="table-responsive">
                            <table class="table datatablesss">
                                <thead>
                                    <tr>
                                        <th style="text-align: center;" scope="col">{{'Code'}}</th>
                                        <th style="text-align: center;" scope="col">{{'Account Name'}}</th>
                                        <th style="text-align: center; width:150px;" scope="col">Kenaikan / (Penurunan) {{$project->book_year}} Unaudited</th>
                                    </tr>
                                </thead>
                                <tbody class="list">
                                    @php
                                        $accountGroups = [];
                                        $totals = [];
                                    @endphp

                                    @if (count(array($data_keuangan)) > 0)
                                        @foreach ($data_keuangan as $data_keuangans)
                                            @php
                                                $accountGroup = $data_keuangans['account_group'];
                                                if (!isset($accountGroups[$accountGroup])) {
                                                    $accountGroups[$accountGroup] = [];
                                                    $totals[$accountGroup] = [
                                                        'prior_period2_total' => 0,
                                                        'prior_period_total' => 0,
                                                        'inhouse_total' => 0,
                                                        'audited_total' => 0,
                                                        'kenaikan_penurunan_prior_period_1_total' => 0,
                                                        'filter_kenaikan_penurunan_prior_period_1_total' => 0,
                                                        'kenaikan_penurunan_prior_period_persen_1_total' => 0,
                                                        'kenaikan_penurunan_prior_period_2_total' => 0,
                                                        'kenaikan_penurunan_prior_period_persen_2_total' => 0,
                                                    ];
                                                }
                                                $accountGroups[$accountGroup][] = $data_keuangans;
                                                $totals[$accountGroup]['prior_period2_total'] += $data_keuangans['prior_period2'];
                                                $totals[$accountGroup]['prior_period_total'] += $data_keuangans['prior_period'];
                                                $totals[$accountGroup]['inhouse_total'] += $data_keuangans['inhouse'];
                                                $totals[$accountGroup]['kenaikan_penurunan_prior_period_1_total'] += $data_keuangans['kenaikan_penurunan_prior_period_1'];
                                                $totals[$accountGroup]['filter_kenaikan_penurunan_prior_period_1_total'] += $data_keuangans['filter_kenaikan_penurunan_prior_period_1'];
                                                $totals[$accountGroup]['kenaikan_penurunan_prior_period_persen_1_total'] += $data_keuangans['kenaikan_penurunan_prior_period_persen_1'];
                                                $totals[$accountGroup]['kenaikan_penurunan_prior_period_2_total'] += $data_keuangans['kenaikan_penurunan_prior_period_2'];
                                                $totals[$accountGroup]['kenaikan_penurunan_prior_period_persen_2_total'] += $data_keuangans['kenaikan_penurunan_prior_period_persen_2'];
                                            @endphp
                                        @endforeach

                                        @php
                                        $sortedAccountGroups = [
                                                'ASET' => [],
                                                'LIABILITAS' => [],
                                                'EKUITAS' => [],
                                                'PENDAPATAN' => [],
                                                'BEBAN POKOK PENDAPATAN' => [],
                                                'BEBAN OPERASIONAL' => [],
                                                'PENDAPATAN / BEBAN KEUANGAN' => [],
                                                'PENDAPATAN / BEBAN LAIN-LAIN' => [],
                                                'BEBAN PAJAK PENGHASILAN' => [],
                                                'PENGHASILAN KOMPREHENSIF LAIN' => [],
                                            ];
                                            foreach ($accountGroups as $accountGroup => $accounts) {
                                                if (count($accounts) > 0) {
                                                    $sortedAccountGroups[$accountGroup] = $accounts;
                                                }
                                            }
                                        @endphp

                                        @foreach ($sortedAccountGroups as $accountGroup => $accounts)
                                            @php
                                                $hasFilteredData = false;
                                            @endphp

                                            @if (count($accounts) > 0)
                                                @php
                                                    $hasFilteredData = collect($accounts)->pluck('filter_kenaikan_penurunan_prior_period_1')->filter()->count() > 0;
                                                @endphp

                                                @if ($hasFilteredData)
                                                    <tr>
                                                        <th colspan="6">{{ $accountGroup }}</th>
                                                    </tr>
                                                    @foreach ($accounts as $account)
                                                        @if (!empty($account['filter_kenaikan_penurunan_prior_period_1']))
                                                            <tr>
                                                                <td style="border: 1px solid black; width:100px;">{{ $account['account_code'] }}</td>
                                                                <td style="border: 1px solid black; width:100px;">{{ $account['name'] }}</td>
                                                                <td style="border: 1px solid black; width:150px; text-align: right;">
                                                                    {!!
                                                                        !empty(number_format($account['filter_kenaikan_penurunan_prior_period_1'])) ? (
                                                                            number_format($account['filter_kenaikan_penurunan_prior_period_1']) < 0 ?
                                                                            '<span style="color: red;">('.number_format(abs($account['filter_kenaikan_penurunan_prior_period_1'])).') <i class="fas fa-arrow-down"></i></span>' :
                                                                            '<span style="color: green;">'.number_format($account['filter_kenaikan_penurunan_prior_period_1']).' <i class="fas fa-arrow-up"></i></span>'
                                                                        ) : '-'
                                                                    !!}
                                                                </td>
                                                            </tr>
                                                        @endif
                                                    @endforeach

                                                    <tr>
                                                        <td colspan="2" style="border: 1px solid black; text-align: center; background-color:#008b8b; color:white; font-weight: bold;"><strong>TOTAL {{ $accountGroup }} :</strong></td>
                                                        <td style="border: 1px solid black; width:150px; text-align: right; background-color:#008b8b; color:white; font-weight: bold;">
                                                            {!!
                                                                !empty(number_format($totals[$accountGroup]['filter_kenaikan_penurunan_prior_period_1_total'])) ? (
                                                                    number_format($totals[$accountGroup]['filter_kenaikan_penurunan_prior_period_1_total']) < 0 ?
                                                                    '<span style="color: white;">('.number_format(abs($totals[$accountGroup]['filter_kenaikan_penurunan_prior_period_1_total'])).') <i class="fas fa-arrow-down"></i></span>' :
                                                                    '<span style="color: white;">'.number_format($totals[$accountGroup]['filter_kenaikan_penurunan_prior_period_1_total']).' <i class="fas fa-arrow-up"></i></span>'
                                                                ) : '-'
                                                            !!}
                                                        </td>
                                                    </tr>
                                                @endif
                                            @endif
                                        @endforeach

                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    {{ Form::open(['route' => ['send.message', [$project->id, $task->id]], 'method' => 'post']) }}
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="col-12">
                        <div class="card-header">
                            <div class="float-end">
                                <button type="submit" class="btn btn-sm btn-primary"> <i class="fas fa-robot"></i>{{__(' Generate Answers With AI')}}</button>
                            </div>
                            <h6 class="mb-0">{{__('Response From AI')}}</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                    <div class="col-sm-12 col-md-12">
                                        <div class="form-group">
                                            <div class="col-sm-12 col-md-12">
                                                    <div class="form-group">
                                                        {{ Form::textarea('message', null, ['class' => 'form-control message']) }}
                                                    </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-12 col-md-12">
                                        @if(isset($respons->response ))
                                            <div class="form-group">
                                                <p class="formatted-text">
                                                    {{ $respons->response }}
                                                </p>
                                            </div>
                                        @endif
                                    </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    {{ Form::close() }}

    {{ Form::open(['route' => ['notes.analysis', [$project->id, $task->id]], 'method' => 'post']) }}
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="col-12">
                        <div class="card-header"><h6 class="mb-0">{{__('Auditor Notes')}}</h6>
                        <br>
                        <p>
                            <strong>
                                    Reference : https://
                            </strong>
                        </p>
                        
                        </div>
                        <div class="card-body">
                            <div class="row">
                                    <div class="col-sm-12 col-md-12">
                                        <div class="form-group">
                                            <div class="col-sm-12 col-md-12">
                                                    <div class="form-group">
                                                    @if(isset($notesanalysis->notes))
                                                        {{ Form::textarea('notes', $notesanalysis->notes, ['class' => 'form-control notes']) }}
                                                    @else
                                                        {{ Form::textarea('notes', null, ['class' => 'form-control notes']) }}
                                                    @endif
                                                    </div>
                                            </div>
                                        </div>
                                    </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <input type="submit" value="{{__('Save')}}" class="btn btn-simpan  btn-primary">
        </div>
    {{ Form::close() }}

    
    {{-- <div class="row">
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
                                        <td style="border: 1px solid black; width:150px; text-align: right;">{{!empty($indexs['kenaikan_2021']) ? (($indexs['kenaikan_2021'] < 0) ? '('.number_format(abs($indexs['kenaikan_2021'])).')' : number_format($indexs['kenaikan_2021'])) : '-'}}</td>
                                        <td style="border: 1px solid black; width:150px; background-color:#ffcccb; font-weight: bold; text-align: center;">{{!empty($indexs['M/TM'])? $indexs['M/TM']:'-'}}</td>
                                        <td style="border: 1px solid black; width:150px; text-align: right;">
                                        <?php
                                            $persen = number_format($indexs['persen_kenaikan'], 2);
                                            $persen = ($persen == '0.00') ? '-' : $persen;
                                        ?>
                                        {{$persen}}
                                        </td>
                                        <td style="border: 1px solid black; width:150px; text-align: right;">{{!empty($indexs['kenaikan_2022']) ? (($indexs['kenaikan_2022'] < 0) ? '('.number_format(abs($indexs['kenaikan_2022'])).')' : number_format($indexs['kenaikan_2022'])) : '-'}}</td>
                                        <td style="border: 1px solid black; width:150px; background-color:#ffcccb; font-weight: bold; text-align: center;">{{!empty($indexs['M/TM_2022'])? $indexs['M/TM_2022']:'-'}}</td>
                                        <td style="border: 1px solid black; width:150px; text-align: right;">
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
                                        <td style="border: 1px solid black; text-align: right;">
                                        <?php
                                            $persen = number_format($results['increase_2021'], 2);
                                            $persen = ($persen == '0.00') ? '-' : $persen;
                                        ?>
                                        {{$persen}}
                                        </td>
                                        <td style="border: 1px solid black; text-align: right;">
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
    </div> --}}
{{-- {{ Form::close() }} --}}
@endsection

